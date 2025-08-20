<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MoldeController;
use App\Http\Controllers\FuenteController;
use App\Http\Controllers\PerfilFuenteController;
use App\Http\Controllers\Api\TallaController;
use App\Http\Controllers\MolderiaController; // ✅ nuevo
use App\Http\Controllers\DisenoController;   // ✅ NUEVO: controlador de Diseño

// ----------------------
// DEBUG / Health
// ----------------------
Route::get('/__routes_loaded', fn () => response()->json(['api_loaded' => true]));
Route::get('/ping', fn () => response()->json(['ok' => true]));

// ----------------------
// Preflight CORS
// ----------------------
Route::options('{any}', fn () => response()->noContent())->where('any', '.*');

// ----------------------
// PDF (otros)
// ----------------------
Route::post('/diag/excel', [PdfController::class, 'diagExcel']);
Route::post('/generate-pdf', [PdfController::class, 'generate']);

// ----------------------
// Auth & Productos
// ----------------------
Route::post('/register',  [AuthController::class, 'register']);
Route::post('/productos', [ProductController::class, 'store']);

// ----------------------
// Molde clásico (existente)
// ----------------------
Route::get('/moldes',               [MoldeController::class, 'index']);
Route::post('/moldes',              [MoldeController::class, 'store']);
Route::delete('/moldes/{molde}',    [MoldeController::class, 'destroy']);

// ----------------------
// Fuentes
// ----------------------
Route::get('/fuentes',               [FuenteController::class, 'index']);
Route::post('/fuentes',              [FuenteController::class, 'store']);
Route::delete('/fuentes/{fuente}',   [FuenteController::class, 'destroy']);

// ----------------------
// Perfiles de fuente
// ----------------------
Route::get('/perfiles',                 [PerfilFuenteController::class, 'index']);
Route::post('/perfiles',                [PerfilFuenteController::class, 'store']);
Route::delete('/perfiles/{perfil}',     [PerfilFuenteController::class, 'destroy']);

// ----------------------
// Tallas (rutas explícitas)
// ----------------------
Route::get('/tallas',               [TallaController::class, 'index']);
Route::post('/tallas',              [TallaController::class, 'store']);
Route::put('/tallas/{talla}',       [TallaController::class, 'update']);
Route::delete('/tallas/{talla}',    [TallaController::class, 'destroy']);

// ===============================
// ✅ Moldería (no colisiona con /moldes)
// ===============================
Route::post('/molderia',                [MolderiaController::class, 'store']);   // subir colección y piezas
Route::get('/molderia/{molderia}',      [MolderiaController::class, 'show']);    // ver una colección (con piezas)

// ===============================
// ✅ NUEVO: Diseño (paleta, presets y PDF)
// ===============================

// Paleta de colores
Route::get('/diseno/paleta',            [DisenoController::class, 'getPalette']);
Route::post('/diseno/paleta',           [DisenoController::class, 'savePalette']);

// Presets de diseño (texto, tamaño, fuente, colores, trazo, etc.)
Route::get('/diseno/presets',           [DisenoController::class, 'indexPresets']);
Route::post('/diseno/presets',          [DisenoController::class, 'storePreset']);
Route::put('/diseno/presets/{preset}',  [DisenoController::class, 'updatePreset']);
Route::delete('/diseno/presets/{preset}', [DisenoController::class, 'destroyPreset']);

// Exportar PDF del diseño (usa Dompdf)
Route::post('/diseno/pdf',              [DisenoController::class, 'exportPdf'])->name('diseno.pdf');
