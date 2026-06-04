<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mesa;
use Carbon\Carbon; // Herramienta para manejar tiempos en PHP

class MesaApiController extends Controller
{
    public function verificarDisponibilidad(Request $request)
    {
        try {
            // 1. Buscamos TODAS las mesas que estén disponibles en tu ENUM
            $mesasLibres = Mesa::where('estado', 'disponible')->get();

            // Si hay al menos una mesa libre
            if ($mesasLibres->count() > 0) {
                // Extraemos solo los números de las mesas (ej: [1, 4, 5])
                $numerosMesas = $mesasLibres->pluck('numero')->toArray();

                return response()->json([
                    'disponible' => true,
                    'cantidad' => $mesasLibres->count(),
                    'mesas' => $numerosMesas // Enviamos la lista de números disponibles
                ], 200);
            }

            // 2. Si TODAS las mesas están ocupadas (Mantenemos tu lógica que ya funciona perfecto)
            $mesaMasAntigua = Mesa::where('estado', 'ocupada')
                ->orderBy('hora_inicio', 'asc')
                ->first();

            $horaLiberacion = "21:30:00";
            if ($mesaMasAntigua && $mesaMasAntigua->hora_inicio) {
                $inicio = Carbon::parse($mesaMasAntigua->hora_inicio);
                $horaLiberacion = $inicio->addHour()->toTimeString();
            }

            return response()->json([
                'disponible' => false,
                'proxima_hora_libre' => $horaLiberacion
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'disponible' => false,
                'proxima_hora_libre' => '21:30:00',
                'error_interno' => $e->getMessage()
            ], 200);
        }
    }

    public function guardarReserva(Request $request)
    {
        try {
            $nombreCliente = $request->input('cliente');
            $mesaObjetivo = $request->input('mesa_objetivo'); // Recibe el número del botón presionado

            // Si el bot nos envió una mesa específica elegida por el usuario
            if ($mesaObjetivo) {
                Mesa::where('numero', $mesaObjetivo)->update([
                    'estado' => 'ocupada',
                    'hora_inicio' => Carbon::now()
                ]);

                return response()->json([
                    'exito' => true,
                    'mensaje' => "Mesa #{$mesaObjetivo} asignada con éxito."
                ], 201);
            }

            // Lógica de respaldo por si viene del flujo de sala llena (toma la más antigua)
            $mesaOcupada = Mesa::where('estado', 'ocupada')
                ->orderBy('hora_inicio', 'asc')
                ->first();

            if ($mesaOcupada) {
                Mesa::where('numero', $mesaOcupada->numero)->update([
                    'hora_inicio' => Carbon::now()
                ]);

                return response()->json([
                    'exito' => true,
                    'mensaje' => "Mesa #{$mesaOcupada->numero} reasignada con éxito."
                ], 201);
            }

            return response()->json(['exito' => false, 'mensaje' => 'No hay mesas.'], 400);

        } catch (\Exception $e) {
            return response()->json(['exito' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }
}