<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

// PARA EXCEL → PDF
use PhpOffice\PhpSpreadsheet\IOFactory;   // composer require phpoffice/phpspreadsheet

class DisenoController extends Controller
{
    /**
     * Genera y descarga el PDF directamente (JSON + base64).
     * Front: POST /api/diseno/pdf  (content-type: application/json, accept: application/pdf)
     */
    public function exportPdf(Request $req)
    {
        $data = $this->validatePayload($req);
        [$paper, $viewData] = $this->composeViewData($data);

        $pdf = Pdf::loadView('pdf.diseno', $viewData)
            ->setPaper($paper, 'landscape')
            ->setOptions([
                'dpi' => 150,
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->download('diseno.pdf');
    }

    /**
     * SUBIR y generar moldería en el servidor.
     * Acepta:
     *  - sourceFile: Excel (.xlsx/.xls) o PDF (.pdf)
     *  - images[]  : imágenes opcionales
     * Devuelve URL pública para descargar el resultado.
     */
    public function molderiaUpload(Request $req)
    {
        // Meta (texto/estilo) y tamaños base
        $req->validate([
            'text' => 'nullable|string',
            'fontFamily' => 'nullable|string',
            'fontSize' => 'nullable|numeric',
            'fillColor' => 'nullable|string',
            'stroke.enabled' => 'nullable',
            'stroke.width' => 'nullable|numeric',
            'stroke.color' => 'nullable|string',
            'page.width_mm' => 'nullable|numeric',
            'page.height_mm' => 'nullable|numeric',
            'thumbs.width_mm' => 'nullable|numeric',
            'thumbs.gap_mm' => 'nullable|numeric',
            'sourceFile' => 'nullable|file|mimes:xlsx,xls,pdf|max:51200', // 50MB
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg|max:12288',
        ]);

        // Si subieron un archivo origen, decidir por tipo:
        if ($req->hasFile('sourceFile')) {
            $f = $req->file('sourceFile');

            if ($f->getClientOriginalExtension() === 'pdf' || $f->getMimeType() === 'application/pdf') {
                // Guardar el PDF tal cual
                $filename = 'molderia-'.time().'.pdf';
                $path = 'molderias/'.$filename;
                Storage::disk('public')->put($path, file_get_contents($f->getRealPath()));
                $url = Storage::disk('public')->url($path);

                return response()->json(['url' => $url, 'filename' => $filename]);
            }

            // Excel → PDF (por hoja, tabla simple)
            $spreadsheet = IOFactory::load($f->getRealPath());

            $sheets = [];
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $rows = $sheet->toArray(null, true, true, true); // con letras A,B,C...
                $sheets[] = [
                    'title' => $sheet->getTitle(),
                    'rows'  => $rows,
                ];
            }

            // Renderizar a PDF con blade específico para Excel
            $pageW = (float)$req->input('page.width_mm', 210);
            $pageH = (float)$req->input('page.height_mm', 297);
            $mmToPt = fn($mm) => $mm * 2.834645669;
            $paper = [0, 0, $mmToPt($pageW), $mmToPt($pageH)];

            $pdf = Pdf::loadView('pdf.excel', [
                    'sheets' => $sheets,
                    'meta'   => [
                        'text' => $req->input('text', ''),
                        'fontFamily' => $req->input('fontFamily', 'Inter, Arial, sans-serif'),
                        'fontSize'   => (float)$req->input('fontSize', 16),
                        'fillColor'  => $req->input('fillColor', '#111827'),
                    ],
                ])
                ->setPaper($paper, 'portrait')
                ->setOptions([
                    'dpi' => 150,
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                ]);

            $binary   = $pdf->output();
            $filename = 'molderia-excel-'.time().'.pdf';
            $path     = 'molderias/'.$filename;
            Storage::disk('public')->put($path, $binary);
            $url = Storage::disk('public')->url($path);

            return response()->json(['url' => $url, 'filename' => $filename]);
        }

        // ---- SIN sourceFile: usar imágenes + texto (tu diseño) ----
        $data = $req->all();

        // Convertir imágenes a dataURL si vinieron como files
        $imagesPayload = [];
        if ($req->hasFile('images')) {
            foreach ($req->file('images') as $file) {
                $mime = $file->getMimeType() ?: 'image/png';
                $b64  = base64_encode(file_get_contents($file->getRealPath()));
                $imagesPayload[] = [
                    'name' => $file->getClientOriginalName(),
                    'width' => 0,
                    'height'=> 0,
                    'dataUrl' => "data:{$mime};base64,{$b64}",
                ];
            }
        } else {
            $imagesPayload = $data['images'] ?? [];
        }
        $data['images'] = $imagesPayload;

        [$paper, $viewData] = $this->composeViewData($data);

        $pdf = Pdf::loadView('pdf.diseno', $viewData)
            ->setPaper($paper, 'landscape')
            ->setOptions([
                'dpi' => 150,
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        $binary   = $pdf->output();
        $filename = 'molderia-'.time().'.pdf';
        $path     = 'molderias/'.$filename;

        Storage::disk('public')->put($path, $binary);
        $url = Storage::disk('public')->url($path);

        return response()->json(['url' => $url, 'filename' => $filename]);
    }

    /* ---------- Extra (opcionales) ---------- */
    public function getPalette()  { return response()->json(['colors' => ['#111827','#ffffff','#ef4444','#3b82f6','#10b981','#f59e0b']]); }
    public function savePalette(Request $r) { return response()->json(['ok'=>true]); }
    public function indexPresets()  { return response()->json(['data'=>[]]); }
    public function storePreset()   { return response()->json(['ok'=>true]); }
    public function updatePreset()  { return response()->json(['ok'=>true]); }
    public function destroyPreset() { return response()->json(['ok'=>true]); }

    /* ---------- Helpers privados ---------- */

    private function validatePayload(Request $req): array
    {
        return $req->validate([
            'text' => 'nullable|string',
            'fontFamily' => 'nullable|string',
            'fontSize' => 'nullable|numeric',
            'fillColor' => 'nullable|string',
            'stroke.enabled' => 'boolean',
            'stroke.width' => 'numeric',
            'stroke.color' => 'string',
            'images' => 'array',
            'images.*.name' => 'string',
            'images.*.width' => 'numeric',
            'images.*.height' => 'numeric',
            'images.*.dataUrl' => 'string',
            'page.width_mm' => 'nullable|numeric',
            'page.height_mm' => 'nullable|numeric',
            'thumbs.width_mm' => 'nullable|numeric',
            'thumbs.gap_mm' => 'nullable|numeric',
            'layout' => 'nullable|string',
            'scale'  => 'nullable|numeric',
        ]);
    }

    /** compone [$paper, $viewData] para la vista pdf.diseno */
    private function composeViewData(array $data): array
    {
        $pageW   = (float) data_get($data, 'page.width_mm', 900);
        $pageH   = (float) data_get($data, 'page.height_mm', 600);
        $thumbW  = (float) data_get($data, 'thumbs.width_mm', 80);
        $thumbGp = (float) data_get($data, 'thumbs.gap_mm', 4);

        $mmToPt = fn($mm) => $mm * 2.834645669;
        $paper  = [0, 0, $mmToPt($pageW), $mmToPt($pageH)];

        $viewData = [
            'text'       => $data['text']       ?? '',
            'fontFamily' => $data['fontFamily'] ?? 'Inter, Arial, sans-serif',
            'fontSize'   => $data['fontSize']   ?? 42,
            'fillColor'  => $data['fillColor']  ?? '#111827',
            'stroke'     => [
                'enabled' => data_get($data, 'stroke.enabled', true),
                'width'   => data_get($data, 'stroke.width', 2),
                'color'   => data_get($data, 'stroke.color', '#ffffff'),
            ],
            'images'     => $data['images']     ?? [],
            'sizes'      => [
                'pageW' => $pageW, 'pageH' => $pageH,
                'thumbW' => $thumbW, 'thumbGap' => $thumbGp,
            ],
            'layout'     => $data['layout'] ?? 'full',
            'scale'      => $data['scale']  ?? 1,
        ];

        return [$paper, $viewData];
    }
}
