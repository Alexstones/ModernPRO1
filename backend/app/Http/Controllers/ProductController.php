// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validar los datos recibidos
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'ptfeLetra' => 'nullable|string|max:255',
            'ptfeNumero' => 'nullable|string|max:255',
            // Reglas de validación para los archivos. 'file' valida que es un archivo.
            'disenoDelante' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'disenoPosterior' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'modeloDelante' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'modeloPosterior' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'disenoMangaDer' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'disenoMangaIzq' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ]);

        // 2. Manejar la subida de archivos (si existen)
        $productData = $validatedData;
        $files = [
            'disenoDelante', 'disenoPosterior', 'modeloDelante', 
            'modeloPosterior', 'disenoMangaDer', 'disenoMangaIzq'
        ];

        foreach ($files as $fileField) {
            if ($request->hasFile($fileField)) {
                $filePath = $request->file($fileField)->store('products');
                $productData[$fileField] = $filePath;
            }
        }

        // 3. Aquí iría la lógica para guardar el producto en la base de datos
        // Por ahora, solo devolvemos los datos para confirmar que funciona
        return response()->json([
            'message' => 'Producto recibido con éxito. Archivos guardados.',
            'product' => $productData
        ], 201); // 201 Created es el código de éxito estándar para una creación
    }
}