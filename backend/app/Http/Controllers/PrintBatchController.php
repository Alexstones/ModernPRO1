<?php

namespace App\Http\Controllers;

use App\Models\PrintBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

class PrintBatchController extends Controller
{
    /**
     * Genera (si hace falta) y descarga el Excel normalizado de un lote.
     * GET /api/print/normalized/{batch}?refresh=1  → fuerza regeneración
     */
    public function downloadNormalized(Request $request, $batchId)
    {
        $batch = $this->findBatch($batchId);
        if (!$batch) {
            return response()->json(['message' => 'Batch no encontrado'], 404);
        }

        // Requisitos mínimos guardados en el batch
        $source = $batch->source_excel_path ?? null;     // ej: print_batches/abcd/source.xlsx
        $mappingJson = $batch->mapping_json ?? null;     // {"NOMBRE":"Nombre","NUMERO":"Numero","TALLE":"Talle","CATEGORIA":"Categoria"}
        if (!$source || !$mappingJson) {
            return response()->json(['message' => 'El lote no tiene Excel fuente o mapping guardado'], 422);
        }

        $mapping = json_decode($mappingJson, true) ?: [];
        $normalizedRel  = $batch->normalized_excel_path ?: null; // ej: print_batches/abcd/normalized.xlsx
        $needsRefresh   = $request->boolean('refresh');

        // Si ya existe y no se pide refresh → descargar directamente
        if (!$needsRefresh && $normalizedRel && Storage::disk('local')->exists($normalizedRel)) {
            return $this->downloadFromStorage($normalizedRel, $this->downloadName($batch, 'normalized.xlsx'));
        }

        // Regenerar
        try {
            $normalizedRel = $this->buildNormalized($source, $mapping, $batch);
            // Persistimos la ruta relativa en el batch (si existe la columna)
            try {
                $batch->normalized_excel_path = $normalizedRel;
                $batch->save();
            } catch (\Throwable $e) {
                // Si tu esquema no tiene la columna, no es bloqueante
                Log::warning('[PrintBatch] No se pudo guardar normalized_excel_path: ' . $e->getMessage());
            }

            return $this->downloadFromStorage($normalizedRel, $this->downloadName($batch, 'normalized.xlsx'));
        } catch (\Throwable $e) {
            Log::error('[PrintBatch] Error generando normalized.xlsx', [
                'batch_id' => $batch->id ?? null,
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['message' => 'No se pudo generar el Excel normalizado', 'detail' => $e->getMessage()], 500);
        }
    }

    /* ===================== Helpers ===================== */

    protected function findBatch($idOrUuid): ?PrintBatch
    {
        // Adapta según tu PK (numérica o uuid). Probamos ambas.
        $q = PrintBatch::query();
        if (Str::isUuid((string)$idOrUuid)) {
            return $q->where('uuid', $idOrUuid)->orWhere('id', $idOrUuid)->first();
        }
        return $q->where('id', $idOrUuid)->orWhere('uuid', $idOrUuid)->first();
    }

    protected function downloadFromStorage(string $relative, string $downloadAs)
    {
        $absPath = Storage::disk('local')->path($relative);
        return response()->download($absPath, $downloadAs, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }

    protected function downloadName(PrintBatch $batch, string $fallback = 'normalized.xlsx'): string
    {
        $base = $batch->file_name ?? $batch->name ?? 'lote';
        $base = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $base);
        return "{$base}_normalized.xlsx";
    }

    /**
     * Lee el Excel original, aplica mapeo y genera
     * storage/app/print_batches/{carpeta}/normalized.xlsx
     *
     * @return string Ruta relativa dentro del disk local
     */
    protected function buildNormalized(string $sourceRel, array $mapping, PrintBatch $batch): string
    {
        $sourceAbs = Storage::disk('local')->path($sourceRel);
        if (!is_file($sourceAbs)) {
            throw new \RuntimeException("No existe el archivo fuente: {$sourceRel}");
        }

        // 1) Leemos Excel original → matriz
        $type   = IOFactory::identify($sourceAbs);
        $reader = IOFactory::createReader($type);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($sourceAbs);
        $sheet  = $spreadsheet->getActiveSheet();
        $rows   = $sheet->toArray(null, true, true, true);
        if (empty($rows)) {
            throw new \RuntimeException('Excel fuente vacío');
        }

        // 2) Normalizamos headers de la 1ª fila
        $header = array_values($rows[1] ?? []);
        $normHeader = $this->normalizeHeaders($header);

        // 3) Construimos array asociativo por fila
        $data = [];
        for ($i = 2; $i <= count($rows); $i++) {
            $row = array_values($rows[$i] ?? []);
            if (!array_filter($row, fn($v) => $v !== null && $v !== '')) continue;
            $assoc = [];
            foreach ($normHeader as $idx => $h) {
                $assoc[$h] = $row[$idx] ?? null;
            }
            $data[] = $assoc;
        }

        // 4) Aplicamos mapping → columnas canónicas
        $want = ['NOMBRE','NUMERO','TALLE','CATEGORIA']; // CATEGORIA opcional
        $out = [];
        foreach ($data as $r) {
            $o = [];
            foreach ($want as $k) {
                $colName = $mapping[$k] ?? null;         // 'Nombre', 'Numero', ...
                $o[$k]   = ($colName !== null) ? ($r[$colName] ?? '') : '';
            }
            // Filtramos filas totalmente vacías
            if (trim(implode('', $o)) !== '') $out[] = $o;
        }
        if (!$out) {
            throw new \RuntimeException('No quedaron filas válidas tras aplicar el mapping');
        }

        // 5) Escribimos normalized.xlsx
        $folder = $this->ensureBatchFolder($batch, $sourceRel); // ej: print_batches/abcd
        $relative = $folder . '/normalized.xlsx';
        $absOut   = Storage::disk('local')->path($relative);

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();
        // Encabezados
        $headers = array_keys($out[0]);
        $ws->fromArray($headers, null, 'A1');
        // Filas
        $r = 2;
        foreach ($out as $row) {
            $ws->fromArray(array_values($row), null, 'A' . $r);
            $r++;
        }
        $writer = new Xlsx($wb);
        @mkdir(dirname($absOut), 0775, true);
        $writer->save($absOut);

        return $relative;
    }

    protected function normalizeHeaders(array $header): array
    {
        $norm = [];
        $seen = [];
        foreach ($header as $i => $h) {
            $h = is_string($h) ? trim($h) : 'Col' . ($i + 1);
            if ($h === '') $h = 'Col' . ($i + 1);
            $base = $h; $k = 1;
            while (in_array($h, $seen)) { $h = $base . '_' . (++$k); }
            $seen[] = $h;
            $norm[$i] = $h;
        }
        return $norm;
    }

    /**
     * Dado el path del source, resuelve la carpeta del batch.
     * Si el batch ya tiene uuid/carpeta, la usa; si no, infiere desde sourceRel.
     */
    protected function ensureBatchFolder(PrintBatch $batch, string $sourceRel): string
    {
        // Si el modelo ya tiene una carpeta definida, úsala.
        if (!empty($batch->folder)) {
            return trim($batch->folder, '/');
        }

        // Inferimos desde el source: print_batches/xxxx/source.xlsx → print_batches/xxxx
        $dir = trim(dirname($sourceRel), '/');
        // Persistimos si el modelo tiene la columna
        try {
            $batch->folder = $dir;
            $batch->save();
        } catch (\Throwable $e) {
            // columna opcional; si no existe, seguimos igual
        }
        return $dir;
    }
}
