<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function generate(Request $request)
    {
        // Valida los datos recibidos
        $validatedData = $request->validate([
            'fileName' => 'required|string',
            'jpegQuality' => 'nullable|integer|between:0,100',
            'imageRes' => 'nullable|integer',
            'colorImageRes' => 'nullable|integer',
            'grayImageRes' => 'nullable|integer',
            'compressPdf' => 'boolean',
        ]);

        // Simplemente responde con los datos para verificar que llegaron correctamente
        return response()->json([
            'message' => 'Configuración recibida con éxito.',
            'settings' => $validatedData
        ], 200);
    }
}