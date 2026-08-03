<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Comparador de proveedores - Inventario Lugarth</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f8fc;color:#0b2948;font-family:"Segoe UI",Tahoma,sans-serif}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;min-width:0;padding:30px 24px 80px}.page{width:min(1500px,100%);margin:auto}.head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;padding:20px 0;border-bottom:1px solid #d6e5f2}.head h1{margin:0;font-size:clamp(27px,3vw,38px)}.muted{color:#61778c;font-size:13px;font-weight:650}.btn{min-height:42px;border:0;border-radius:8px;padding:0 14px;text-decoration:none;font-weight:850;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}.blue{background:#0877d1;color:#fff}.soft{background:#fff;color:#075c9d;border:1px solid #b8d5ec}.filter{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:9px;margin:18px 0}.filter input{min-height:44px;border:1px solid #b8cee1;border-radius:8px;padding:0 13px;font:inherit}.groups{display:grid;gap:14px}.group{background:#fff;border:1px solid #d6e5f2;border-radius:8px;padding:16px}.group-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}.group-head h2{margin:0;font-size:18px}.saving{padding:7px 10px;border-radius:999px;background:#dcfce7;color:#08734c;font-size:12px;font-weight:850}.offers{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:9px;margin-top:12px}.offer{border:1px solid #dce8f2;border-radius:8px;padding:12px;background:#f8fbfe}.offer.best{border-color:#6ee7b7;background:#ecfdf5}.offer strong{display:block}.price{font-size:20px;margin:7px 0}.meta{font-size:12px;color:#61778c}.increase{color:#b45309;font-weight:850}.photo{width:58px;height:58px;object-fit:contain;border:1px solid #cbddec;border-radius:7px;background:#fff;float:left;margin-right:10px}.empty{padding:30px;text-align:center;background:#fff;border:1px dashed #b8cee1;border-radius:8px}.monthly{margin-top:24px;padding-top:20px;border-top:1px solid #d6e5f2}.monthly table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d6e5f2}.monthly th,.monthly td{padding:10px;border-bottom:1px solid #e2ebf3;text-align:left}.monthly th{background:#eaf4fc;color:#315673;font-size:11px;text-transform:uppercase}@media(max-width:900px){.app-content{padding:76px 14px 90px}.head{display:block}.head .btn{margin-top:12px}.filter{grid-template-columns:1fr}.filter .btn{width:100%}.group-head{display:block}.saving{display:inline-flex;margin-top:8px}.monthly{overflow:auto}.monthly table{min-width:700px}}
    </style>
</head>
<body><div class="app-shell">@include('materiales.partials.sidebar')
<main class="app-content"><div class="page">
    <header class="head"><div><h1>Comparador de precios</h1><p class="muted">Agrupa la misma pieza aunque exista en registros de proveedores diferentes y conserva su historial.</p></div><a class="btn soft" href="{{ route('admin.compras.index') }}">Volver a compras</a></header>
    <form class="filter" method="GET"><input type="search" name="buscar" value="{{ $search }}" placeholder="Pieza, apodo, no. parte o proveedor"><button class="btn blue">Comparar</button></form>
    <section class="groups">
        @forelse($groups as $group)
            @php($representante=$group['representante'])
            <article class="group">
                <div class="group-head">
                    <div>
                        @if($representante->fotografia)<img class="photo" src="{{ asset('storage/'.$representante->fotografia) }}" alt="">@endif
                        <h2>{{ $representante->descripcion }}</h2>
                        <div class="muted">{{ $representante->numero_parte ?: 'Sin no. parte' }} · {{ $group['materiales']->count() }} registros relacionados</div>
                    </div>
                    @if($group['ahorro_maximo']>0)<span class="saving">Ahorro posible ${{ number_format($group['ahorro_maximo'],2) }}</span>@endif
                </div>
                <div class="offers">
                    @foreach($group['ofertas'] as $offer)
                        <div class="offer {{ $loop->first ? 'best' : '' }}">
                            <strong>{{ $offer['proveedor'] }}</strong>
                            <div class="price">${{ number_format($offer['precio'],2) }} {{ $offer['moneda'] }}</div>
                            <div class="meta">{{ $offer['fecha'] ? \Carbon\Carbon::parse($offer['fecha'])->format('d/m/Y') : 'Sin fecha' }}</div>
                            @if($offer['aumento'])<div class="increase">Aumento {{ number_format((float)$offer['variacion'],1) }}%</div>@endif
                            @if($loop->first)<div class="meta"><strong>Mejor precio actual</strong></div>@endif
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="empty"><strong>No hay precios para comparar.</strong><br>Los precios apareceran al importar XML, aprobar entradas o recibir ordenes.</div>
        @endforelse
    </section>
    @if($monthly->isNotEmpty())
        <section class="monthly"><h2>Evolucion mensual por proveedor</h2><p class="muted">Promedio de los precios registrados en los ultimos 12 meses.</p>
            <table><thead><tr><th>Periodo</th><th>Proveedor</th><th>Precio promedio</th><th>Registros</th></tr></thead><tbody>
            @foreach($monthly as $row)<tr><td>{{ $row->periodo }}</td><td>{{ $row->proveedor }}</td><td>${{ number_format((float)$row->precio_promedio,2) }}</td><td>{{ $row->compras }}</td></tr>@endforeach
            </tbody></table>
        </section>
    @endif
</div></main></div></body></html>
