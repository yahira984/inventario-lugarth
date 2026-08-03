<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordenes de compra - Inventario Lugarth</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f8fc;color:#0b2948;font-family:"Segoe UI",Tahoma,sans-serif}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;min-width:0;padding:30px 24px 80px}.page{width:min(1540px,100%);margin:auto}.head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 0;border-bottom:1px solid #d5e4f1}.head h1{margin:0;font-size:clamp(27px,3vw,38px)}.muted{color:#60768c;font-size:13px;font-weight:650;line-height:1.45}.btn{min-height:42px;border:0;border-radius:8px;padding:0 14px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;font-weight:850;cursor:pointer;transition:.16s}.btn:hover{filter:brightness(1.05);transform:translateY(-1px);box-shadow:0 8px 18px rgba(15,73,132,.16)}.blue{background:#0877d1;color:#fff}.green{background:#059669;color:#fff}.amber{background:#d97706;color:#fff}.red{background:#dc2626;color:#fff}.purple{background:#7c3aed;color:#fff}.soft{background:#fff;color:#075c9d;border:1px solid #b9d5ea}.alert{margin:15px 0;padding:13px 15px;border-radius:8px;font-weight:750}.alert-ok{background:#ecfdf5;color:#076b4c;border:1px solid #a7f3d0}.alert-bad{background:#fff1f2;color:#a2102d;border:1px solid #fecdd3}.layout{display:grid;grid-template-columns:minmax(330px,.72fr) minmax(0,1.28fr);gap:18px;padding-top:18px}.panel{background:#fff;border:1px solid #d5e4f1;border-radius:8px;padding:18px;align-self:start}.panel h2{margin:0}.fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:14px}.field{display:grid;gap:5px}.field.full{grid-column:1/-1}.field label{font-size:11px;font-weight:850;text-transform:uppercase;color:#315673}.field input,.field select,.field textarea,.filter input,.filter select,.stage-form input,.stage-form select,.stage-form textarea{width:100%;min-height:42px;border:1px solid #b9cee0;border-radius:7px;background:#fff;color:#0b2948;padding:8px 10px;font:inherit}.field textarea{min-height:72px}.line{display:grid;grid-template-columns:minmax(0,1fr) 88px 115px 34px;gap:7px;align-items:end;margin-top:9px;padding-top:9px;border-top:1px solid #e1ebf3}.remove{height:42px;border:0;border-radius:7px;background:#dc2626;color:#fff;font-weight:900;cursor:pointer}.total-row{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-top:14px}.filter{display:grid;grid-template-columns:minmax(0,1fr) 180px auto;gap:8px;margin-bottom:14px}.orders{display:grid;gap:12px}.order{border:1px solid #d5e4f1;border-left:4px solid #f59e0b;border-radius:8px;padding:15px;background:#fff}.order[data-state="enviada"],.order[data-state="recepcion_parcial"]{border-left-color:#0877d1}.order[data-state="recibida"],.order[data-state="facturada"]{border-left-color:#059669}.order[data-state="cancelada"]{border-left-color:#dc2626}.order-top{display:flex;justify-content:space-between;gap:12px}.order-top h3{margin:0}.price{font-size:20px;font-weight:900}.chip{display:inline-flex;margin-top:6px;padding:5px 8px;border-radius:999px;background:#e7f2ff;color:#075c9d;font-size:11px;font-weight:850}.progress{height:7px;background:#e5edf5;border-radius:99px;overflow:hidden;margin:12px 0}.progress span{display:block;height:100%;background:#059669}.items{display:grid;gap:7px}.item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;padding:9px 10px;background:#f7fafc;border:1px solid #e0e9f1;border-radius:7px}.item small{display:block;color:#60768c}.stage-form{display:grid;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid #dce7f0}.stage-actions{display:flex;flex-wrap:wrap;gap:8px}.receive-row{display:grid;grid-template-columns:minmax(0,1fr) 120px;gap:8px;align-items:center}.empty{padding:28px;text-align:center;border:1px dashed #b9cee0;border-radius:8px;color:#60768c}.pagination{margin-top:14px}
        @media(max-width:1080px){.app-content{padding:76px 14px 90px}.layout{grid-template-columns:1fr}.panel{width:100%}}@media(max-width:650px){.head,.order-top,.total-row{display:block}.head .btn,.total-row .btn{width:100%;margin-top:10px}.fields,.filter{grid-template-columns:1fr}.field.full{grid-column:auto}.line{grid-template-columns:1fr 1fr}.line .field:first-child{grid-column:1/-1}.remove{align-self:end}.stage-actions .btn{width:100%}.receive-row{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="page">
            <header class="head">
                <div><h1>Ordenes de compra</h1><p class="muted">Autoriza, envia, recibe y vincula factura sin perder la trazabilidad del stock.</p></div>
                <a class="btn soft" href="{{ route('admin.compras.index') }}">Planeacion y solicitudes</a>
            </header>
            @if(session('success'))<div class="alert alert-ok">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-bad"><strong>Revisa la orden:</strong> {{ $errors->first() }}</div>@endif

            <div class="layout" @unless($isAdmin) style="grid-template-columns:minmax(0,1fr)" @endunless>
                @if($isAdmin)
                <section class="panel">
                    <h2>Nueva orden directa</h2>
                    <p class="muted">Para compras planeadas conviene iniciar desde una solicitud autorizada. Esta opcion sirve para una compra directa.</p>
                    <form method="POST" action="{{ route('admin.ordenes.store') }}" id="purchaseOrderForm">
                        @csrf
                        <div class="fields">
                            <div class="field full"><label for="proveedor">Proveedor *</label><input id="proveedor" name="proveedor" list="providers" value="{{ old('proveedor') }}" required placeholder="Nombre fiscal o comercial"><datalist id="providers">@foreach($proveedores as $proveedor)<option value="{{ $proveedor }}">@endforeach</datalist></div>
                            <div class="field"><label for="referencia">Folio</label><input id="referencia" name="referencia" value="{{ old('referencia') }}" placeholder="OC-2026-001"></div>
                            <div class="field"><label for="fecha_orden">Fecha *</label><input id="fecha_orden" type="date" name="fecha_orden" value="{{ old('fecha_orden',today()->toDateString()) }}" required></div>
                            <div class="field"><label for="fecha_esperada">Entrega esperada</label><input id="fecha_esperada" type="date" name="fecha_esperada" value="{{ old('fecha_esperada') }}"></div>
                            <div class="field full"><label for="notas">Notas</label><textarea id="notas" name="notas">{{ old('notas') }}</textarea></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;margin-top:15px"><strong>Materiales</strong><button class="btn soft" type="button" id="addLine">Agregar renglon</button></div>
                        <div id="orderLines"></div>
                        <template id="lineTemplate">
                            <div class="line">
                                <div class="field"><label>Material *</label><select name="material_id[]" class="material"><option value="">Descripcion libre</option>@foreach($materiales as $material)<option value="{{ $material->id }}" data-description="{{ $material->nombreBusqueda() }}" data-cost="{{ $material->costo_unitario }}" data-provider="{{ $material->proveedor }}">{{ $material->nombreBusqueda() }}{{ $material->numero_parte ? ' · '.$material->numero_parte : '' }}</option>@endforeach</select><input name="descripcion[]" class="description" required placeholder="Descripcion"></div>
                                <div class="field"><label>Cantidad</label><input type="number" name="cantidad[]" class="quantity" min="0.01" step="0.01" value="1" required></div>
                                <div class="field"><label>Precio</label><input type="number" name="costo_unitario[]" class="cost" min="0" step="0.01" value="0" required></div>
                                <button class="remove" type="button" aria-label="Quitar">×</button>
                            </div>
                        </template>
                        <div class="total-row"><strong>Total: <span id="total">$0.00</span></strong><button class="btn green" type="submit">Guardar borrador</button></div>
                    </form>
                </section>
                @endif

                <section>
                    <form class="filter" method="GET">
                        <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Proveedor o referencia">
                        <select name="estado"><option value="">Todos los estados</option>@foreach(['borrador'=>'Borrador','autorizada'=>'Autorizada','enviada'=>'Enviada','recepcion_parcial'=>'Recepcion parcial','recibida'=>'Recibida','facturada'=>'Facturada','cancelada'=>'Cancelada'] as $value=>$label)<option value="{{ $value }}" @selected($estado===$value)>{{ $label }}</option>@endforeach</select>
                        <button class="btn blue">Filtrar</button>
                    </form>
                    <div class="orders">
                        @forelse($ordenes as $orden)
                            @php
                                $ordered = (float) $orden->items->sum('cantidad');
                                $received = (float) $orden->items->sum('cantidad_recibida');
                                $percent = $ordered > 0 ? min(100, round(($received / $ordered) * 100)) : 0;
                            @endphp
                            <article class="order" data-state="{{ $orden->estado }}">
                                <div class="order-top">
                                    <div><h3>{{ $orden->referencia ?: 'Orden #'.$orden->id }}</h3><div class="muted">{{ $orden->proveedor }} · {{ $orden->fecha_orden?->format('d/m/Y') }} · {{ $orden->items->count() }} renglones</div><span class="chip">{{ ucfirst(str_replace('_',' ',$orden->estado)) }}</span></div>
                                    <div class="price">${{ number_format((float)$orden->total,2) }} <small>{{ $orden->moneda }}</small></div>
                                </div>
                                <div class="progress" title="{{ $percent }}% recibido"><span style="width:{{ $percent }}%"></span></div>
                                <div class="muted">{{ number_format($received,2) }} de {{ number_format($ordered,2) }} unidades recibidas ({{ $percent }}%)</div>
                                <div class="items">
                                    @foreach($orden->items as $item)
                                        <div class="item"><span><strong>{{ $item->descripcion }}</strong><small>${{ number_format((float)$item->costo_unitario,2) }} por unidad</small></span><span>{{ number_format((float)$item->cantidad_recibida,2) }} / {{ number_format((float)$item->cantidad,2) }}</span></div>
                                    @endforeach
                                </div>

                                @if($isAdmin && $orden->estado==='borrador')
                                    <div class="stage-actions" style="margin-top:12px">
                                        <form method="POST" action="{{ route('admin.ordenes.status',$orden) }}">@csrf @method('PATCH')<input type="hidden" name="estado" value="autorizada"><button class="btn green">Autorizar orden</button></form>
                                        <form method="POST" action="{{ route('admin.ordenes.status',$orden) }}">@csrf @method('PATCH')<input type="hidden" name="estado" value="cancelada"><button class="btn red">Cancelar</button></form>
                                    </div>
                                @elseif($isAdmin && $orden->estado==='autorizada')
                                    <form class="stage-form" method="POST" action="{{ route('admin.ordenes.status',$orden) }}">@csrf @method('PATCH')<input type="hidden" name="estado" value="enviada"><button class="btn blue">Marcar como enviada al proveedor</button></form>
                                @elseif(in_array($orden->estado,['enviada','recepcion_parcial'],true))
                                    <form class="stage-form" method="POST" action="{{ route('admin.ordenes.receive',$orden) }}">
                                        @csrf
                                        <strong>Registrar recepcion</strong>
                                        @foreach($orden->items as $item)
                                            @php
                                                $pending = max(0, (float) $item->cantidad - (float) $item->cantidad_recibida);
                                            @endphp
                                            @if($pending>0)
                                                <label class="receive-row"><span>{{ $item->descripcion }} <small class="muted">Pendiente {{ number_format($pending,2) }}</small></span><input type="number" name="cantidad_recibida[{{ $item->id }}]" min="0" max="{{ $pending }}" step="1" value="{{ $pending }}"></label>
                                            @endif
                                        @endforeach
                                        <input name="referencia_recepcion" placeholder="Remision, recibo o referencia">
                                        <button class="btn green">Recibir y sumar stock</button>
                                    </form>
                                @elseif($isAdmin && $orden->estado==='recibida')
                                    <form class="stage-form" method="POST" action="{{ route('admin.ordenes.invoice',$orden) }}">
                                        @csrf @method('PATCH')
                                        <strong>Completar con factura</strong>
                                        <input name="invoice_uuid" placeholder="UUID fiscal">
                                        <input name="invoice_folio" placeholder="Folio de factura">
                                        <button class="btn purple">Vincular factura</button>
                                        <a class="btn soft" href="{{ route('materiales.xml.create',['orden'=>$orden->id]) }}">Importar XML de esta orden</a>
                                    </form>
                                @elseif($orden->estado==='facturada')
                                    <div class="alert alert-ok">Flujo completo · Factura {{ $orden->invoice_folio ?: $orden->invoice_uuid }}</div>
                                @endif
                                @if($isAdmin && in_array($orden->estado, ['borrador', 'autorizada', 'cancelada'], true) && $received <= 0 && blank($orden->invoice_uuid))
                                    <form class="stage-form" method="POST" action="{{ route('admin.ordenes.destroy', $orden) }}" onsubmit="return confirm('¿Eliminar esta orden? No se ha recibido mercancía y el stock no cambiará.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn red" type="submit">Eliminar orden</button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <div class="empty"><strong>No hay ordenes registradas.</strong><br>Crea una directa o genera una desde Planeacion de compras.</div>
                        @endforelse
                    </div>
                    <div class="pagination">{{ $ordenes->links() }}</div>
                </section>
            </div>
        </div>
    </main>
</div>
<script>
(() => {
    const lines=document.getElementById('orderLines'),template=document.getElementById('lineTemplate'),total=document.getElementById('total');
    if(!lines||!template||!total)return;
    const recalc=()=>{let sum=0;lines.querySelectorAll('.line').forEach(line=>sum+=(Number(line.querySelector('.quantity').value)||0)*(Number(line.querySelector('.cost').value)||0));total.textContent=new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(sum)};
    const add=()=>{const line=template.content.firstElementChild.cloneNode(true);const select=line.querySelector('.material');select.addEventListener('change',()=>{const option=select.selectedOptions[0];if(option?.value){line.querySelector('.description').value=option.dataset.description||'';line.querySelector('.cost').value=option.dataset.cost||0;const provider=document.getElementById('proveedor');if(!provider.value&&option.dataset.provider)provider.value=option.dataset.provider}recalc()});line.querySelectorAll('input').forEach(input=>input.addEventListener('input',recalc));line.querySelector('.remove').addEventListener('click',()=>{if(lines.children.length>1)line.remove();recalc()});lines.append(line)};
    document.getElementById('addLine').addEventListener('click',add);add();
})();
</script>
</body>
</html>
