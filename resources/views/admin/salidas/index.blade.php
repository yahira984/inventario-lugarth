<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salidas de almacen - Inventario</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        body{margin:0;font-family:"Segoe UI",Tahoma,sans-serif;background:#f6f8fb;color:#102033}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;padding:32px 18px}.container{max-width:1280px;margin:0 auto;background:#fff;border:1px solid #dbe5f0;border-radius:16px;padding:26px;box-shadow:0 16px 40px rgba(15,23,42,.08)}h1{margin:0 0 6px}.muted{color:#64748b;font-size:13px;font-weight:600}.table-wrap{overflow-x:auto;margin-top:18px}table{width:100%;min-width:1050px;border-collapse:collapse}th{background:#f8fafc;color:#475569;font-size:11px;text-transform:uppercase;text-align:left;padding:12px;border-bottom:1px solid #dbe5f0}td{padding:12px;border-bottom:1px solid #edf2f7}.qty{font-weight:900;color:#dc2626}.links{margin-top:18px}@media(max-width:860px){.app-content{padding-top:76px}}
    </style>
</head>
<body><div class="app-shell">@include('materiales.partials.sidebar')<main class="app-content"><div class="container">
    <h1>Salidas de almacen</h1>
    <div class="muted">Historial completo: fecha, producto, cantidad, usuario y notas.</div>
    <div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Producto</th><th>No. parte</th><th>Codigo</th><th>Cantidad</th><th>Stock anterior</th><th>Stock nuevo</th><th>Usuario</th><th>Referencia</th><th>Notas</th></tr></thead><tbody>
        @foreach($salidas as $salida)
            <tr>
                <td>{{ $salida->created_at?->format('d/m/Y H:i') }}</td>
                <td><strong>{{ $salida->material?->descripcion ?? 'Material eliminado' }}</strong></td>
                <td>{{ $salida->material?->numero_parte ?? 'N/A' }}</td>
                <td>{{ $salida->codigo_barras ?? 'N/A' }}</td>
                <td class="qty">-{{ $salida->cantidad }}</td>
                <td>{{ $salida->stock_anterior }}</td>
                <td>{{ $salida->stock_nuevo }}</td>
                <td>{{ $salida->user?->name ?? 'Usuario no disponible' }}</td>
                <td>{{ $salida->referencia ?? 'N/A' }}</td>
                <td>{{ $salida->motivo ?? 'Sin notas' }}</td>
            </tr>
        @endforeach
    </tbody></table></div>
    <div class="links">{{ $salidas->links() }}</div>
</div></main></div></body></html>
