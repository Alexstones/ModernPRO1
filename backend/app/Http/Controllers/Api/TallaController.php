<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Talla;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TallaController extends Controller
{
    public function index()
    {
        return Talla::select('id','categoria','talle','ancho','alto')
            ->orderBy('categoria')
            ->orderBy('talle')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'categoria' => ['required','string', Rule::in(['CAMISETAS','MANGAS','SHORT'])],
            'talle'     => ['required','string','max:50'],
            'ancho'     => ['required','numeric','min:0'],
            'alto'      => ['required','numeric','min:0'],
        ]);

        // Validación manual de duplicado (evita que caiga al índice único y explote en 500)
        $existe = Talla::where('categoria', $data['categoria'])
            ->where('talle', $data['talle'])
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Validación fallida',
                'errors'  => [
                    'talle' => ['Ya existe ese talle para la categoría seleccionada.']
                ]
            ], 422);
        }

        try {
            $talla = Talla::create($data);
            return response()->json($talla->fresh(), 201);
        } catch (QueryException $e) {
            // Captura cualquier SQL y devuélvelo legible (sin 500)
            Log::error('Error al crear talla', ['code'=>$e->getCode(),'msg'=>$e->getMessage()]);
            return response()->json([
                'message' => 'No se pudo guardar la talla',
                'sqlstate'=> $e->getCode(),
                'detail'  => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Excepción inesperada al crear talla', ['msg'=>$e->getMessage()]);
            return response()->json([
                'message' => 'Error inesperado',
                'detail'  => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Talla $talla)
    {
        $data = $request->validate([
            'categoria' => ['sometimes','required','string', Rule::in(['CAMISETAS','MANGAS','SHORT'])],
            'talle'     => ['sometimes','required','string','max:50'],
            'ancho'     => ['sometimes','required','numeric','min:0'],
            'alto'      => ['sometimes','required','numeric','min:0'],
        ]);

        // Validación manual de duplicado en update
        if (isset($data['categoria']) || isset($data['talle'])) {
            $categoria = $data['categoria'] ?? $talla->categoria;
            $talle     = $data['talle']     ?? $talla->talle;

            $existe = Talla::where('categoria', $categoria)
                ->where('talle', $talle)
                ->where('id', '!=', $talla->id)
                ->exists();

            if ($existe) {
                return response()->json([
                    'message' => 'Validación fallida',
                    'errors'  => [
                        'talle' => ['Ya existe ese talle para la categoría seleccionada.']
                    ]
                ], 422);
            }
        }

        $talla->update($data);
        return response()->json($talla->fresh());
    }

    public function destroy(Talla $talla)
    {
        $talla->delete();
        return response()->noContent();
    }
}
