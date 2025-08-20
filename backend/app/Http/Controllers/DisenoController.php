<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DisenoController extends Controller
{
    /**
     * Genera un PDF con:
     * - Columna de miniaturas a la izquierda
     * - Área principal a la derecha
     * - Texto con contorno (en SVG) arriba
     *
     * Espera un JSON como:
     * {
     *   "text":"LA GOMERIA",
     *   "fontFamily":"Inter",
     *   "fontSize":42,
     *   "fillColor":"#ffffff",
     *   "stroke":{"enabled":true,"width":2,"color":"#000000"},
     *   "images":[{"name":"1.png","width":1000,"height":1200,"dataUrl":"data:image/png;base64,..."}],
     *   "page":{"width_mm":900,"height_mm":600},
     *   "thumbs":{"width_mm":80,"gap_mm":4}
     * }
     */
    public function exportPdf(Request $req)
    {
        $data = $req->validate([
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
            'images.*.dataUrl' => 'string', // data:image/...;base64,xxxx
            'page.width_mm' => 'nullable|numeric',
            'page.height_mm' => 'nullable|numeric',
            'thumbs.width_mm' => 'nullable|numeric',
            'thumbs.gap_mm' => 'nullable|numeric',
        ]);

        // Defaults
        $pageW = $data['page']['width_mm']  ?? 900; // mm
        $pageH = $data['page']['height_mm'] ?? 600; // mm
        $thumbW = $data['thumbs']['width_mm'] ?? 80; // mm
        $thumbGap = $data['thumbs']['gap_mm'] ?? 4;  // mm

        // mm -> puntos (Dompdf trabaja en puntos)
        $mmToPt = fn($mm) => $mm * 2.834645669;
        $paper = [0, 0, $mmToPt($pageW), $mmToPt($pageH)];

        $viewData = [
            'text'       => $data['text']       ?? '',
            'fontFamily' => $data['fontFamily'] ?? 'Inter, Arial, sans-serif',
            'fontSize'   => $data['fontSize']   ?? 42,
            'fillColor'  => $data['fillColor']  ?? '#111827',
            'stroke'     => $data['stroke']     ?? ['enabled'=>true, 'width'=>2, 'color'=>'#ffffff'],
            'images'     => $data['images']     ?? [],
            'sizes'      => [
                'pageW' => $pageW, 'pageH' => $pageH,
                'thumbW' => $thumbW, 'thumbGap' => $thumbGap,
            ],
        ];

        $pdf = Pdf::loadView('pdf.diseno', $viewData)
            ->setPaper($paper, 'landscape')
            ->setOptions([
                'dpi' => 150,
                'isRemoteEnabled' => true, // por si en el futuro usas URLs http(s)
                'defaultFont' => 'DejaVu Sans', // fallback para caracteres especiales
            ]);

        // Puedes usar ->stream() si prefieres abrir en el navegador
        return $pdf->download('diseno.pdf');
    }

    /* --- (Opcional) Endpoints de paleta/presets si los usas --- */
    public function getPalette()  { return response()->json(['colors' => ['#111827','#ffffff','#ef4444','#3b82f6','#10b981','#f59e0b']]); }
    public function savePalette(Request $r) { return response()->json(['ok'=>true]); }
    public function indexPresets()  { return response()->json(['data'=>[]]); }
    public function storePreset()   { return response()->json(['ok'=>true]); }
    public function updatePreset()  { return response()->json(['ok'=>true]); }
    public function destroyPreset() { return response()->json(['ok'=>true]); }
}
