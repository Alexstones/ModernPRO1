<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;
use App\Models\PrintBatch;
use App\Models\PrintBatchItem;
use Barryvdh\DomPDF\Facade\Pdf;

class ProcessPrintBatch implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $timeout = 900; // 15min
  public function __construct(public int $batchId, public string $excelPath) {}

  public function handle(): void
  {
    $batch = PrintBatch::findOrFail($this->batchId);
    $batch->update(['status'=>'processing','items_done'=>0,'items_failed'=>0,'artifact_path'=>null,'error'=>null]);

    // 1) Leer Excel → filas (headers normalizados)
    $abs = Storage::path($this->excelPath);
    $type = IOFactory::identify($abs);
    $reader = IOFactory::createReader($type);
    $reader->setReadDataOnly(true);
    $sheet = $reader->load($abs)->getActiveSheet();
    $rows  = $sheet->toArray(null, true, true, true);
    if (empty($rows)) throw new \RuntimeException('Excel vacío');

    $hdr = array_values($rows[1] ?? []);
    $norm = [];
    $seen = [];
    foreach ($hdr as $i=>$h) {
      $h = is_string($h) ? trim($h) : "Col".($i+1);
      if ($h==='') $h="Col".($i+1);
      $base=$h; $k=1; while (in_array($h,$seen)) $h=$base.'_'.(++$k);
      $seen[]=$h; $norm[$i]=$h;
    }

    $data=[];
    for ($i=2; $i<=count($rows); $i++){
      $row=array_values($rows[$i] ?? []);
      if (!array_filter($row, fn($v)=>$v!==null && $v!=='')) continue;
      $o=[]; foreach ($norm as $idx=>$h){ $o[$h]=$row[$idx] ?? null; } $data[]=$o;
    }

    // 2) Mapear a placeholders
    $map = $batch->mapping;
    $items=[];
    foreach ($data as $r){
      $itm = [
        'NOMBRE'     => (string)($r[$map['NOMBRE']] ?? ''),
        'NUMERO'     => (string)($r[$map['NUMERO']] ?? ''),
        'TALLE'      => (string)($r[$map['TALLE']]  ?? ''),
        'CATEGORIA'  => (string)($r[$map['CATEGORIA']] ?? ''), // opcional
      ];
      if (trim(implode('', $itm))==='') continue;
      $items[] = $itm;
    }
    if (!$items) throw new \RuntimeException('No hay filas válidas tras el mapeo');

    // Crear records items
    $batch->update(['items_total'=>count($items)]);
    foreach ($items as $i=>$it) {
      PrintBatchItem::create([
        'print_batch_id'=>$batch->id,
        'index'=>$i+1,
        'payload'=>$it,
      ]);
    }

    // 3) Generar PDFs individuales o composición
    $dir   = dirname($this->excelPath);
    $group = $batch->group_by;     // none|TALLE|CATEGORIA
    $mode  = $batch->merge_mode;   // zip|single
    $tpl   = $batch->naming_template;

    // Helper de nombre (IDX con padding)
    $nameFor = function(array $ctx, int $idx) use ($tpl){
      $repl = [
        '{NOMBRE}' => $ctx['NOMBRE'] ?? '',
        '{NUMERO}' => $ctx['NUMERO'] ?? '',
        '{TALLE}'  => $ctx['TALLE']  ?? '',
        '{CATEGORIA}' => $ctx['CATEGORIA'] ?? '',
        '{YYYY}'   => now()->format('Y'),
        '{MM}'     => now()->format('m'),
        '{DD}'     => now()->format('d'),
      ];
      $name = strtr($tpl, $repl);
      // {IDX:n}
      $name = preg_replace_callback('/\{IDX(?::(\d+))?\}/', function($m) use ($idx){
        $pad = isset($m[1]) ? (int)$m[1] : 1;
        return str_pad((string)$idx, $pad, '0', STR_PAD_LEFT);
      }, $name);
      $name = preg_replace('/[<>:"\/\\\\|?*\x00-\x1F]/','',$name);
      return trim($name) !== '' ? $name : ('item_'.$idx);
    };

    // Generación
    $pdfPaths = [];
    foreach ($batch->items()->orderBy('index')->cursor() as $it){
      try {
        $html = view('pdf.personalizacion', [
          'title'  => $batch->name,
          'item'   => $it->payload,
          'margin' => $batch->imposition['margin'] ?? 10,
          'bleed'  => $batch->imposition['bleed'] ?? 0,
          'crop'   => $batch->imposition['cropMarks'] ?? false,
        ])->render();

        $paper = strtolower($batch->imposition['sheet'] ?? 'a4');
        $pdf = Pdf::loadHTML($html)->setPaper($paper,'portrait');
        $fname = $nameFor($it->payload, $it->index).'.pdf';
        $rel = $dir.'/items/'.$fname;
        Storage::makeDirectory($dir.'/items');
        Storage::put($rel, $pdf->output());
        $it->update(['status'=>'done','pdf_path'=>$rel]);
        $pdfPaths[] = $rel;
        $batch->increment('items_done');
      } catch (\Throwable $e){
        $it->update(['status'=>'failed','error'=>$e->getMessage()]);
        $batch->increment('items_failed');
      }
      // (opcional) broadcast progreso aquí
      // event(new \App\Events\PrintBatchProgress($batch->fresh()));
    }

    // 4) Empaquetado/Imposición final
    if ($mode === 'zip'){
      // ZIP con opción de agrupar por carpeta
      $zipRel = $dir.'/final_'.$batch->name.'.zip';
      $zipAbs = Storage::path($zipRel);
      $zip = new ZipArchive();
      $zip->open($zipAbs, ZipArchive::CREATE|ZipArchive::OVERWRITE);

      foreach ($batch->items()->orderBy('index')->get() as $it){
        if (!$it->pdf_path || !Storage::exists($it->pdf_path)) continue;
        $folder = '';
        if ($group !== 'none') {
          $groupVal = $it->payload[$group] ?? 'SIN_'+$group;
          $folder = trim($groupVal) !== '' ? ($groupVal.'/') : ('_SIN_'.$group.'/');
        }
        $zip->addFile(Storage::path($it->pdf_path), $folder.basename($it->pdf_path));
      }
      $zip->close();
      $batch->update(['artifact_path'=>$zipRel,'status'=> $batch->items_failed ? 'partial':'done']);
    } else {
      // PDF único estilo imposición (grilla) usando tu Blade 'pdf.imposicion'
      $html = view('pdf.imposicion', [
        'title' => $batch->name,
        'items' => $batch->items()->orderBy('index')->get()->pluck('payload')->all(),
        'grid'  => ['cols'=>$batch->imposition['cols'] ?? 2, 'rows'=>$batch->imposition['rows'] ?? 3],
        'sheet' => strtoupper($batch->imposition['sheet'] ?? 'A4'),
        'margin'=> $batch->imposition['margin'] ?? 10,
        'bleed' => $batch->imposition['bleed'] ?? 0,
        'crop'  => $batch->imposition['pageCrop'] ?? false,
      ])->render();

      $pdf = Pdf::loadHTML($html)->setPaper(strtolower($batch->imposition['sheet'] ?? 'a4'),'portrait');
      $rel = $dir.'/final_'.$batch->name.'.pdf';
      Storage::put($rel, $pdf->output());
      $batch->update(['artifact_path'=>$rel,'status'=> $batch->items_failed ? 'partial':'done']);
    }

    // 5) Curvas (opcional, post-proceso con Ghostscript)
    if ($batch->outline && $batch->artifact_path) {
      try {
        $outlined = self::outlinePdf(Storage::path($batch->artifact_path));
        Storage::put($batch->artifact_path, file_get_contents($outlined));
        @unlink($outlined);
      } catch (\Throwable $e){
        Log::warning('[Batch] Outline falló: '.$e->getMessage());
      }
    }
  }

  // Convertir a curvas (si GS está instalado). Usa -dNoOutputFonts
  protected static function outlinePdf(string $inPath): string
  {
    $outPath = $inPath.'.outlined.pdf';
    $cmd = [
      'gs','-dBATCH','-dNOPAUSE','-dNOSAFER',
      '-sDEVICE=pdfwrite',
      '-dCompatibilityLevel=1.6',
      '-dPDFSETTINGS=/prepress',
      '-dEmbedAllFonts=true',
      '-dSubsetFonts=true',
      '-dNoOutputFonts',          // <- convierte texto a trazos
      "-sOutputFile={$outPath}",
      $inPath
    ];
    $proc = proc_open(implode(' ', array_map('escapeshellarg',$cmd)), [1=>['pipe','w'],2=>['pipe','w']], $pipes, base_path());
    if (!is_resource($proc)) throw new \RuntimeException('No se pudo ejecutar Ghostscript');
    $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $code = proc_close($proc);
    if ($code !== 0 || !file_exists($outPath)) {
      throw new \RuntimeException("GS error ($code): $stderr $stdout");
    }
    return $outPath;
  }
}
