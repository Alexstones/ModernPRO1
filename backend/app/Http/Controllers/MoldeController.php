<?php

namespace App\Http\Controllers;

use App\Models\Molde;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MoldeController extends Controller
{
    public function index()
    {
        $items = Molde::orderByDesc('id')->get()->map(function (Molde $m) {
            return $this->serialize($m);
        });

        return response()->json($items);
    }

    public function store(Request $request)
    {
        // Validación: nombre + archivos opcionales (svg/png/jpg/jpeg/pdf)
        $validated = $request->validate([
            'nombre' => 'required|string|max:160',
            'camiseta_frente'  => 'nullable|file|mimes:svg,png,jpg,jpeg,pdf|max:51200',
            'camiseta_espalda' => 'nullable|file|mimes:svg,png,jpg,jpeg,pdf|max:51200',
            'short_izq'        => 'nullable|file|mimes:svg,png,jpg,jpeg,pdf|max:51200',
            'short_der'        => 'nullable|file|mimes:svg,png,jpg,jpeg,pdf|max:51200',
            'manga_izq'        => 'nullable|file|mimes:svg,png,jpg,jpeg,pdf|max:51200',
            'manga_der'        => 'nullable|file|mimes:svg,png,jpg,jpeg,pdf|max:51200',
        ]);

        $dir = 'moldes/' . Str::slug($validated['nombre']) . '/' . now()->format('Ymd-His');

        $data = ['nombre' => $validated['nombre']];
        foreach (['camiseta_frente','camiseta_espalda','short_izq','short_der','manga_izq','manga_der'] as $field) {
            if ($file = $request->file($field)) {
                $orig = $file->getClientOriginalName();
                $ext  = strtolower($file->getClientOriginalExtension());
                $safe = Str::limit(Str::slug(pathinfo($orig, PATHINFO_FILENAME)), 120, '');
                $name = uniqid()."_{$safe}.{$ext}";
                $path = $file->storeAs($dir, $name, 'public');

                $data["{$field}_path"] = $path;
                $data["{$field}_orig"] = $orig;
            }
        }

        $molde = Molde::create($data);

        return response()->json($this->serialize($molde), 201);
    }

    public function destroy(Molde $molde)
    {
        // Borra archivos si existen
        foreach (['camiseta_frente','camiseta_espalda','short_izq','short_der','manga_izq','manga_der'] as $field) {
            $path = $molde->getAttribute("{$field}_path");
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        $molde->delete();

        return response()->json(['deleted' => true]);
    }

    private function serialize(Molde $m): array
    {
        $url = fn($p) => $p ? Storage::disk('public')->url($p) : null;

        return [
            'id'     => $m->id,
            'nombre' => $m->nombre,

            'camiseta_frente'  => $m->camiseta_frente_path  ? ['name'=>$m->camiseta_frente_orig,  'url'=>$url($m->camiseta_frente_path)] : null,
            'camiseta_espalda' => $m->camiseta_espalda_path ? ['name'=>$m->camiseta_espalda_orig, 'url'=>$url($m->camiseta_espalda_path)] : null,
            'short_izq'        => $m->short_izq_path        ? ['name'=>$m->short_izq_orig,        'url'=>$url($m->short_izq_path)]        : null,
            'short_der'        => $m->short_der_path        ? ['name'=>$m->short_der_orig,        'url'=>$url($m->short_der_path)]        : null,
            'manga_izq'        => $m->manga_izq_path        ? ['name'=>$m->manga_izq_orig,        'url'=>$url($m->manga_izq_path)]        : null,
            'manga_der'        => $m->manga_der_path        ? ['name'=>$m->manga_der_orig,        'url'=>$url($m->manga_der_path)]        : null,

            'created_at' => $m->created_at,
        ];
    }
}
