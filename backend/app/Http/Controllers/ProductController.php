<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre'          => 'required|string|max:255',
            'tipo'            => 'required|string|max:255',
            'ptfeLetra'       => 'nullable|string|max:255',
            'ptfeNumero'      => 'nullable|string|max:255',
            'disenoDelante'   => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'disenoPosterior' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'modeloDelante'   => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'modeloPosterior' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'disenoMangaDer'  => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'disenoMangaIzq'  => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ]);

        $productData = $validatedData;

        foreach ([
            'disenoDelante','disenoPosterior','modeloDelante',
            'modeloPosterior','disenoMangaDer','disenoMangaIzq'
        ] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('products');
                $productData[$field] = $path;
            }
        }

        return response()->json([
            'message' => 'Producto recibido con éxito. Archivos guardados.',
            'product' => $productData,
        ], 201);
    }
}
