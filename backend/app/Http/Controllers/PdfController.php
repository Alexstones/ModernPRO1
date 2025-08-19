<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory; // Agregado
use PhpOffice\PhpSpreadsheet\Settings; // Agregado
use PhpOffice\PhpSpreadsheet\Shared\ZipStreamWrapper; // Agregado

class PdfController extends Controller
{
    public function generate(Request $request)
    {
        Log::info('[PDF] Inicio', [
            'ip'       => $request->ip(),
            'hasFile'  => $request->hasFile('excelFile'),
            'fileName' => $request->input('fileName'),
            'download' => $request->boolean('download'),
        ]);

        // Validación
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

        // Nombre PDF
        $baseName = preg_replace('/\.pdf$/i', '', $validated['fileName']);
        $baseName = trim($baseName) !== '' ? $baseName : 'documento';
        $baseName = preg_replace('/[<>:"\/\\\\|?*\x00-\x1F]/', '', $baseName);
        $pdfName  = $baseName . '.pdf';

        if (!view()->exists('pdf.excel')) {
            return response()->json(['message' => 'No se encontró la vista Blade "pdf.excel".'], 500);
        }

        // === LECTURA DIRECTA DEL TEMPORAL + PclZip ===
        try {
            @ini_set('memory_limit', '1024M'); // por si el excel es grande

            // Fuerza PclZip en lugar de ZipArchive
            // NOTA: PhpSpreadsheet 5.0 ya no requiere esta linea. La clase ya maneja la compresion internamente.
            // Settings::setZipClass(Settings::PCLZIP); 

            $uploaded   = $request->file('excelFile');
            $tmpPath    = $uploaded->getRealPath(); // archivo temporal del sistema
            $size       = ($tmpPath && file_exists($tmpPath)) ? filesize($tmpPath) : 0;
            $clientName = $uploaded->getClientOriginalName();

            Log::info('[PDF] Archivo temporal', [
                'tmpPath'     => $tmpPath,
                'size_bytes'  => $size,
                'client_name' => $clientName,
                'client_mime' => $uploaded->getClientMimeType(),
            ]);

            if (!$tmpPath || $size < 1) {
                return response()->json([
                    'message' => 'El archivo subido está vacío o no es accesible.',
                    'detail'  => 'Revisa upload_max_filesize/post_max_size en php.ini',
                ], 500);
            }

            $type = IOFactory::identify($tmpPath); // Xlsx/Xls/…
            Log::info('[PDF] Tipo detectado', ['type' => $type]);

            if (!in_array($type, ['Xlsx', 'Xls'])) {
                return response()->json([
                    'message' => 'El archivo subido no es un libro Excel válido.',
                    'detail'  => "Detectado: {$type}. (¿Es un CSV renombrado?)",
                ], 422);
            }

            $reader = IOFactory::createReader($type);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($tmpPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            Log::error('[PDF] Error leyendo Excel', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error interno al procesar el archivo.',
                'detail'  => $e->getMessage(),   // el front debería mostrarlo
            ], 500);
        }

        // Tabla simple
        $table = '<table border="1" cellspacing="0" cellpadding="6" width="100%">';
        foreach ($rows as $row) {
            $table .= '<tr>';
            foreach ($row as $cell) $table .= '<td>'.e($cell).'</td>';
            $table .= '</tr>';
        }
        $table .= '</table>';

        // Genera PDF
        try {
            $html = view('pdf.excel', [
                'title'    => $baseName,
                'table'    => $table,
                'settings' => $validated,
            ])->render();

            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        } catch (\Throwable $e) {
            Log::error('[PDF] Error generando PDF', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'No se pudo generar el PDF.', 'detail' => $e->getMessage()], 500);
        }

        // Descarga directa o JSON
        if ($request->boolean('download')) {
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $pdfName, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$pdfName.'"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'              => 'no-cache',
            ]);
        }

        return response()->json([
            'message'  => 'PDF generado con éxito',
            'fileName' => $pdfName,
        ]);
    }

    // --- DIAGNÓSTICO: solo subir y leer Excel ---
    public function diagExcel(Request $request)
    {
        $request->validate(['excelFile' => 'required|file']);

        try {
            @ini_set('memory_limit', '1024M');
            
            // La linea fue comentada porque Settings::setZipClass() ya no es un metodo de la clase
            // Settings::setZipClass(Settings::PCLZIP);

            $uploaded = $request->file('excelFile');
            $tmpPath  = $uploaded->getRealPath();
            $size     = ($tmpPath && file_exists($tmpPath)) ? filesize($tmpPath) : 0;

            if (!$tmpPath || $size < 1) {
                return response()->json(['ok' => false, 'stage' => 'upload', 'msg' => 'Archivo vacío/inaccesible'], 500);
            }

            $type = IOFactory::identify($tmpPath);
            if (!in_array($type, ['Xlsx', 'Xls'])) {
                return response()->json(['ok' => false, 'stage' => 'identify', 'type' => $type], 422);
            }

            $reader = IOFactory::createReader($type);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($tmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray(null, true, true, true);

            return response()->json([
                'ok'         => true,
                'type'       => $type,
                'size_bytes' => $size,
                'rows_count' => count($rows),
                'first_row'  => $rows[1] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('[DIAG] Error leyendo Excel', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'stage' => 'read', 'detail' => $e->getMessage()], 500);
        }
    }
}