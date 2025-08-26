<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;

class PdfController extends Controller
{
    /* =========================
     * Utils
     * ========================= */
    protected function mmToPt($mm) { return ($mm * 72.0) / 25.4; }

    /** Normaliza nombre con plantilla. Soporta {CAMPO}, {IDX} y {IDX:n} (cero-padding). */
    protected function formatName(string $tpl, array $item, int $idx): string
    {
        $map = [
            'NOMBRE'     => $item['NOMBRE'] ?? '',
            'NUMERO'     => $item['NUMERO'] ?? '',
            'TALLE'      => $item['TALLE']  ?? '',
            'CATEGORIA'  => $item['CATEGORIA'] ?? '',
            'YYYY'       => now()->format('Y'),
            'MM'         => now()->format('m'),
            'DD'         => now()->format('d'),
        ];
        $name = preg_replace_callback('/\{IDX(?::(\d+))?\}/', function($m) use ($idx){
            $pad = isset($m[1]) ? (int)$m[1] : 1;
            return str_pad((string)$idx, max(1,$pad), '0', STR_PAD_LEFT);
        }, $tpl);
        $name = preg_replace_callback('/\{([A-Z_]+)\}/', function($m) use ($map){
            return $map[$m[1]] ?? '';
        }, $name);
        $name = trim(preg_replace('/\s+/', ' ', $name));
        $name = preg_replace('/[<>:"\/\\\\|?*\x00-\x1F]+/', '', $name);
        return $name === '' ? 'item' : $name;
    }

    /** Ejecuta Ghostscript para convertir texto a curvas (si está disponible). */
    protected function outlineWithGhostscript(string $pdfBytes): ?string
    {
        $tmpIn  = tempnam(sys_get_temp_dir(), 'in_').'.pdf';
        $tmpOut = tempnam(sys_get_temp_dir(), 'out_').'.pdf';
        file_put_contents($tmpIn, $pdfBytes);

        // -dNoOutputFonts → convierte texto a contornos.
        // /prepress mantiene calidad, sin bajadas de resolución.
        $cmd = sprintf(
            'gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -dCompatibilityLevel=1.6 -dPDFSETTINGS=/prepress -dEmbedAllFonts=true -dNoOutputFonts -sOutputFile=%s %s 2>&1',
            escapeshellarg($tmpOut),
            escapeshellarg($tmpIn)
        );

        @exec($cmd, $out, $code);
        if ($code !== 0 || !file_exists($tmpOut)) {
            @unlink($tmpIn);
            @unlink($tmpOut);
            Log::warning('[GS] Ghostscript no disponible o falló', ['rc' => $code, 'out' => $out]);
            return null; // fallback: devolveremos el PDF original
        }
        $bytes = file_get_contents($tmpOut);
        @unlink($tmpIn); @unlink($tmpOut);
        return $bytes !== false ? $bytes : null;
    }

    /** Lee Excel y devuelve [headerNorm, rowsAssoc[]] */
    protected function readExcelAssoc(\Illuminate\Http\UploadedFile $uploaded): array
    {
        @ini_set('memory_limit','1024M');
        $tmpPath  = $uploaded->getRealPath();
        $type     = IOFactory::identify($tmpPath);
        $reader   = IOFactory::createReader($type);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($tmpPath);
        $sheet  = $spreadsheet->getActiveSheet();
        $rows   = $sheet->toArray(null, true, true, true);
        if (empty($rows)) return [[],[]];

        $header = array_values($rows[1] ?? []);
        $normHeader = [];
        $seen = [];
        foreach ($header as $i=>$h) {
            $h = is_string($h) ? trim($h) : "Col".($i+1);
            if ($h === '') $h = "Col".($i+1);
            $base = $h; $k=1;
            while (in_array($h, $seen)) { $h = $base.'_'.(++$k); }
            $seen[] = $h;
            $normHeader[$i] = $h;
        }

        $data = [];
        for ($i=2; $i<=count($rows); $i++) {
            $row = array_values($rows[$i] ?? []);
            if (!array_filter($row, fn($v)=>$v!==null && $v!=='')) continue;
            $assoc = [];
            foreach ($normHeader as $idx=>$h) $assoc[$h] = $row[$idx] ?? null;
            $data[] = $assoc;
        }
        return [$normHeader, $data];
    }

    /* =========================
     * Existente (simple)
     * ========================= */
    public function generate(Request $request)
    {
        Log::info('[PDF] Inicio', [
            'ip'       => $request->ip(),
            'hasFile'  => $request->hasFile('excelFile'),
            'fileName' => $request->input('fileName'),
            'download' => $request->boolean('download'),
        ]);

        $validated = $request->validate([
            'excelFile'     => 'required|file|mimes:xlsx,xls',
            'fileName'      => 'required|string',
            'jpegQuality'   => 'nullable|integer|min:0|max:100',
            'imageRes'      => 'nullable|integer',
            'colorImageRes' => 'nullable|integer',
            'grayImageRes'  => 'nullable|integer',
            'compressPdf'   => 'nullable|boolean',
            'download'      => 'nullable|boolean',
        ]);

        if (!view()->exists('pdf.excel')) {
            return response()->json(['message' => 'No se encontró la vista Blade "pdf.excel".'], 500);
        }

        try {
            [$header, $data] = $this->readExcelAssoc($request->file('excelFile'));
        } catch (\Throwable $e) {
            Log::error('[PDF] Error leyendo Excel', ['error' => $e->getMessage()]);
            return response()->json(['message'=>'Error interno al procesar el archivo.','detail'=>$e->getMessage()],500);
        }

        // Tabla simple
        $table = '<table border="1" cellspacing="0" cellpadding="6" width="100%"><thead><tr>';
        foreach ($header as $h) $table .= '<th>'.e($h).'</th>';
        $table .= '</tr></thead><tbody>';
        foreach ($data as $row) {
            $table .= '<tr>';
            foreach ($header as $h) $table .= '<td>'.e($row[$h]).'</td>';
            $table .= '</tr>';
        }
        $table .= '</tbody></table>';

        try {
            $html = view('pdf.excel', [
                'title'    => preg_replace('/\.pdf$/i','',$validated['fileName']),
                'table'    => $table,
                'settings' => $validated,
            ])->render();

            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        } catch (\Throwable $e) {
            Log::error('[PDF] Error generando PDF', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'No se pudo generar el PDF.', 'detail' => $e->getMessage()], 500);
        }

        $pdfName = preg_replace('/[<>:"\/\\\\|?*\x00-\x1F]+/','',preg_replace('/\.pdf$/i','',$validated['fileName'])) . '.pdf';
        if ($request->boolean('download')) {
            return response()->streamDownload(fn()=>print($pdf->output()), $pdfName, [
                'Content-Type'=>'application/pdf',
                'Content-Disposition'=>'attachment; filename="'.$pdfName.'"',
                'Cache-Control'=>'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'=>'no-cache',
            ]);
        }

        return response()->json(['message'=>'PDF generado con éxito','fileName'=>$pdfName]);
    }

    /* =========================
     * Lote + Imposición + ZIP + Nomenclatura
     * ========================= */
    public function generateBatch(Request $request)
    {
        $validated = $request->validate([
            'excelFile'      => 'required|file|mimes:xlsx,xls',
            'fileName'       => 'required|string',
            'mapping'        => 'required|string', // JSON con NOMBRE/NUMERO/TALLE/CATEGORIA(opc)
            'imposition'     => 'required|string', // JSON: sheet, cols, rows, margin, gap, bleed, cropMarks, rotate, fit
            'namingTemplate' => 'nullable|string', // p.ej "{NOMBRE}_{NUMERO}_{TALLE}_{IDX:3}"
            'groupBy'        => 'nullable|string|in:none,TALLE,CATEGORIA',
            'mergeMode'      => 'nullable|string|in:zip,single', // zip(default) ó single (PDF único)
            'outline'        => 'nullable|boolean', // convertir a curvas con GS
            'download'       => 'nullable|boolean',
        ]);

        $mapping    = json_decode($validated['mapping'], true) ?: [];
        $imposition = json_decode($validated['imposition'], true) ?: [];
        $mergeMode  = $validated['mergeMode'] ?? ($imposition['mergeMode'] ?? 'zip');
        $tpl        = $validated['namingTemplate'] ?? '{NOMBRE}_{NUMERO}_{TALLE}_{IDX:2}';
        $groupBy    = $validated['groupBy'] ?? 'none';
        $outline    = (bool)($validated['outline'] ?? false);

        foreach (['NOMBRE','NUMERO','TALLE'] as $k) {
            if (empty($mapping[$k])) return response()->json(['message'=>"Falta mapping para $k"], 422);
        }

        try {
            [$header, $rows] = $this->readExcelAssoc($request->file('excelFile'));
        } catch (\Throwable $e) {
            Log::error('[BATCH] Error Excel', ['e'=>$e->getMessage()]);
            return response()->json(['message'=>'No se pudo leer el Excel','detail'=>$e->getMessage()],500);
        }

        $items = collect($rows)->map(function($r) use ($mapping){
            $get = fn($k)=> (string)($r[$mapping[$k]] ?? '');
            return [
                'NOMBRE'     => $get('NOMBRE'),
                'NUMERO'     => $get('NUMERO'),
                'TALLE'      => $get('TALLE'),
                'CATEGORIA'  => $r[$mapping['CATEGORIA'] ?? ''] ?? '',
            ];
        })->filter(fn($it)=>trim(implode('', $it))!=='')->values()->all();

        if (empty($items)) return response()->json(['message'=>'No hay filas válidas tras el mapeo.'], 422);

        // Parámetros de imposición
        $sheet   = strtolower($imposition['sheet'] ?? 'a4');                 // 'a4' | 'letter' | 'legal' ...
        $cols    = max(1, (int)($imposition['cols'] ?? 1));
        $rowsG   = max(1, (int)($imposition['rows'] ?? 1));
        $margin  = (float)($imposition['margin'] ?? 10);                     // mm
        $gap     = (float)($imposition['gap'] ?? 4);                          // mm entre celdas
        $bleed   = (float)($imposition['bleed'] ?? 3);                        // mm de sangrado interno de cada pieza
        $crop    = !empty($imposition['cropMarks']);                          // marcas de corte por pieza
        $pageCrop= !empty($imposition['pageCrop']);                           // marcas en bordes de página
        $rotate  = !empty($imposition['rotate']);                             // auto-rotar piezas (texto)
        $fit     = in_array(($imposition['fit'] ?? 'contain'), ['contain','fill']) ? $imposition['fit'] : 'contain';

        /* ============ Modo ZIP: 1 PDF por ítem, agrupado opcionalmente ============ */
        if ($mergeMode === 'zip') {
            $zipPath = storage_path('app/tmp_'.Str::uuid().'.zip');
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE)!==true) {
                return response()->json(['message'=>'No se pudo crear ZIP'], 500);
            }

            foreach ($items as $i=>$item) {
                $name = $this->formatName($tpl, $item, $i+1);

                $html = view('pdf.print_item', [
                    'title'   => $validated['fileName'],
                    'item'    => $item,
                    'margin'  => $margin,
                    'bleed'   => $bleed,
                    'crop'    => $crop,
                    'pageCrop'=> $pageCrop,
                    'rotate'  => $rotate,
                ])->render();

                $pdf = Pdf::loadHTML($html)->setPaper($sheet,'portrait');
                $bytes = $pdf->output();

                if ($outline) {
                    $outlined = $this->outlineWithGhostscript($bytes);
                    if ($outlined) $bytes = $outlined;
                }

                $dir = '';
                if ($groupBy === 'TALLE')      $dir = trim((string)$item['TALLE']);
                elseif ($groupBy === 'CATEGORIA') $dir = trim((string)$item['CATEGORIA']);
                $filename = ($dir ? ($dir.'/') : '') . $name . '.pdf';

                $zip->addFromString($filename, $bytes);
            }
            $zip->close();

            $zipName = preg_replace('/[<>:"\/\\\\|?*\x00-\x1F]+/','',preg_replace('/\.zip$/i','',$validated['fileName'])) . '.zip';
            return response()->download($zipPath, $zipName, ['Content-Type'=>'application/zip'])
                             ->deleteFileAfterSend(true);
        }

        /* ============ Modo single: PDF único impuesto (grilla) ============ */
        $html = view('pdf.print_imposition', [
            'title'   => $validated['fileName'],
            'items'   => $items,
            'grid'    => ['cols'=>$cols,'rows'=>$rowsG,'gap'=>$gap],
            'sheet'   => strtoupper($sheet),
            'margin'  => $margin,
            'bleed'   => $bleed,
            'crop'    => $crop,
            'pageCrop'=> $pageCrop,
            'fit'     => $fit,
            'rotate'  => $rotate,
        ])->render();

        $pdf  = Pdf::loadHTML($html)->setPaper($sheet,'portrait');
        $bytes = $pdf->output();

        if ($outline) {
            $outlined = $this->outlineWithGhostscript($bytes);
            if ($outlined) $bytes = $outlined;
        }

        if ($request->boolean('download', true)) {
            $name = preg_replace('/[<>:"\/\\\\|?*\x00-\x1F]+/','',preg_replace('/\.pdf$/i','',$validated['fileName'])) . '.pdf';
            return response()->streamDownload(function () use ($bytes) { echo $bytes; }, $name, [
                'Content-Type'=>'application/pdf'
            ]);
        }
        return response()->json(['message'=>'PDF listo','bytes'=>strlen($bytes)]);
    }

    /* =========================
     * Diagnóstico
     * ========================= */
    public function diagExcel(Request $request)
    {
        $request->validate(['excelFile' => 'required|file']);
        try {
            $uploaded = $request->file('excelFile');
            [$header, $rows] = $this->readExcelAssoc($uploaded);
            return response()->json([
                'ok'         => true,
                'rows_count' => count($rows),
                'header'     => $header,
                'first_row'  => $rows[0] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('[DIAG] Error leyendo Excel', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'detail' => $e->getMessage()], 500);
        }
    }
}
