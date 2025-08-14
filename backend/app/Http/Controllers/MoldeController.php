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
        $moldes = Molde::latest()->get()->map(fn ($m) => $this->mapMolde($m));
        return response()->json(['moldes' => $moldes]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'        => ['required','string','max:255'],
            // Se agregaron los tipos de archivo ttf y otf a todas las reglas de validación
            'camiseta_izq'  => ['nullable','file','mimes:pdf,svg,png,jpg,jpeg,ai,eps,zip,ttf,otf','max:20480'],
            'camiseta_der'  => ['nullable','file','mimes:pdf,svg,png,jpg,jpeg,ai,eps,zip,ttf,otf','max:20480'],
            'short_izq'     => ['nullable','file','mimes:pdf,svg,png,jpg,jpeg,ai,eps,zip,ttf,otf','max:20480'],
            'short_der'     => ['nullable','file','mimes:pdf,svg,png,jpg,jpeg,ai,eps,zip,ttf,otf','max:20480'],
            'manga_izq'     => ['nullable','file','mimes:pdf,svg,png,jpg,jpeg,ai,eps,zip,ttf,otf','max:20480'],
            'manga_der'     => ['nullable','file','mimes:pdf,svg,png,jpg,jpeg,ai,eps,zip,ttf,otf','max:20480'],
        ]);

        $dir = 'moldes/'.Str::slug($request->nombre).'-'.Str::random(6);

        $data = ['nombre' => $request->nombre];
        foreach (['camiseta_izq','camiseta_der','short_izq','short_der','manga_izq','manga_der'] as $k) {
            if ($request->file($k)) {
                $data[$k] = $request->file($k)->store($dir, 'public');
            }
        }

        $molde = Molde::create($data);
        return response()->json(['molde' => $this->mapMolde($molde)], 201);
    }

    public function destroy(Molde $molde)
    {
        foreach (['camiseta_izq','camiseta_der','short_izq','short_der','manga_izq','manga_der'] as $k) {
            if ($molde->$k) {
                Storage::disk('public')->delete($molde->$k);
            }
        }
        $molde->delete();
        return response()->json(['ok' => true]);
    }

    private function mapMolde(Molde $m): array
    {
        $f = fn($path) => $path ? ['url' => Storage::url($path), 'name' => basename($path)] : null;

        return [
            'id'           => $m->id,
            'nombre'       => $m->nombre,
            'camiseta_izq' => $f($m->camiseta_izq),
            'camiseta_der' => $f($m->camiseta_der),
            'short_izq'    => $f($m->short_izq),
            'short_der'    => $f($m->short_der),
            'manga_izq'    => $f($m->manga_izq),
            'manga_der'    => $f($m->manga_der),
            'created_at'   => $m->created_at,
        ];
    }
}