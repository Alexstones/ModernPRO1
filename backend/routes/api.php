<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MoldeController;
use App\Http\Controllers\FuenteController;
use App\Http\Controllers\PerfilFuenteController;
use App\Http\Controllers\Api\TallaController;

// DEBUG: BORRAR LUEGO
Route::get('/__routes_loaded', fn () => response()->json(['api_loaded' => true]));


Route::options('{any}', fn () => response()->noContent())->where('any', '.*');

Route::get('/ping', fn () => response()->json(['ok' => true]));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/productos', [ProductController::class, 'store']);

Route::get('/moldes',  [MoldeController::class, 'index']);
Route::post('/moldes', [MoldeController::class, 'store']);
Route::delete('/moldes/{molde}', [MoldeController::class, 'destroy']);

Route::get('/fuentes',  [FuenteController::class, 'index']);
Route::post('/fuentes', [FuenteController::class, 'store']);
Route::delete('/fuentes/{fuente}', [FuenteController::class, 'destroy']);

Route::get('/perfiles',  [PerfilFuenteController::class, 'index']);
Route::post('/perfiles', [PerfilFuenteController::class, 'store']);
Route::delete('/perfiles/{perfil}', [PerfilFuenteController::class, 'destroy']);

Route::post('/generate-pdf', [PdfController::class, 'generate']);

// --- Tallas ---  (usa rutas explícitas para descartar problemas con apiResource)
Route::get('/tallas',            [TallaController::class, 'index']);
Route::post('/tallas',           [TallaController::class, 'store']);
Route::put('/tallas/{talla}',    [TallaController::class, 'update']);
Route::delete('/tallas/{talla}', [TallaController::class, 'destroy']);
