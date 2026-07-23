<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes de compra - Inventario Lugarth</title>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="workspace-page">
            <header class="workspace-page-header">
                <div><h1>Órdenes de compra</h1><p>Planea pedidos y da seguimiento a su estado. Recibir una orden no modifica stock: las existencias cambian únicamente al aprobar una entrada.</p></div>
            </header>
            @if(session('success'))<div class="alert alert-ok">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-bad"><strong>Revisa la orden:</strong> {{ $errors->first() }}</div>@endif
            <div class="workspace-grid">
                <section class="workspace-panel">
                    <div class="workspace-panel-header"><div><h2>Nueva orden</h2><p>Agrega uno o varios materiales.</p></div></div>
                    <form method="POST" action="{{ route('admin.ordenes.store') }}" id="purchaseOrderForm">
                        @csrf
                        <div class="workspace-fields">
                            <div class="workspace-field full"><label for="proveedor">Proveedor *</label><input id="proveedor" name="proveedor" list="proveedoresList" value="{{ old('proveedor') }}" required placeholder="Nombre fiscal o comercial"><datalist id="proveedoresList">@foreach($proveedores as $proveedor)<option value="{{ $proveedor }}">@endforeach</datalist></div>
                            <div class="workspace-field"><label for="referencia">Folio o referencia</label><input id="referencia" name="referencia" value="{{ old('referencia') }}" placeholder="Ej. OC-2026-001"></div>
                            <div class="workspace-field"><label for="fecha_orden">Fecha de orden *</label><input id="fecha_orden" type="date" name="fecha_orden" value="{{ old('fecha_orden', now()->toDateString()) }}" required></div>
                            <div class="workspace-field"><label for="fecha_esperada">Entrega esperada</label><input id="fecha_esperada" type="date" name="fecha_esperada" value="{{ old('fecha_esperada') }}"></div>
                            <div class="workspace-field full"><label for="notas">Notas</label><textarea id="notas" name="notas" rows="3" placeholder="Condiciones, contacto o instrucciones">{{ old('notas') }}</textarea></div>
                        </div>
                        <div class="workspace-panel-header" style="margin-top:18px"><div><h3>Materiales solicitados</h3><p>El total se calcula automáticamente.</p></div><button type="button" class="btn workspace-action-soft" id="addOrderLine">Agregar renglón</button></div>
                        <div id="orderLines"></div>
                        <template id="orderLineTemplate">
                            <div class="order-line" style="display:grid;grid-template-columns:minmax(0,1.5fr) minmax(80px,.5fr) minmax(100px,.7fr) 34px;gap:8px;margin-bottom:9px;align-items:end">
                                <div class="workspace-field"><label>Material *</label><select name="material_id[]" class="order-material"><option value="">Descripción libre</option>@foreach($materiales as $material)<option value="{{ $material->id }}" data-description="{{ $material->nombreBusqueda() }}" data-cost="{{ $material->costo_unitario }}" data-provider="{{ $material->proveedor }}">{{ $material->nombreBusqueda() }}{{ $material->numero_parte ? ' · '.$material->numero_parte : '' }}</option>@endforeach</select><input name="descripcion[]" class="order-description" required placeholder="Descripción"></div>
                                <div class="workspace-field"><label>Cantidad *</label><input type="number" name="cantidad[]" class="order-quantity" min="0.01" step="0.01" value="1" required></div>
                                <div class="workspace-field"><label>Costo unitario</label><input type="number" name="costo_unitario[]" class="order-cost" min="0" step="0.01" value="0" required></div>
                                <button type="button" class="remove-order-line workspace-action-red" aria-label="Quitar renglón" style="height:44px;border:0;border-radius:7px;color:#fff;cursor:pointer">×</button>
                            </div>
                        </template>
                        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-top:14px"><strong>Total estimado: <span id="orderTotal">$0.00</span></strong><button class="btn workspace-action-green" type="submit">Guardar orden</button></div>
                    </form>
                </section>
                <section class="workspace-panel">
                    <form class="workspace-filter-bar" method="GET" action="{{ route('admin.ordenes.index') }}">
                        <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Proveedor o referencia">
                        <select name="estado"><option value="">Todos los estados</option>@foreach(['borrador'=>'Borrador','enviada'=>'Enviada','recibida'=>'Recibida','cancelada'=>'Cancelada'] as $value=>$label)<option value="{{ $value }}" @selected($estado===$value)>{{ $label }}</option>@endforeach</select>
                        <button class="btn workspace-action-teal" type="submit">Filtrar</button>
                    </form>
                    <div style="display:grid;gap:10px">
                    @forelse($ordenes as $orden)
                        <article class="workspace-item-card">
                            <div><span class="workspace-chip {{ match($orden->estado){'recibida'=>'tone-green','cancelada'=>'tone-red','enviada'=>'tone-blue',default=>'tone-amber'} }}">{{ ucfirst($orden->estado) }}</span><h3 style="margin-top:8px">{{ $orden->referencia ?: 'Orden #'.$orden->id }}</h3><p>{{ $orden->proveedor }} · {{ $orden->fecha_orden?->format('d/m/Y') }} · {{ $orden->items->count() }} renglones</p></div>
                            <strong style="font-size:1.2rem">${{ number_format($orden->total, 2) }} MXN</strong>
                            <details><summary style="cursor:pointer;font-weight:750;color:var(--ws-blue)">Ver materiales</summary><ul style="margin:9px 0 0;padding-left:20px;color:var(--ws-muted);font-size:12px">@foreach($orden->items as $item)<li>{{ number_format($item->cantidad, 2) }} × {{ $item->descripcion }} · ${{ number_format($item->subtotal, 2) }}</li>@endforeach</ul></details>
                            <form method="POST" action="{{ route('admin.ordenes.status', $orden) }}" style="display:flex;gap:8px">@csrf @method('PATCH')<select name="estado" style="flex:1">@foreach(['borrador'=>'Borrador','enviada'=>'Enviada','recibida'=>'Recibida','cancelada'=>'Cancelada'] as $value=>$label)<option value="{{ $value }}" @selected($orden->estado===$value)>{{ $label }}</option>@endforeach</select><button class="btn workspace-action-amber" type="submit">Actualizar</button></form>
                        </article>
                    @empty
                        <div class="workspace-empty-panel"><strong>No hay órdenes registradas</strong><span>Crea la primera para organizar las próximas compras.</span></div>
                    @endforelse
                    </div>
                    <div style="margin-top:16px">{{ $ordenes->links() }}</div>
                </section>
            </div>
        </div>
    </main>
</div>
<script>
(() => {
    const lines = document.getElementById('orderLines');
    const template = document.getElementById('orderLineTemplate');
    const total = document.getElementById('orderTotal');
    const recalculate = () => {
        let sum = 0;
        lines.querySelectorAll('.order-line').forEach((line) => sum += (Number(line.querySelector('.order-quantity').value) || 0) * (Number(line.querySelector('.order-cost').value) || 0));
        total.textContent = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(sum);
    };
    const addLine = () => {
        const line = template.content.firstElementChild.cloneNode(true);
        line.querySelector('.order-material').addEventListener('change', (event) => {
            const option = event.target.selectedOptions[0];
            if (option?.value) {
                line.querySelector('.order-description').value = option.dataset.description || '';
                line.querySelector('.order-cost').value = option.dataset.cost || 0;
                const provider = document.getElementById('proveedor');
                if (!provider.value && option.dataset.provider) provider.value = option.dataset.provider;
            }
            recalculate();
        });
        line.querySelectorAll('input').forEach((input) => input.addEventListener('input', recalculate));
        line.querySelector('.remove-order-line').addEventListener('click', () => { if (lines.children.length > 1) line.remove(); recalculate(); });
        lines.append(line);
    };
    document.getElementById('addOrderLine').addEventListener('click', addLine);
    addLine();
})();
</script>
</body>
</html>
