<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores - Inventario</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        body{margin:0;font-family:"Segoe UI",Tahoma,sans-serif;background:#f6f8fb;color:#102033}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;padding:32px 18px}.container{max-width:1120px;margin:0 auto;background:#fff;border:1px solid #dbe5f0;border-radius:16px;padding:26px;box-shadow:0 16px 40px rgba(15,23,42,.08)}h1{margin:0 0 6px}.muted{color:#64748b;font-size:13px;font-weight:600}.table-wrap{overflow-x:auto;margin-top:18px}table{width:100%;min-width:760px;border-collapse:collapse}th{background:#f8fafc;color:#475569;font-size:11px;text-transform:uppercase;text-align:left;padding:12px;border-bottom:1px solid #dbe5f0}td{padding:12px;border-bottom:1px solid #edf2f7}.btn{min-height:38px;display:inline-flex;align-items:center;border-radius:10px;background:#2563eb;color:#fff;padding:0 12px;text-decoration:none;font-weight:800}.btn:hover{background:#1d4ed8;transform:translateY(-1px)}.links{margin-top:18px}@media(max-width:860px){.app-content{padding-top:76px}}
    </style>
</head>
<body><div class="app-shell">@include('materiales.partials.sidebar')<main class="app-content"><div class="container">
    <h1>Proveedores</h1>
    <div class="muted">Resumen de proveedores y productos comprados a cada uno.</div>
    <div class="table-wrap"><table><thead><tr><th>Proveedor</th><th>RFC</th><th>Productos</th><th>Piezas</th><th>Valor inventario</th><th></th></tr></thead><tbody>
        @foreach($proveedores as $proveedor)
            <tr>
                <td><strong>{{ $proveedor->proveedor_nombre }}</strong></td>
                <td>{{ $proveedor->proveedor_rfc ?? 'N/A' }}</td>
                <td>{{ number_format($proveedor->productos) }}</td>
                <td>{{ number_format($proveedor->piezas) }}</td>
                <td>${{ number_format((float) $proveedor->valor, 2) }}</td>
                <td><a class="btn btn-blue" href="{{ route('admin.proveedores.show', urlencode($proveedor->proveedor_nombre)) }}">Ver productos</a></td>
            </tr>
        @endforeach
    </tbody></table></div>
    <div class="links">{{ $proveedores->links() }}</div>
</div></main></div></body></html>
