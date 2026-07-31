<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Inventario historico - Inventario Lugarth</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f8fc;color:#0b2948;font-family:"Segoe UI",Tahoma,sans-serif}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;min-width:0;padding:30px 24px 80px}.page{width:min(1500px,100%);margin:auto}.head{display:flex;justify-content:space-between;gap:16px;padding:20px 0;border-bottom:1px solid #d6e5f2}.head h1{margin:0;font-size:clamp(27px,3vw,38px)}.muted{color:#61778c;font-size:13px;font-weight:650}.btn{min-height:42px;border:0;border-radius:8px;padding:0 14px;text-decoration:none;font-weight:850;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}.blue{background:#0877d1;color:#fff}.soft{background:#fff;color:#075c9d;border:1px solid #b8d5ec}.filters{display:grid;grid-template-columns:180px minmax(220px,1fr) auto;gap:9px;margin:18px 0}.filters input{min-height:44px;border:1px solid #b8cee1;border-radius:8px;padding:0 12px;font:inherit}.summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:18px}.summary div{padding:16px;background:#fff;border:1px solid #d6e5f2;border-radius:8px}.summary small{display:block;color:#61778c;font-size:11px;font-weight:850;text-transform:uppercase}.summary strong{display:block;margin-top:5px;font-size:24px}.table-wrap{overflow:auto;background:#fff;border:1px solid #d6e5f2;border-radius:8px}.table{width:100%;border-collapse:collapse;min-width:920px}.table th{position:sticky;top:0;background:#eaf4fc;color:#315673;padding:10px;text-align:left;font-size:11px;text-transform:uppercase}.table td{padding:11px;border-top:1px solid #e2ebf3}.photo{width:44px;height:44px;object-fit:contain;border:1px solid #cbddec;border-radius:7px;vertical-align:middle;margin-right:8px}.pagination{margin-top:15px}.note{margin:18px 0;padding:13px;background:#eff6ff;color:#174c79;border:1px solid #bfdbfe;border-radius:8px;font-size:13px;font-weight:700}@media(max-width:900px){.app-content{padding:76px 14px 90px}.head{display:block}.head .btn{margin-top:10px}.filters,.summary{grid-template-columns:1fr}.filters .btn{width:100%}}
    </style>
    <style>
        .breakdowns{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:18px}
        .breakdown{padding:14px;background:#fff;border:1px solid #d6e5f2;border-radius:8px}
        .breakdown h2{margin:0 0 9px;font-size:15px}
        .breakdown-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;padding:7px 0;border-top:1px solid #e5edf4;font-size:12px}
        .breakdown-row:first-of-type{border-top:0}
        .breakdown-row span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        @media(max-width:900px){.breakdowns{grid-template-columns:1fr}}
    </style>
</head>
<body><div class="app-shell">@include('materiales.partials.sidebar')
<main class="app-content"><div class="page">
    <header class="head"><div><h1>Inventario historico</h1><p class="muted">Reconstruye las existencias usando cada movimiento confirmado, sin guardar copias pesadas.</p></div><a class="btn soft" href="{{ route('admin.compras.index') }}">Volver a compras</a></header>
    <form class="filters" method="GET"><input type="date" name="fecha" max="{{ today()->toDateString() }}" value="{{ $fecha->toDateString() }}"><input type="search" name="buscar" value="{{ $search }}" placeholder="Pieza, apodo, categoria o almacen"><button class="btn blue">Consultar fecha</button></form>
    <div class="summary"><div><small>Fecha consultada</small><strong>{{ $fecha->format('d/m/Y') }}</strong></div><div><small>Piezas en esa fecha</small><strong>{{ number_format($piezasHistoricas) }}</strong></div><div><small>Valor estimado</small><strong>${{ number_format($valorHistorico,2) }}</strong></div></div>
    <div class="breakdowns">
        @foreach(['almacenes'=>'Valor por almacen','categorias'=>'Valor por categoria','proveedores'=>'Valor por proveedor'] as $key=>$title)
            <section class="breakdown">
                <h2>{{ $title }}</h2>
                @forelse($breakdowns[$key]->take(5) as $item)
                    <div class="breakdown-row">
                        <span title="{{ $item['etiqueta'] }}">{{ $item['etiqueta'] }}</span>
                        <strong>${{ number_format($item['valor'],2) }}</strong>
                    </div>
                @empty
                    <div class="muted">Sin datos para esta fecha.</div>
                @endforelse
            </section>
        @endforeach
    </div>
    <div class="note">Cuando existe captura diaria se usa el stock, ubicacion y costo exactos de esa fecha. En fechas sin captura, el sistema reconstruye movimientos y toma el ultimo precio conocido hasta ese dia.</div>
    <div class="table-wrap"><table class="table"><thead><tr><th>Material</th><th>No. parte</th><th>Categoria</th><th>Almacen</th><th>Stock historico</th><th>Costo</th><th>Valor</th></tr></thead><tbody>
        @forelse($materiales as $material)<tr><td>@if($material->fotografia)<img class="photo" src="{{ asset('storage/'.$material->fotografia) }}" alt="">@endif<strong>{{ $material->descripcion }}</strong></td><td>{{ $material->numero_parte ?: 'N/A' }}</td><td>{{ $material->categoria_historica ?: 'Sin categoria' }}</td><td>{{ $material->almacen_historico ?: 'Sin almacen' }}</td><td><strong>{{ number_format($material->stock_historico) }}</strong></td><td>${{ number_format((float)$material->costo_historico,2) }}</td><td>${{ number_format((float)$material->valor_historico,2) }}<div class="muted">{{ $material->origen_historico }}</div></td></tr>
        @empty<tr><td colspan="7">No hay materiales para esta consulta.</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination">{{ $materiales->links() }}</div>
</div></main></div></body></html>
