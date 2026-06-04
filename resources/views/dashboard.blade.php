<h1 style="font-family: Arial; text-align: center; color: #333; margin-bottom: 25px; font-weight: bold;">
    Control de Mesas - Billar Club
</h1>

<!-- Bloque para mostrar el monto cobrado (Mensaje de éxito) -->
@if(session('mensaje'))
    <div style="background: #fff3cd; color: #856404; padding: 15px; margin: 0 auto 20px auto; max-width: 800px; border-radius: 8px; border: 1px solid #ffeeba; font-family: Arial; text-align: center; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        {{ session('mensaje') }}
    </div>
@endif

<!-- Contenedor principal con Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; padding: 20px; font-family: Arial;">

    @foreach($mesas as $mesa)
        <!-- RECUADRO DE LA MESA: Borde definido para la exposición -->
        <div style="padding: 20px; border: 3px solid {{ $mesa->estado == 'disponible' ? '#28a745' : '#dc3545' }}; background: {{ $mesa->estado == 'disponible' ? '#f8fdf9' : '#fff9f9' }}; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.08); transition: transform 0.2s; box-sizing: border-box;">
            
            <h3 style="margin: 0 0 15px 0; border-bottom: 2px solid {{ $mesa->estado == 'disponible' ? '#28a745' : '#dc3545' }}; padding-bottom: 8px; font-size: 18px; font-weight: bold; color: #222;">
                Mesa #{{ $mesa->numero }}
            </h3>

            @if($mesa->estado == 'disponible')
                <div style="text-align: center; padding: 10px 0;">
                    <p style="color: #28a745; font-weight: bold; margin-bottom: 15px;">● Disponible</p>
                    
                    <a href="{{ route('mesas.abrir', $mesa->id) }}"
                       style="background: #28a745; color: white; padding: 12px; text-decoration: none; border-radius: 6px; display: block; margin-bottom: 10px; font-size: 13px; font-weight: bold; text-align: center;">
                       1 HORA (30 Bs)
                    </a>

                    <a href="{{ route('mesas.abrirBloques', $mesa->id) }}"
                       style="background: #007bff; color: white; padding: 12px; text-decoration: none; border-radius: 6px; display: block; font-size: 13px; font-weight: bold; text-align: center;">
                       TIEMPO LIBRE (1 Bs/2m)
                    </a>
                </div>
            @else
                <div style="margin-bottom: 15px;">
                    <p style="color: #dc3545; font-weight: bold; margin: 0 0 8px 0;">● Ocupada</p>
                    <p style="font-size: 14px; margin: 5px 0; color: #444;">
                        <strong>Modo:</strong> 
                        <span style="color: {{ $mesa->tipo_pago == 'bloques' ? '#007bff' : '#28a745' }}; font-weight: bold;">
                            {{ $mesa->tipo_pago == 'bloques' ? 'Tiempo Libre' : 'Hora Fija' }}
                        </span>
                    </p>
                    <p style="font-size: 14px; margin: 5px 0; color: #444;"><strong>Inició:</strong> {{ date('H:i', strtotime($mesa->hora_inicio)) }}</p>
                    
                    @if($mesa->tipo_pago == 'bloques')
                        @php
                            $minutos_actuales = ceil(\Carbon\Carbon::parse($mesa->hora_inicio)->diffInMinutes(\Carbon\Carbon::now('America/La_Paz')));
                        @endphp
                        <div style="background: #e7f3ff; padding: 6px 10px; border-radius: 5px; margin-top: 8px; border: 1px solid #b8daff;">
                            <p style="font-size: 13px; color: #0056b3; margin: 0; font-weight: bold;">
                                Tiempo: {{ $minutos_actuales }} min
                            </p>
                        </div>
                    @endif
                </div>

                <!-- FORMULARIO DE GASTRONOMÍA CORREGIDO (El botón ya no se sale) -->
                <form action="{{ route('mesas.anadirProducto', $mesa->id) }}" method="POST" style="background: rgba(0,0,0,0.04); padding: 12px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e2e2e2; box-sizing: border-box;">
                    @csrf
                    <label style="font-size: 11px; font-weight: bold; display: block; margin-bottom: 6px; color: #555;">AÑADIR CONSUMO:</label>
                    <div style="display: block;">
                        <select name="producto_id" required style="width: 100%; padding: 6px; border-radius: 5px; border: 1px solid #ccc; font-size: 12px; margin-bottom: 8px; display: block; box-sizing: border-box;">
                            @foreach(\App\Models\Producto::all() as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->nombre }} - {{ $prod->precio_venta }}Bs</option>
                            @endforeach
                        </select>
                        <button type="submit" style="width: 100%; background: #6c757d; color: white; border: none; padding: 7px; cursor: pointer; border-radius: 5px; font-weight: bold; font-size: 12px; display: block; box-sizing: border-box;">
                            + Añadir Producto
                        </button>
                    </div>
                </form>

                <!-- LISTA DE CONSUMO -->
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 11px; font-weight: bold; color: #555;">CONSUMO ACTUAL:</label>
                    <ul style="font-size: 13px; margin-top: 5px; list-style: none; padding: 0; max-height: 100px; overflow-y: auto; border-top: 1px solid #eee;">
                        @php 
                            $pedidosMesa = \App\Models\Pedido::where('mesa_id', $mesa->id)->with('producto')->get();
                        @endphp
                        
                        @forelse($pedidosMesa as $pedido)
                            <li style="border-bottom: 1px dashed #eee; padding: 5px 0; display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #333;">{{ $pedido->producto->nombre }}</span>
                                <span style="font-weight: bold; color: #111;">{{ $pedido->precio_unitario }} Bs</span>
                            </li>
                        @empty
                            <li style="color: #999; font-style: italic; font-size: 12px; padding: 8px 0; text-align: center;">Sin extras</li>
                        @endforelse
                    </ul>
                </div>

                <!-- BOTÓN CERRAR (Llama a la alerta personalizada integrada) -->
                <button type="button" 
                    onclick="mostrarModalCobro('Mesa #{{ $mesa->numero }}', '{{ route('mesas.cerrar', $mesa->id) }}')"
                    style="width: 100%; border: none; background: #dc3545; color: white; padding: 12px; border-radius: 8px; display: block; text-align: center; font-weight: bold; box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3); font-size: 14px; cursor: pointer;">
                    Cerrar y Cobrar Todo
                </button>
            @endif
        </div>
    @endforeach
</div>

<div style="text-align: center; margin: 30px 0 50px 0;">
    <hr style="border: 0; border-top: 1px solid #ddd; margin-bottom: 25px;">
    <a href="{{ route('ventas.exportar') }}" 
       style="background: #1d6f42; color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-weight: bold; font-family: Arial; font-size: 15px; box-shadow: 0 4px 10px rgba(29, 111, 66, 0.3); display: inline-flex; align-items: center; gap: 10px;">
        <span>📊</span> DESCARGAR REPORTE DE VENTAS (EXCEL/CSV)
    </a>
</div>


<!-- ========================================== -->
<!-- MODAL DE ALERTA INTEGRADO EN LA PÁGINA     -->
<!-- ========================================== -->
<div id="modalConfirmacionCobro" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; h-screen: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); font-family: Arial; align-items: center; justify-content: center;">
    <div style="background-color: #ffffff; padding: 25px; border-radius: 12px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.25); animation: fadeEffect 0.3s;">
        
        <!-- Icono de advertencia estético -->
        <div style="background: #fde8e8; color: #e02424; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; font-size: 24px; font-weight: bold;">
            ⚠️
        </div>

        <h2 style="margin: 0 0 10px 0; color: #111827; font-size: 18px; font-weight: bold;">¿Finalizar Turno?</h2>
        <p style="margin: 0 0 25px 0; color: #6b7280; font-size: 14px; line-height: 1.5;">
            Estás a punto de cerrar y cobrar todo lo acumulado en la <span id="textoMesaModal" style="font-weight: bold; color: #111;"></span>. ¿Deseas continuar?
        </p>
        
        <!-- Acciones del Modal -->
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" onclick="cerrarModalCobro()" style="flex: 1; background: #e5e7eb; color: #374151; border: none; padding: 10px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px;">
                Cancelar
            </button>
            <a id="botonConfirmarCobro" href="#" style="flex: 1; background: #dc3545; color: white; padding: 10px; border-radius: 6px; font-weight: bold; text-decoration: none; text-align: center; font-size: 14px;">
                Sí, Cobrar
            </a>
        </div>
    </div>
</div>

<!-- Scripts lógicos del modal para simular la alerta nativa -->
<script>
    function mostrarModalCobro(nombreMesa, urlDestino) {
        const modal = document.getElementById('modalConfirmacionCobro');
        document.getElementById('textoMesaModal').innerText = nombreMesa;
        document.getElementById('botonConfirmarCobro').setAttribute('href', urlDestino);
        
        // Activa el display flex para centrarlo en pantalla
        modal.style.display = 'flex';
    }

    function cerrarModalCobro() {
        document.getElementById('modalConfirmacionCobro').style.display = 'none';
    }

    // Cerrar el modal de manera intuitiva si hacen clic fuera del recuadro blanco
    window.onclick = function(event) {
        const modal = document.getElementById('modalConfirmacionCobro');
        if (event.target == modal) {
            cerrarModalCobro();
        }
    }
</script>

<style>
    /* Efecto de entrada suave para la exposición */
    @keyframes fadeEffect {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>