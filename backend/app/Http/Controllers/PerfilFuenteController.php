<?php

namespace App\Http\Controllers;

use App\Models\PerfilFuente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilFuenteController extends Controller
{
    public function index()
    {
        return PerfilFuente::with('fuenteRef:id,nombre')
            ->latest()
            ->get()
            ->map(function ($p) {
                return [
                    'id'       => $p->id,
                    'perfil'   => $p->perfil,
                    'fuente'   => $p->fuenteRef?->nombre,
                    'cont'     => $p->cont,
                    'letra'    => $p->letra_dir ? Storage::url($p->letra_dir) : null,
                    'contorno' => $p->contorno_dir ? Storage::url($p->contorno_dir) : null,
                ];
            });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'perfil'       => ['required', 'string', 'max:150'],
            'fuente_id'    => ['required', 'integer', 'exists:fuentes,id'],
            'cont'         => ['nullable', 'integer', 'min:0'],
            'letra_dir'    => ['nullable', 'string', 'max:255'],
            'contorno_dir' => ['nullable', 'string', 'max:255'],
        ]);

        $perfil = PerfilFuente::create($data);
        $perfil->load('fuenteRef:id,nombre');

        return response()->json([
            'id'       => $perfil->id,
            'perfil'   => $perfil->perfil,
            'fuente'   => $perfil->fuenteRef?->nombre,
            'cont'     => $perfil->cont,
            'letra'    => $perfil->letra_dir ? Storage::url($perfil->letra_dir) : null,
            'contorno' => $perfil->contorno_dir ? Storage::url($perfil->contorno_dir) : null,
        ], 201);
    }

    // DELETE /perfiles/{id} (con el id del perfil)
    public function destroy(int $id)
    {
        $perfil = PerfilFuente::findOrFail($id);

        // Borra los archivos o directorios asociados, si existen
        if ($perfil->letra_dir && Storage::disk('public')->exists($perfil->letra_dir)) {
             // Asume que letra_dir es una ruta a un archivo
             Storage::disk('public')->delete($perfil->letra_dir);
        }
        if ($perfil->contorno_dir && Storage::disk('public')->exists($perfil->contorno_dir)) {
             // Asume que contorno_dir es una ruta a un archivo
             Storage::disk('public')->delete($perfil->contorno_dir);
        }

        $perfil->delete();

        return response()->json(['ok' => true]);
    }
}