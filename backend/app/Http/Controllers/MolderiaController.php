<?php

namespace App\Http\Controllers;

use App\Models\Molderia;
use App\Models\MolderiaPieza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MolderiaController extends Controller
{
    public function store(Request $request)
    {
        // Validación básica
        $validated = $request->validate([
            'coleccion'        => 'required|string|max:120',
            'notaGeneral'      => 'nullable|string',
            'tallasColeccion'  => 'required|string', // JSON string (array)
            'meta'             => 'required|string', // JSON string (array de piezas)
            'files'            => 'required|array|min:1',
            'files.*'          => 'file|mimes:svg,png,jpg,jpeg,pdf|max:51200', // 50MB c/u
        ]);

        // Parsear JSON
        $tallas = json_decode($validated['tallasColeccion'], true);
        $meta   = json_decode($validated['meta'], true);

        if (!is_array($tallas)) $tallas = [];
        if (!is_array($meta)) {
            return response()->json(['message' => 'Campo "meta" debe ser un JSON válido.'], 422);
        }

        // Crear registro de moldería
        $molderia = Molderia::create([
            'coleccion'    => $validated['coleccion'],
            'nota_general' => $validated['notaGeneral'] ?? null,
            'tallas'       => $tallas,
            'total_piezas' => 0,
        ]);

        // Carpeta destino: storage/app/public/molderia/<slug-coleccion>/<fecha>
        $dir = 'molderia/' . Str::slug($molderia->coleccion) . '/' . now()->format('Ymd-His');

        $subidas = [];
        $metaByName = collect($meta)->keyBy(fn ($m) => $m['nombreOriginal'] ?? ''); // mapea por nombre original

        foreach ($request->file('files', []) as $idx => $file) {
            $orig = $file->getClientOriginalName();
            $pieceMeta = $metaByName->get($orig, []);

            // Nombre final único preservando el original
            $safeName = Str::limit(Str::slug(pathinfo($orig, PATHINFO_FILENAME)), 120, '');
            $ext = strtolower($file->getClientOriginalExtension());
            $finalName = uniqid() . '_' . ($safeName ?: 'pieza') . '.' . $ext;

            // Guardar en disco 'public'
            $path = $file->storeAs($dir, $finalName, 'public');

            $pieza = MolderiaPieza::create([
                'molderia_id'    => $molderia->id,
                'nombre_original'=> $orig,
                'nombre_pieza'   => $pieceMeta['nombrePieza'] ?? pathinfo($orig, PATHINFO_FILENAME),
                'prenda'         => $pieceMeta['prenda'] ?? '',
                'genero'         => $pieceMeta['genero'] ?? 'Unisex',
                'lado'           => $pieceMeta['lado'] ?? 'Centro',
                'talla'          => $pieceMeta['talla'] ?? null,
                'cantidad'       => isset($pieceMeta['cantidad']) ? max(1, (int)$pieceMeta['cantidad']) : 1,
                'notas'          => $pieceMeta['notas'] ?? null,
                'orden'          => (int)($pieceMeta['order'] ?? $idx),
                'mime'           => $file->getClientMimeType(),
                'size'           => $file->getSize(),
                'path'           => $path, // relativo al disco
            ]);

            $subidas[] = [
                'id'       => $pieza->id,
                'original' => $orig,
                'url'      => Storage::disk('public')->url($path),
                'meta'     => [
                    'nombrePieza' => $pieza->nombre_pieza,
                    'prenda'      => $pieza->prenda,
                    'genero'      => $pieza->genero,
                    'lado'        => $pieza->lado,
                    'talla'       => $pieza->talla,
                    'cantidad'    => $pieza->cantidad,
                    'notas'       => $pieza->notas,
                    'orden'       => $pieza->orden,
                ]
            ];
        }

        // Actualizar total
        $molderia->update(['total_piezas' => count($subidas)]);

        return response()->json([
            'message'   => 'Moldería guardada correctamente.',
            'molderia'  => [
                'id'           => $molderia->id,
                'coleccion'    => $molderia->coleccion,
                'nota_general' => $molderia->nota_general,
                'tallas'       => $molderia->tallas,
                'total_piezas' => $molderia->total_piezas,
                'created_at'   => $molderia->created_at,
            ],
            'piezas'    => $subidas,
        ], 201);
    }

    // (opcional) Ver una moldería
    public function show(Molderia $molderia)
    {
        $molderia->load('piezas');
        // Agregar URLs públicas
        $data = $molderia->toArray();
        $data['piezas'] = collect($data['piezas'])->map(function ($p) {
            $p['url'] = Storage::disk('public')->url($p['path']);
            return $p;
        });
        return response()->json($data);
    }
}
