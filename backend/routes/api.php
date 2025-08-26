<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MoldeController;
use App\Http\Controllers\FuenteController;
use App\Http\Controllers\PerfilFuenteController;
use App\Http\Controllers\Api\TallaController;
use App\Http\Controllers\MolderiaController;
use App\Http\Controllers\DisenoController;

/**
 * ----------------------
 * DEBUG / Health
 * ----------------------
 */
Route::get('/__routes_loaded', fn () => response()->json(['api_loaded' => true]));
Route::get('/ping', fn () => response()->json(['ok' => true]));

/**
 * ----------------------
 * Preflight CORS
 * ----------------------
 */
Route::options('{any}', fn () => response()->noContent())->where('any', '.*');

/**
 * ----------------------
 * PDF (diagnóstico y básico)
 * ----------------------
 */
Route::post('/diag/excel', [PdfController::class, 'diagExcel']);
Route::post('/generate-pdf', [PdfController::class, 'generate']);

/**
 * ----------------------
 * Auth & Productos
 * ----------------------
 */
Route::post('/register',  [AuthController::class, 'register']);
Route::post('/productos', [ProductController::class, 'store']);

/**
 * ----------------------
 * Molde clásico
 * ----------------------
 */
Route::get('/moldes',               [MoldeController::class, 'index']);
Route::post('/moldes',              [MoldeController::class, 'store']);
Route::delete('/moldes/{molde}',    [MoldeController::class, 'destroy']);

/**
 * ----------------------
 * Fuentes
 * ----------------------
 */
Route::get('/fuentes',               [FuenteController::class, 'index']);
Route::post('/fuentes',              [FuenteController::class, 'store']);
Route::delete('/fuentes/{fuente}',   [FuenteController::class, 'destroy']);

/**
 * ----------------------
 * Perfiles de fuente
 * ----------------------
 */
Route::get('/perfiles',                 [PerfilFuenteController::class, 'index']);
Route::post('/perfiles',                [PerfilFuenteController::class, 'store']);
Route::delete('/perfiles/{perfil}',     [PerfilFuenteController::class, 'destroy']);

/**
 * ----------------------
 * Tallas
 * ----------------------
 */
Route::get('/tallas',               [TallaController::class, 'index']);
Route::post('/tallas',              [TallaController::class, 'store']);
Route::put('/tallas/{talla}',       [TallaController::class, 'update']);
Route::delete('/tallas/{talla}',    [TallaController::class, 'destroy']);

/**
 * ===============================
 * Moldería
 * ===============================
 */
Route::post('/molderia',                [MolderiaController::class, 'store']);
Route::get('/molderia/{molderia}',      [MolderiaController::class, 'show']);

/**
 * ===============================
 * Diseño (paleta, presets y PDF)
 * ===============================
 */
Route::get('/diseno/paleta',            [DisenoController::class, 'getPalette']);
Route::post('/diseno/paleta',           [DisenoController::class, 'savePalette']);
Route::get('/diseno/presets',           [DisenoController::class, 'indexPresets']);
Route::post('/diseno/presets',          [DisenoController::class, 'storePreset']);
Route::put('/diseno/presets/{preset}',  [DisenoController::class, 'updatePreset']);
Route::delete('/diseno/presets/{preset}', [DisenoController::class, 'destroyPreset']);
Route::post('/diseno/pdf',              [DisenoController::class, 'exportPdf'])->name('diseno.pdf');

/**
 * ===============================
 * NEW: Print-ready (imposición, zip, nomenclatura, curvas)
 * ===============================
 */
Route::post('/print/generate-batch', [PdfController::class, 'generateBatch']);
