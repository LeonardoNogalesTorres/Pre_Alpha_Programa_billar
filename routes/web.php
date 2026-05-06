<?php

use App\Http\Controllers\MesaController;
use Illuminate\Support\Facades\Route;

// Vista principal (Dashboard)
Route::get('/', [MesaController::class, 'index'])->name('dashboard');

// --- RUTAS DE APERTURA ---
// Opción A: 1 Hora Fija (30 Bs)
Route::get('/mesas/abrir/{id}', [MesaController::class, 'abrirMesa'])->name('mesas.abrir');

// Opción B: Tiempo Libre (1 Bs / 2 min)
Route::get('/mesas/abrir-bloques/{id}', [MesaController::class, 'abrirMesaBloques'])->name('mesas.abrirBloques');

// --- RUTAS DE GESTIÓN ---
// Añadir productos (Comida/Bebida)
Route::post('/mesa/anadir-producto/{id}', [MesaController::class, 'anadirProducto'])->name('mesas.anadirProducto');

// --- RUTAS DE CIERRE Y COBRO ---
// Una sola ruta para cerrar y cobrar es suficiente
Route::get('/mesas/cerrar/{id}', [MesaController::class, 'cerrarMesa'])->name('mesas.cerrar');

Route::get('/exportar-ventas', [App\Http\Controllers\MesaController::class, 'exportarExcel'])->name('ventas.exportar');