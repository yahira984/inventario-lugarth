<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de material - Inventario</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        body { margin:0; font-family:"Segoe UI",Tahoma,sans-serif; background:#f6f8fb; color:#102033; }
        .app-shell { display:flex; min-height:100vh; }
        .app-content { flex:1; padding:32px 18px; overflow-x:hidden; }
        .container { max-width:1180px; margin:0 auto; }
        .hero,.card { background:#fff; border:1px solid #dbe5f0; border-radius:16px; box-shadow:0 16px 40px rgba(15,23,42,.08); }
        .hero { padding:24px; margin-bottom:18px; display:flex; gap:16px; justify-content:space-between; align-items:flex-start; }
        h1,h2 { margin:0; color:#062443; }
        .muted { color:#64748b; font-size:13px; font-weight:700; line-height:1.45; }
        .btn { min-height:42px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; border:1px solid #1d4ed8; background:#2563eb; color:#fff; padding:0 14px; text-decoration:none; font-weight:800; }
        .grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:18px; }
        .card { padding:20px; }
        .row { border:1px solid #e2e8f0; border-radius:12px; padding:12px; margin-top:10px; background:#f8fafc; }
        .pill { display:inline-flex; padding:5px 9px; border-radius:999px; font-size:12px; font-weight:900; background:#dbeafe; color:#1d4ed8; }
        .pill.salida,.pill.merma { background:#fee2e2; color:#b91c1c; }
        .pill.entrada,.pill.devolucion { background:#dcfce7; color:#166534; }
        .photo { width:72px; height:72px; object-fit:cover; border-radius:12px; border:1px solid #dbe5f0; margin-top:8px; }
        .price-card { margin-bottom:18px; }
        .price-overview { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(260px,.65fr); gap:18px; margin-top:14px; }
        .price-chart { width:100%; min-height:220px; padding:12px; border:1px solid #dbe5f0; border-radius:12px; background:linear-gradient(180deg,#f8fbff,#fff); }
        .price-chart svg { width:100%; height:210px; overflow:visible; }
        .price-chart polyline { fill:none; stroke:#0b76d0; stroke-width:3; stroke-linecap:round; stroke-linejoin:round; }
        .price-chart circle { fill:#10b981; stroke:#fff; stroke-width:2; }
        .price-legend { display:flex; justify-content:space-between; gap:10px; color:#64748b; font-size:11px; font-weight:700; }
        .supplier-price { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:8px; padding:10px 0; border-bottom:1px solid #e5edf5; }
        .supplier-price:last-child { border-bottom:0; }
        .supplier-price small { display:block; color:#64748b; margin-top:3px; }
        .price-table { margin-top:18px; overflow:auto; border:1px solid #dbe5f0; border-radius:12px; }
        .price-table table { width:100%; min-width:760px; border-collapse:collapse; }
        .price-table th { padding:10px; text-align:left; background:#eff7ff; color:#315673; font-size:11px; text-transform:uppercase; }
        .price-table td { padding:10px; border-top:1px solid #e5edf5; vertical-align:top; }
        .price-up { color:#b45309; font-weight:900; }.price-down { color:#047857; font-weight:900; }
        @media(max-width:900px){ .app-content{padding-top:76px;} .hero,.grid{display:block;} .card{margin-bottom:16px;} }
        @media(max-width:900px){ .price-overview{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="container">
            <section class="hero">
                <div>
                    <h1>{{ $material->descripcion }}</h1>
                    <div class="muted">
                        {{ $material->apodo ? 'Apodo: '.$material->apodo.' - ' : '' }}
                        No. parte: {{ $material->numero_parte ?: 'N/A' }} - Stock actual: {{ $material->stock }} pzas
                    </div>
                </div>
            <a class="btn btn-soft" href="{{ route('admin.materiales.index') }}">Volver</a>
            </section>

            <section class="card price-card">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                    <div><h2>Historial de precios</h2><p class="muted">Cada entrada, orden recibida o factura conserva proveedor, fecha, importe y variación contra el registro previo.</p></div>
                    <a class="btn" href="{{ route('admin.proveedores.comparador', ['buscar' => $material->numero_parte ?: $material->descripcion]) }}">Comparar proveedores</a>
                </div>
                @php
                    $chartPrices = $seriePrecios->pluck('precio_unitario')->map(fn ($price) => (float) $price)->values();
                    $chartMin = $chartPrices->min() ?? 0;
                    $chartMax = $chartPrices->max() ?? 0;
                    $chartRange = max(0.01, $chartMax - $chartMin);
                    $chartPoints = $chartPrices->map(function (float $price, int $index) use ($chartPrices, $chartMin, $chartRange): string {
                        $x = $chartPrices->count() <= 1 ? 150 : 10 + (($index / ($chartPrices->count() - 1)) * 280);
                        $y = 188 - ((($price - $chartMin) / $chartRange) * 160);
                        return round($x, 1).','.round($y, 1);
                    })->implode(' ');
                @endphp
                <div class="price-overview">
                    <div class="price-chart">
                        @if($seriePrecios->isNotEmpty())
                            <svg viewBox="0 0 300 210" role="img" aria-label="Evolución del precio unitario">
                                <line x1="10" x2="290" y1="188" y2="188" stroke="#cbddec" stroke-width="1"/>
                                <line x1="10" x2="290" y1="28" y2="28" stroke="#e5edf5" stroke-width="1" stroke-dasharray="4 4"/>
                                <polyline points="{{ $chartPoints }}"/>
                                @foreach($chartPrices as $index => $price)
                                    @php($x = $chartPrices->count() <= 1 ? 150 : 10 + (($index / ($chartPrices->count() - 1)) * 280))
                                    @php($y = 188 - ((($price - $chartMin) / $chartRange) * 160))
                                    <circle cx="{{ $x }}" cy="{{ $y }}" r="4"><title>${{ number_format($price,2) }}</title></circle>
                                @endforeach
                            </svg>
                            <div class="price-legend"><span>{{ $seriePrecios->first()->registrado_en?->format('d/m/Y') }}</span><strong>${{ number_format($chartMin,2) }} - ${{ number_format($chartMax,2) }}</strong><span>{{ $seriePrecios->last()->registrado_en?->format('d/m/Y') }}</span></div>
                        @else
                            <div class="muted" style="padding:55px 10px;text-align:center;">Todavía no hay compras o facturas con precio para esta pieza.</div>
                        @endif
                    </div>
                    <div>
                        <strong>Último precio por proveedor</strong>
                        @forelse($resumenProveedores as $supplier)
                            <div class="supplier-price"><span><strong>{{ $supplier['proveedor'] }}</strong><small>{{ $supplier['registros'] }} registros · {{ $supplier['ultima_fecha']?->format('d/m/Y') }}</small></span><strong>${{ number_format($supplier['precio_actual'],2) }}</strong></div>
                        @empty
                            <p class="muted">Sin historial de proveedores.</p>
                        @endforelse
                    </div>
                </div>
                <div class="price-table"><table><thead><tr><th>Fecha</th><th>Proveedor</th><th>Precio</th><th>Variación</th><th>Factura / referencia</th><th>Origen</th></tr></thead><tbody>
                    @forelse($precios as $price)
                        <tr><td>{{ $price->registrado_en?->format('d/m/Y H:i') }}</td><td><strong>{{ $price->proveedor }}</strong>@if($price->proveedor_rfc)<div class="muted">{{ $price->proveedor_rfc }}</div>@endif</td><td><strong>${{ number_format((float)$price->precio_unitario,2) }} {{ $price->moneda }}</strong>@if($price->precio_anterior)<div class="muted">Anterior: ${{ number_format((float)$price->precio_anterior,2) }}</div>@endif</td><td>@if($price->variacion_porcentaje !== null)<span class="{{ $price->variacion_porcentaje >= 0 ? 'price-up' : 'price-down' }}">{{ $price->variacion_porcentaje >= 0 ? '+' : '' }}{{ number_format((float)$price->variacion_porcentaje,1) }}%</span>@else<span class="muted">Primer registro</span>@endif</td><td>{{ $price->referencia ?: 'Sin referencia' }}</td><td>{{ ucfirst($price->origen) }}</td></tr>
                    @empty<tr><td colspan="6" class="muted">Sin precios registrados todavía.</td></tr>@endforelse
                </tbody></table></div>
                {{ $precios->links() }}
            </section>

            <div class="grid">
                <section class="card">
                    <h2>Movimientos de stock</h2>
                    @forelse($movimientos as $movimiento)
                        <div class="row">
                            <span class="pill {{ $movimiento->tipo }}">{{ ucfirst($movimiento->tipo) }}</span>
                            <strong> {{ $movimiento->cantidad }} pzas</strong>
                            <div class="muted">{{ $movimiento->created_at->format('d/m/Y H:i') }} - {{ $movimiento->user?->name ?? 'Usuario no disponible' }}</div>
                            <div class="muted">Stock: {{ $movimiento->stock_anterior }} -> {{ $movimiento->stock_nuevo }}</div>
                            @if($movimiento->referencia)<div class="muted">Referencia: {{ $movimiento->referencia }}</div>@endif
                            @if($movimiento->motivo)<div class="muted">Nota: {{ $movimiento->motivo }}</div>@endif
                            @if($movimiento->evidencia_foto)<img class="photo" src="{{ asset('storage/'.$movimiento->evidencia_foto) }}" alt="Evidencia">@endif
                        </div>
                    @empty
                        <p class="muted">Este material aun no tiene movimientos.</p>
                    @endforelse
                    {{ $movimientos->links() }}
                </section>

                <section class="card">
                    <h2>Bitacora administrativa</h2>
                    @forelse($logs as $log)
                        <div class="row">
                            <span class="pill">{{ $log->accion }}</span>
                            <div class="muted">{{ $log->created_at->format('d/m/Y H:i') }} - {{ $log->user?->name ?? 'Sistema' }}</div>
                            <strong>{{ $log->modulo }}</strong>
                            <div class="muted">{{ $log->descripcion }}</div>
                        </div>
                    @empty
                        <p class="muted">No hay logs administrativos vinculados a este material.</p>
                    @endforelse
                    {{ $logs->links() }}
                </section>
            </div>
        </div>
    </main>
</div>
</body>
</html>
