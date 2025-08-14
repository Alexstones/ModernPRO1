<?php

namespace App\Http\Controllers;

use App\Models\Fuente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FuenteController extends Controller
{
    public function index()
    {
        // Gracias a $appends en el modelo, salen 'path' y 'url'
        return Fuente::orderByDesc('created_at')
            ->get(['id','nombre','archivo','created_at']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fuente' => ['required', 'file', 'max:8192'], // 8MB
        ]);

        $file = $request->file('fuente');

        // Aceptamos por extensión (robusto para TTF/OTF)
        $ext = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) ?: $file->extension());
        if (!in_array($ext, ['ttf', 'otf'])) {
            return response()->json(['message' => 'Solo se admiten archivos .ttf o .otf'], 422);
        }

        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($base, '_');
        $filename = $slug.'.'.$ext;
        $dir = 'fuentes';

        if (Storage::disk('public')->exists("$dir/$filename")) {
            $filename = $slug.'_'.Str::random(6).'.'.$ext;
        }

        // Guarda en storage/app/public/fuentes
        $path = $file->storeAs($dir, $filename, 'public');

        $fuente = Fuente::create([
            'nombre'  => $base,
            'archivo' => $path,
        ]);

        // Devuelve con appends: path/url
        return response()->json($fuente, 201);
    }

    // DELETE /fuentes/{fuente} (model binding)
    public function destroy(Fuente $fuente)
    {
        if ($fuente->archivo) {
            Storage::disk('public')->delete($fuente->archivo);
        }
        $fuente->delete();

        return response()->json(['ok' => true]);
    }
}

