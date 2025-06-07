<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReservaApiController;

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
