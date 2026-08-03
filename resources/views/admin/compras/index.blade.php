<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planeacion de compras - Inventario Lugarth</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f8fc;color:#0b2948;font-family:"Segoe UI",Tahoma,sans-serif}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;min-width:0;padding:30px 24px 80px}.purchase-page{width:min(1560px,100%);margin:auto}.purchase-header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding:22px 0;border-bottom:1px solid #d7e5f2}.purchase-header h1{margin:0;font-size:clamp(26px,3vw,38px);letter-spacing:0}.purchase-header p,.muted{color:#5b7188;font-size:13px;font-weight:650;line-height:1.5}.actions{display:flex;flex-wrap:wrap;gap:9px}.btn{min-height:42px;border:0;border-radius:8px;padding:0 14px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;font-weight:850;cursor:pointer;transition:transform .16s ease,filter .16s ease,box-shadow .16s ease}.btn:hover{transform:translateY(-1px);filter:brightness(1.05);box-shadow:0 8px 18px rgba(15,73,132,.18)}.blue{background:#0877d1;color:#fff}.green{background:#059669;color:#fff}.amber{background:#d97706;color:#fff}.red{background:#dc2626;color:#fff}.purple{background:#7c3aed;color:#fff}.teal{background:#0f8b8d;color:#fff}.soft{background:#fff;color:#0b5da8;border:1px solid #bad7ef}.alert{margin:16px 0;padding:13px 15px;border-radius:8px;font-weight:750}.alert-ok{background:#ecfdf5;color:#076b4c;border:1px solid #a7f3d0}.alert-bad{background:#fff1f2;color:#a2102d;border:1px solid #fecdd3}.summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:18px 0}.summary div{background:#fff;border:1px solid #d7e5f2;padding:16px;border-radius:8px}.summary small{display:block;color:#647b91;font-size:11px;font-weight:850;text-transform:uppercase}.summary strong{display:block;margin-top:6px;font-size:24px}.section{padding:22px 0;border-top:1px solid #d7e5f2}.section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-bottom:14px}.section-head h2{margin:0;font-size:21px}.section-head p{margin:4px 0 0}.table-wrap{overflow:auto;border:1px solid #d7e5f2;border-radius:8px;background:#fff;overscroll-behavior:auto}.data-table{width:100%;border-collapse:collapse;min-width:900px}.data-table th{position:sticky;top:0;z-index:1;background:#eaf4fc;color:#254c70;text-align:left;padding:11px 12px;font-size:11px;text-transform:uppercase}.data-table td{padding:12px;border-top:1px solid #e4edf5;vertical-align:middle}.material-cell{display:flex;align-items:center;gap:10px;min-width:280px}.photo{width:48px;height:48px;object-fit:contain;background:#fff;border:1px solid #cbddec;border-radius:7px}.material-cell strong{display:block}.material-cell small{display:block;color:#60778d;margin-top:3px}.chip{display:inline-flex;padding:5px 8px;border-radius:999px;font-size:11px;font-weight:850}.chip-red{background:#fee2e2;color:#a31818}.chip-amber{background:#fff3cd;color:#8b5600}.chip-green{background:#d9fbe9;color:#087149}.chip-blue{background:#dceeff;color:#075c9d}.request-list{display:grid;gap:12px}.request-tabs{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 14px}.request-tab{padding:8px 11px;border:1px solid #c5d9eb;border-radius:999px;color:#3e5e7c;background:#fff;text-decoration:none;font-size:12px;font-weight:850}.request-tab.active{color:#fff;background:#0877d1;border-color:#0877d1}.request-top-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}.request-card{background:#fff;border:1px solid #d7e5f2;border-left:4px solid #f59e0b;border-radius:8px;padding:16px}.request-card[data-state="autorizada"]{border-left-color:#16a34a}.request-card[data-state="ordenada"],.request-card[data-state="recibida"],.request-card[data-state="facturada"]{border-left-color:#0877d1}.request-card[data-state="rechazada"]{border-left-color:#dc2626}.request-top{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}.request-top h3{margin:0}.request-delete{min-height:30px;padding:0 9px;font-size:11px}.request-items{display:grid;gap:7px;margin:12px 0}.request-item{display:grid;grid-template-columns:minmax(0,1fr) 130px;gap:10px;align-items:center;padding:9px;background:#f7fafc;border:1px solid #e0eaf3;border-radius:7px}.request-item input,.request-actions input,.request-actions select,.request-actions textarea{width:100%;min-height:40px;border:1px solid #bcd1e4;border-radius:7px;background:#fff;padding:8px 10px;color:#0b2948;font:inherit}.request-actions{display:grid;gap:9px;margin-top:10px}.request-actions.two{grid-template-columns:1fr 1fr}.request-actions form{display:grid;gap:8px;align-content:start}.supplier-offers{display:flex;flex-wrap:wrap;gap:6px;margin:-1px 0 2px}.supplier-offers-title{width:100%;color:#4c657e;font-size:11px;font-weight:800}.supplier-offer{display:inline-flex;align-items:center;gap:5px;padding:5px 7px;border:1px solid #c9d9e8;border-radius:999px;background:#f8fbfd;color:#43617d;font-size:11px;font-weight:750}.supplier-offer.cheapest{border-color:#6ee7b7;background:#ecfdf5;color:#087149}.supplier-offer em{font-style:normal;font-size:10px;font-weight:900}.price-source{display:block;margin-top:3px;color:#58718a;font-size:10px;font-weight:700}.empty{padding:26px;background:#fff;border:1px dashed #bcd1e4;border-radius:8px;text-align:center;color:#61778c}.inactive-toolbar{display:flex;gap:8px}.inactive-toolbar a{padding:7px 10px;border:1px solid #bad7ef;border-radius:7px;text-decoration:none;color:#0b5da8;font-weight:800}.inactive-toolbar a.active{background:#0877d1;color:#fff}.mini-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.mini-item{background:#fff;border:1px solid #d7e5f2;border-radius:8px;padding:13px}.mini-item strong{display:block}.mini-item span{display:block;color:#61778c;font-size:12px;margin-top:4px}.pagination{margin-top:15px}
        @media(max-width:1000px){.app-content{padding:76px 14px 90px}.purchase-header,.section-head{display:block}.actions{margin-top:12px}.summary{grid-template-columns:repeat(2,minmax(0,1fr))}.mini-grid{grid-template-columns:1fr}.request-actions.two{grid-template-columns:1fr}}@media(max-width:560px){.summary{grid-template-columns:1fr}.request-top,.request-item{display:block}.request-item input{margin-top:8px}.btn{width:100%}}
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="purchase-page">
            <header class="purchase-header">
                <div>
                    <h1>Planeacion de compras</h1>
                    <p>Solicitudes, autorizaciones, faltantes, sobreinventario y consumo en un mismo flujo.</p>
                </div>
                <div class="actions">
                    @if($isAdmin)
                        <a class="btn amber" href="{{ route('admin.ordenes.index') }}">Ordenes de compra</a>
                        <a class="btn purple" href="{{ route('admin.proveedores.comparador') }}">Comparar precios</a>
                        <a class="btn teal" href="{{ route('admin.compras.historical') }}">Inventario historico</a>
                    @endif
                </div>
            </header>

            @if(session('success'))<div class="alert alert-ok">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-bad"><strong>Revisa la operacion:</strong> {{ $errors->first() }}</div>@endif

            <div class="summary">
                <div><small>Sugerencias de compra</small><strong>{{ $sugerencias->count() }}</strong></div>
                <div><small>Solicitudes visibles</small><strong>{{ number_format($solicitudes->total()) }}</strong></div>
                <div><small>Excesos de stock</small><strong>{{ $excesos->count() }}</strong></div>
                <div><small>Sin movimiento {{ $inactiveDays }} dias</small><strong>{{ $sinMovimiento->count() }}</strong></div>
            </div>

            <section class="section" id="sugerencias">
                <div class="section-head">
                    <div><h2>Sugerencias automaticas de compra</h2><p class="muted">El objetivo combina consumo de 90 dias, stock minimo, stock maximo y 30 dias de cobertura.</p></div>
                </div>
                @if($sugerencias->isNotEmpty())
                    <form id="suggestionForm" method="POST" action="{{ route('admin.compras.requests.store') }}">
                        @csrf
                        <input type="hidden" name="prioridad" value="alta">
                        <input type="hidden" name="origen" value="consumo">
                        <input type="hidden" name="motivo" value="Solicitud generada desde las sugerencias automaticas de compra">
                    </form>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead><tr><th>Elegir</th><th>Material</th><th>Stock</th><th>Consumo 90 dias</th><th>Cobertura</th><th>Objetivo</th><th>Cantidad sugerida</th><th>Motivo</th></tr></thead>
                            <tbody>
                            @foreach($sugerencias as $index => $sugerencia)
                                @php
                                    $material = $sugerencia['material'];
                                @endphp
                                <tr>
                                    <td><input form="suggestionForm" type="checkbox" name="material_id[]" value="{{ $material->id }}" aria-label="Solicitar {{ $material->descripcion }}"></td>
                                    <td>
                                        <div class="material-cell">
                                            @if($material->fotografia)<img class="photo" src="{{ asset('storage/'.$material->fotografia) }}" alt="">@endif
                                            <span><strong>{{ $material->descripcion }}</strong><small>{{ $material->numero_parte ?: 'Sin no. parte' }} · {{ $material->almacen ?: 'Sin almacen' }}</small></span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($material->stock) }}</td>
                                    <td>{{ number_format($sugerencia['consumo_periodo']) }}</td>
                                    <td>{{ $sugerencia['dias_cobertura'] === null ? 'Sin consumo' : $sugerencia['dias_cobertura'].' dias' }}</td>
                                    <td>{{ number_format($sugerencia['objetivo']) }}</td>
                                    <td><input form="suggestionForm" type="number" min="1" name="cantidad[{{ $material->id }}]" value="{{ max(1, $sugerencia['cantidad_sugerida']) }}" style="width:110px"></td>
                                    <td><span class="chip {{ $sugerencia['nivel']==='danger' ? 'chip-red' : 'chip-amber' }}">{{ $sugerencia['razon'] }}</span></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="actions" style="margin-top:12px">
                        <button class="btn green" form="suggestionForm" type="submit">Crear solicitud con seleccionados</button>
                    </div>
                @else
                    <div class="empty"><strong>No hay compras urgentes sugeridas.</strong><br>El inventario tiene cobertura suficiente con la informacion disponible.</div>
                @endif
            </section>

            <section class="section" id="solicitudes">
                <div class="section-head"><div><h2>Flujo de solicitudes</h2><p class="muted">Solicitud → autorizacion → orden → recepcion → factura.</p></div></div>
                @php
                    $requestTabs = [
                        'pendientes' => 'Por revisar',
                        'autorizadas' => 'Listas para ordenar',
                        'con_orden' => 'Con orden',
                        'rechazadas' => 'Rechazadas',
                        'todas' => 'Todas',
                    ];
                    $requestQuery = request()->except(['solicitudes', 'solicitudes_page']);
                @endphp
                <nav class="request-tabs" aria-label="Filtrar solicitudes de compra">
                    @foreach($requestTabs as $filter => $label)
                        <a class="request-tab {{ $requestFilter === $filter ? 'active' : '' }}" href="{{ route('admin.compras.index', array_merge($requestQuery, ['solicitudes' => $filter])) }}#solicitudes">
                            {{ $label }} <span>{{ $requestCounts[$filter] ?? 0 }}</span>
                        </a>
                    @endforeach
                </nav>
                <div class="request-list">
                    @forelse($solicitudes as $solicitud)
                        <article class="request-card" id="solicitud-{{ $solicitud->id }}" data-state="{{ $solicitud->estado }}">
                            <div class="request-top">
                                <div>
                                    <h3>Solicitud #{{ $solicitud->id }}</h3>
                                    <div class="muted">{{ $solicitud->requester?->name ?? 'Usuario eliminado' }} · {{ $solicitud->created_at->format('d/m/Y H:i') }} · Prioridad {{ $solicitud->prioridad }}</div>
                                </div>
                                <div class="request-top-actions">
                                    <span class="chip {{ match($solicitud->estado){'rechazada'=>'chip-red','autorizada','recibida','facturada'=>'chip-green',default=>'chip-amber'} }}">{{ ucfirst($solicitud->estado) }}</span>
                                    @if($isAdmin && ! $solicitud->order && in_array($solicitud->estado, ['solicitada', 'autorizada', 'rechazada'], true))
                                        <form method="POST" action="{{ route('admin.compras.requests.destroy', $solicitud) }}" onsubmit="return confirm('¿Eliminar esta solicitud? No se modificará el inventario.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn red request-delete" type="submit">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            @if($solicitud->motivo)<p class="muted">{{ $solicitud->motivo }}</p>@endif
                            <div class="request-items">
                                @foreach($solicitud->items as $item)
                                    <div class="request-item">
                                        <span><strong>{{ $item->material?->descripcion ?? 'Material eliminado' }}</strong><small class="muted">Stock actual {{ $item->material?->stock ?? 0 }} · {{ $item->razon }}</small></span>
                                        <span><strong>{{ number_format((float)$item->cantidad_solicitada, 2) }}</strong> solicitadas @if($item->cantidad_autorizada)<br><small>{{ number_format((float)$item->cantidad_autorizada, 2) }} autorizadas</small>@endif</span>
                                    </div>
                                @endforeach
                            </div>

                            @if($isAdmin && $solicitud->estado === 'solicitada')
                                <div class="request-actions two">
                                    <form method="POST" action="{{ route('admin.compras.requests.authorize', $solicitud) }}">
                                        @csrf @method('PATCH')
                                        @foreach($solicitud->items as $item)
                                            <label class="muted">Autorizar {{ $item->material?->descripcion }}
                                                <input type="number" name="cantidad_autorizada[{{ $item->id }}]" min="0.01" step="0.01" value="{{ $item->cantidad_solicitada }}" required>
                                            </label>
                                        @endforeach
                                        <textarea name="comentario_revision" placeholder="Comentario opcional"></textarea>
                                        <button class="btn green" type="submit">Autorizar solicitud</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.compras.requests.reject', $solicitud) }}">
                                        @csrf @method('PATCH')
                                        <textarea name="comentario_revision" required placeholder="Motivo obligatorio del rechazo"></textarea>
                                        <button class="btn red" type="submit">Rechazar solicitud</button>
                                    </form>
                                </div>
                            @elseif($isAdmin && $solicitud->estado === 'autorizada')
                                @php
                                    $offersByItem = [];
                                    $suppliersForOrder = [];
                                    foreach ($solicitud->items as $orderItem) {
                                        $offers = collect($orderItem->material?->supplierPrices ?? [])
                                            ->filter(fn ($price) => filled($price->proveedor) && (float) $price->precio_unitario > 0)
                                            ->sortByDesc(fn ($price) => $price->registrado_en?->getTimestamp() ?? 0)
                                            ->unique(fn ($price) => mb_strtolower(trim($price->proveedor)))
                                            ->sortBy('precio_unitario')
                                            ->values();
                                        if ($offers->isEmpty() && filled($orderItem->material?->proveedor) && (float) $orderItem->material?->costo_unitario > 0) {
                                            $offers = collect([(object) [
                                                'proveedor' => $orderItem->material->proveedor,
                                                'precio_unitario' => $orderItem->material->costo_unitario,
                                                'registrado_en' => $orderItem->material->updated_at,
                                            ]]);
                                        }
                                        $offersByItem[$orderItem->id] = $offers;
                                        foreach ($offers as $offer) {
                                            $key = mb_strtolower(trim($offer->proveedor));
                                            if (! isset($suppliersForOrder[$key])) {
                                                $suppliersForOrder[$key] = ['nombre' => trim($offer->proveedor), 'precios' => []];
                                            }
                                            $suppliersForOrder[$key]['precios'][$orderItem->id] = (float) $offer->precio_unitario;
                                        }
                                    }
                                @endphp
                                <form class="request-actions" method="POST" action="{{ route('admin.compras.requests.order', $solicitud) }}" data-order-form>
                                    @csrf
                                    <div class="request-actions two">
                                        <label class="muted">Proveedor
                                            <select name="proveedor" required data-order-supplier>
                                                <option value="">Elige un proveedor</option>
                                                @foreach($suppliersForOrder as $supplier)
                                                    <option value="{{ $supplier['nombre'] }}" data-price-map="{{ e(json_encode($supplier['precios'])) }}">
                                                        {{ $supplier['nombre'] }} · {{ count($supplier['precios']) }}/{{ $solicitud->items->count() }} piezas con precio · desde ${{ number_format(min($supplier['precios']), 2) }}
                                                    </option>
                                                @endforeach
                                                <option value="__otro__">Otro proveedor (capturar nombre y precio)</option>
                                            </select>
                                            <small class="price-source">Se muestran los proveedores ya usados para estas piezas.</small>
                                        </label>
                                        <label class="muted" data-other-supplier hidden>Nombre del proveedor nuevo
                                            <input name="proveedor_otro" maxlength="255" placeholder="Escribe el nombre del proveedor">
                                        </label>
                                        <label class="muted">Entrega esperada<input type="date" name="fecha_esperada" min="{{ today()->toDateString() }}"></label>
                                    </div>
                                    @foreach($solicitud->items as $item)
                                        <div class="supplier-offers">
                                            <span class="supplier-offers-title">Precios conocidos de {{ $item->material?->descripcion ?? 'esta pieza' }}</span>
                                            @forelse($offersByItem[$item->id] as $offer)
                                                <span class="supplier-offer {{ $loop->first ? 'cheapest' : '' }}">
                                                    {{ $offer->proveedor }} <strong>${{ number_format((float) $offer->precio_unitario, 2) }}</strong>
                                                    @if($loop->first)<em>Más barato</em>@endif
                                                </span>
                                            @empty
                                                <span class="supplier-offer">Aún no hay precios anteriores.</span>
                                            @endforelse
                                        </div>
                                        <label class="muted">Precio de {{ $item->material?->descripcion }}
                                            <input type="number" name="costo_unitario[{{ $item->id }}]" min="0" step="0.01" value="{{ $item->material?->costo_unitario ?? 0 }}" data-order-item-price data-reference-price="{{ $item->material?->costo_unitario ?? 0 }}">
                                            <small class="price-source" data-price-source>Precio de referencia actual.</small>
                                        </label>
                                    @endforeach
                                    <button class="btn amber" type="submit">Generar orden de compra</button>
                                </form>
                            @elseif($solicitud->order)
                                <div class="actions"><a class="btn soft" href="{{ route('admin.ordenes.index', ['buscar'=>$solicitud->order->referencia]) }}">Abrir orden {{ $solicitud->order->referencia }}</a></div>
                            @endif
                            @if($solicitud->comentario_revision)<p class="muted"><strong>Revision:</strong> {{ $solicitud->comentario_revision }}</p>@endif
                        </article>
                    @empty
                        <div class="empty">
                            @if($requestFilter === 'pendientes')
                                No hay solicitudes pendientes por revisar.
                            @elseif($requestFilter === 'autorizadas')
                                No hay solicitudes autorizadas listas para generar una orden.
                            @elseif($requestFilter === 'con_orden')
                                No hay solicitudes con una orden de compra en proceso.
                            @elseif($requestFilter === 'rechazadas')
                                No hay solicitudes rechazadas.
                            @else
                                Aún no hay solicitudes de compra.
                            @endif
                        </div>
                    @endforelse
                </div>
                <div class="pagination">{{ $solicitudes->links() }}</div>
            </section>

            @if($isAdmin && $aumentos->isNotEmpty())
                <section class="section">
                    <div class="section-head"><div><h2>Aumentos importantes de precio</h2><p class="muted">Variaciones de 15% o mas contra la compra anterior del mismo proveedor.</p></div></div>
                    <div class="mini-grid">
                        @foreach($aumentos as $aumento)
                            <div class="mini-item">
                                <strong>{{ $aumento->material?->descripcion ?? 'Material eliminado' }}</strong>
                                <span>{{ $aumento->proveedor }} · {{ number_format((float)$aumento->variacion_porcentaje,1) }}% · ${{ number_format((float)$aumento->precio_unitario,2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="section">
                <div class="section-head">
                    <div><h2>Materiales sin movimiento</h2><p class="muted">Existencias que pueden inmovilizar dinero y espacio.</p></div>
                    <div class="inactive-toolbar">
                        @foreach([30,90,180] as $days)<a class="{{ $inactiveDays===$days ? 'active' : '' }}" href="{{ route('admin.compras.index',['sin_movimiento'=>$days]).'#sin-movimiento' }}">{{ $days }} dias</a>@endforeach
                    </div>
                </div>
                <div class="mini-grid" id="sin-movimiento">
                    @forelse($sinMovimiento as $material)
                        <div class="mini-item"><strong>{{ $material->descripcion }}</strong><span>{{ $material->stock }} pzas · Ultimo movimiento {{ $material->movimientos_max_created_at ? \Carbon\Carbon::parse($material->movimientos_max_created_at)->diffForHumans() : 'nunca' }}</span></div>
                    @empty<div class="empty">No hay materiales inmovilizados en este periodo.</div>@endforelse
                </div>
            </section>

            @if($excesos->isNotEmpty())
                <section class="section" id="exceso-stock">
                    <div class="section-head"><div><h2>Exceso de stock</h2><p class="muted">Piezas por encima del maximo configurado.</p></div></div>
                    <div class="mini-grid">
                        @foreach($excesos as $material)<div class="mini-item"><strong>{{ $material->descripcion }}</strong><span>{{ $material->stock }} actuales · Maximo {{ $material->stock_maximo }} · Exceso {{ $material->stock-$material->stock_maximo }}</span></div>@endforeach
                    </div>
                </section>
            @endif
        </div>
    </main>
</div>
<script>
document.querySelectorAll('[data-order-form]').forEach((form) => {
    const supplier = form.querySelector('[data-order-supplier]');
    const prices = form.querySelectorAll('[data-order-item-price]');
    const otherSupplier = form.querySelector('[data-other-supplier]');
    const otherSupplierInput = otherSupplier?.querySelector('input');

    supplier?.addEventListener('change', () => {
        const option = supplier.options[supplier.selectedIndex];
        const isOtherSupplier = supplier.value === '__otro__';
        if (otherSupplier) otherSupplier.hidden = !isOtherSupplier;
        if (otherSupplierInput) otherSupplierInput.required = isOtherSupplier;
        let priceMap = {};
        try { priceMap = JSON.parse(option.dataset.priceMap || '{}'); } catch (_) { priceMap = {}; }

        prices.forEach((input) => {
            const knownPrice = priceMap[input.name.match(/\[(\d+)\]/)?.[1]];
            const source = input.parentElement.querySelector('[data-price-source]');
            if (knownPrice !== undefined) {
                input.value = Number(knownPrice).toFixed(2);
                if (source) source.textContent = `Último precio registrado con ${supplier.value}.`;
            } else {
                input.value = input.dataset.referencePrice || 0;
                if (source) source.textContent = 'Sin precio previo con este proveedor: se usa el precio de referencia, revísalo antes de crear la orden.';
            }
        });
    });
});
</script>
</body>
</html>
