<h1 style="font-family: Arial; text-align: center; color: #333;">Control de Mesas - Billar Club</h1>

<!-- Bloque para mostrar el monto cobrado (Mensaje de éxito) -->
@if(session('mensaje'))
    <div style="background: #fff3cd; color: #856404; padding: 15px; margin: 0 auto 20px auto; max-width: 800px; border-radius: 8px; border: 1px solid #ffeeba; font-family: Arial; text-align: center; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        {{ session('mensaje') }}
    </div>
@endif

<!-- Contenedor principal con Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; padding: 20px; font-family: Arial;">

    @foreach($mesas as $mesa)
        <div style="padding: 20px; border: 1px solid #ddd; background: {{ $mesa->estado == 'disponible' ? '#f0fff4' : '#fff5f5' }}; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s;">
            
            <h3 style="margin: 0 0 10px 0; border-bottom: 2px solid {{ $mesa->estado == 'disponible' ? '#28a745' : '#dc3545' }}; padding-bottom: 5px;">
                Mesa #{{ $mesa->numero }}
            </h3>

            @if($mesa->estado == 'disponible')
                <div style="text-align: center; padding: 10px 0;">
                    <p style="color: #28a745; font-weight: bold;">● Disponible</p>
                    
                    <!-- Botón Hora Fija (Opción A) -->
                    <a href="{{ route('mesas.abrir', $mesa->id) }}"
                       style="background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px; display: block; margin-bottom: 8px; font-size: 12px; font-weight: bold;">
                       1 HORA (30 Bs)
                    </a>

                    <!-- Botón Tiempo Libre (Opción B) -->
                    <a href="{{ route('mesas.abrirBloques', $mesa->id) }}"
                       style="background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px; display: block; font-size: 12px; font-weight: bold;">
                       TIEMPO LIBRE (1 Bs/2m)
                    </a>
                </div>
            @else
                <div style="margin-bottom: 15px;">
                    <p style="color: #dc3545; font-weight: bold; margin: 0;">● Ocupada</p>
                    <p style="font-size: 14px; margin: 5px 0;">
                        <strong>Modo:</strong> 
                        <span style="color: {{ $mesa->tipo_pago == 'bloques' ? '#007bff' : '#28a745' }};">
                            {{ $mesa->tipo_pago == 'bloques' ? 'Tiempo Libre' : 'Hora Fija' }}
                        </span>
                    </p>
                    <p style="font-size: 14px; margin: 5px 0;"><strong>Inició:</strong> {{ date('H:i', strtotime($mesa->hora_inicio)) }}</p>
                    
                    <!-- Solo muestra el tiempo si es modo bloques (Tiempo Libre) -->
                    @if($mesa->tipo_pago == 'bloques')
                        @php
                            $minutos_actuales = ceil(\Carbon\Carbon::parse($mesa->hora_inicio)->diffInMinutes(\Carbon\Carbon::now('America/La_Paz')));
                        @endphp
                        <div style="background: #e7f3ff; padding: 5px 10px; border-radius: 5px; margin-top: 5px;">
                            <p style="font-size: 13px; color: #0056b3; margin: 0; font-weight: bold;">
                                Tiempo: {{ $minutos_actuales }} min
                            </p>
                        </div>
                    @endif
                </div>

                <!-- FORMULARIO DE GASTRONOMÍA -->
                <form action="{{ route('mesas.anadirProducto', $mesa->id) }}" method="POST" style="background: rgba(0,0,0,0.03); padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                    @csrf
                    <label style="font-size: 11px; font-weight: bold; display: block; margin-bottom: 5px; color: #555;">AÑADIR CONSUMO:</label>
                    <div style="display: flex; gap: 5px;">
                        <select name="producto_id" required style="flex-grow: 1; padding: 5px; border-radius: 5px; border: 1px solid #ccc; font-size: 12px;">
                            @foreach(\App\Models\Producto::all() as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->nombre }} - {{ $prod->precio_venta }}Bs</option>
                            @endforeach
                        </select>
                        <button type="submit" style="background: #6c757d; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 5px; font-weight: bold; font-size: 12px;">
                            +
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
                            <li style="border-bottom: 1px dashed #eee; padding: 4px 0; display: flex; justify-content: space-between;">
                                <span>{{ $pedido->producto->nombre }}</span>
                                <span style="font-weight: bold;">{{ $pedido->precio_unitario }} Bs</span>
                            </li>
                        @empty
                            <li style="color: #999; font-style: italic; font-size: 12px; padding: 5px 0;">Sin extras</li>
                        @endforelse
                    </ul>
                </div>

                <!-- BOTÓN CERRAR -->
                <a href="{{ route('mesas.cerrar', $mesa->id) }}"
                   onclick="return confirm('¿Finalizar y cobrar Mesa #{{ $mesa->numero }}?')"
                   style="background: #dc3545; color: white; padding: 12px; text-decoration: none; border-radius: 8px; display: block; text-align: center; font-weight: bold; box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);">
                   Cerrar y Cobrar Todo
                </a>
            @endif
        </div>
    @endforeach

    <div style="text-align: center; margin: 40px 0; padding-bottom: 40px;">
    <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">
    <a href="{{ route('ventas.exportar') }}" 
       style="background: #1d6f42; color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-weight: bold; font-family: Arial; font-size: 16px; box-shadow: 0 4px 10px rgba(29, 111, 66, 0.3); display: inline-flex; align-items: center; gap: 10px;">
       <span>📊</span> DESCARGAR REPORTE DE VENTAS (EXCEL/CSV)
    </a>
</div>
</div>