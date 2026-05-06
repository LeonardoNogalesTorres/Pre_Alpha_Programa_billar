<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\Pedido;
use Carbon\Carbon;

class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::all();
        return view('dashboard', compact('mesas'));
    }

    /**
     * OPCIÓN 1: Tarifa Plana 1 Hora (30 Bs)
     */
    public function abrirMesa($id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->update([
            'estado' => 'ocupada',
            'hora_inicio' => Carbon::now('America/La_Paz'),
            'tipo_pago' => 'hora_fija'
        ]);
        return redirect()->back();
    }

    /**
     * OPCIÓN 2: Tiempo Libre (1 Bs cada 2 min)
     */
    public function abrirMesaBloques($id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->update([
            'estado' => 'ocupada',
            'hora_inicio' => Carbon::now('America/La_Paz'),
            'tipo_pago' => 'bloques'
        ]);
        return redirect()->back();
    }

    public function anadirProducto(Request $request, $id)
    {
        $producto = Producto::findOrFail($request->producto_id);

        Pedido::create([
            'mesa_id' => $id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario' => $producto->precio_venta
        ]);

        return redirect()->back()->with('mensaje', "Añadido: " . $producto->nombre);
    }

    public function cerrarMesa($id)
    {
        $mesa = Mesa::findOrFail($id);

        // Sincronización de tiempo en Bolivia
        $inicio = Carbon::parse($mesa->hora_inicio)->timezone('America/La_Paz');
        $fin = Carbon::now('America/La_Paz');

        $minutos = ceil($inicio->diffInMinutes($fin));
        if ($minutos < 1)
            $minutos = 1;

        if ($mesa->tipo_pago == 'hora_fija') {
            if ($minutos <= 60) {
                $totalBillar = 30.00;
                $detalleTiempo = "Tarifa Plana (1h)";
            } else {
                $minutosExtras = $minutos - 60;
                $totalBillar = 30.00 + ($minutosExtras * 0.5);
                $detalleTiempo = "1h + $minutosExtras min extras";
            }
        } else {
            $totalBillar = ceil($minutos / 2) * 1;
            $detalleTiempo = "$minutos min transcurridos";
        }

        // --- NUEVA LÓGICA: GUARDAR EN HISTORIAL ANTES DE BORRAR ---

        // 1. Guardamos el cobro del tiempo de la mesa
        \DB::table('ventas_reporte')->insert([
            'mesa_id' => $id,
            'producto_nombre' => 'Uso de Mesa: ' . $detalleTiempo,
            'precio_cobrado' => $totalBillar,
            'tipo_pago_mesa' => $mesa->tipo_pago,
            'fecha_venta' => Carbon::now('America/La_Paz')
        ]);

        // 2. Obtenemos los pedidos actuales para guardarlos uno por uno
        $pedidosActuales = Pedido::where('mesa_id', $id)->with('producto')->get();

        foreach ($pedidosActuales as $pedido) {
            \DB::table('ventas_reporte')->insert([
                'mesa_id' => $id,
                'producto_nombre' => $pedido->producto->nombre,
                'precio_cobrado' => $pedido->precio_unitario,
                'tipo_pago_mesa' => $mesa->tipo_pago,
                'fecha_venta' => Carbon::now('America/La_Paz')
            ]);
        }

        // Sumamos consumos para el mensaje de pantalla
        $totalComida = $pedidosActuales->sum('precio_unitario');
        $totalGeneral = $totalBillar + $totalComida;

        // Limpiar datos de la mesa para la siguiente sesión
        Pedido::where('mesa_id', $id)->delete();
        $mesa->update([
            'estado' => 'disponible',
            'hora_inicio' => null,
            'tipo_pago' => 'hora_fija'
        ]);

        return redirect()->back()->with(
            'mensaje',
            "Cobro Final: " . number_format($totalGeneral, 2) . " Bs. (Billar: " . number_format($totalBillar, 2) . " Bs - $detalleTiempo)"
        );
    }

    public function exportarExcel()
    {
        // Nombre del archivo con la fecha actual
        $fileName = 'reporte_ventas_diario_' . date('d-m-Y') . '.csv';

        // Obtenemos TODO lo acumulado en la tabla de historial de HOY
        $ventasHistorial = \DB::table('ventas_reporte')
            ->whereDate('fecha_venta', Carbon::today())
            ->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($ventasHistorial) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM para Excel

            // Encabezados del reporte diario
            fputcsv($file, ['ID Registro', 'Mesa', 'Descripción/Producto', 'Monto (Bs)', 'Modo de Juego', 'Hora de Venta']);

            foreach ($ventasHistorial as $venta) {
                fputcsv($file, [
                    $venta->id,
                    "Mesa #" . $venta->mesa_id,
                    $venta->producto_nombre,
                    number_format($venta->precio_cobrado, 2),
                    $venta->tipo_pago_mesa == 'bloques' ? 'Tiempo Libre' : 'Hora Fija',
                    date('H:i:s', strtotime($venta->fecha_venta))
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}