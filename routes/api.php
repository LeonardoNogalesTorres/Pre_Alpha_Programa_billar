<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MesaApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Esta es la ruta que llamará Node.js: http://localhost/tu-proyecto/public/api/mesas/disponibilidad
Route::get('/mesas/disponibilidad', [MesaApiController::class, 'verificarDisponibilidad']);

// Ruta para guardar la reserva de forma oficial
Route::post('/mesas/reservar', [MesaApiController::class, 'guardarReserva']);