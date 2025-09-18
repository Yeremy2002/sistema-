<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReservaApiController;
use App\Http\Controllers\ClienteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

// Rutas públicas para el calendario y reservas
Route::get('/reservas/calendario', [ReservaApiController::class, 'calendario']);
Route::get('/reservas/disponibilidad', [ReservaApiController::class, 'disponibilidad']);
Route::post('/reservas', [ReservaApiController::class, 'crearReserva']);

// Rutas públicas para búsqueda de clientes
Route::get('/clientes/buscar', [ClienteController::class, 'buscar']);
Route::get('/clientes/buscar-por-dpi/{dpi}', [ClienteController::class, 'buscarPorDpi']);
Route::get('/clientes/buscar-por-nit/{nit}', [ClienteController::class, 'buscarPorNit']);

// Ruta de prueba para CORS
Route::get('/test-cors', function () {
    \Log::info('Test CORS endpoint hit');
    return response()->json([
        'success' => true,
        'message' => 'CORS test successful',
        'timestamp' => now()
    ]);
});

// Ruta de prueba simple para disponibilidad
Route::get('/test-disponibilidad', function () {
    \Log::info('Test disponibilidad endpoint hit');
    return response()->json([
        'success' => true,
        'message' => 'Disponibilidad test successful',
        'habitaciones_count' => \App\Models\Habitacion::count()
    ]);
});
