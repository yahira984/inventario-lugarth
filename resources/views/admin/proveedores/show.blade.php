<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos del proveedor - Inventario</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        body{margin:0;font-family:"Segoe UI",Tahoma,sans-serif;background:#f6f8fb;color:#102033}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;padding:32px 18px}.container{max-width:1200px;margin:0 auto;background:#fff;border:1px solid #dbe5f0;border-radius:16px;padding:26px;box-shadow:0 16px 40px rgba(15,23,42,.08)}h1{margin:0 0 6px}.muted{color:#64748b;font-size:13px;font-weight:600}.table-wrap{overflow-x:auto;margin-top:18px}table{width:100%;min-width:980px;border-collapse:collapse}th{background:#f8fafc;color:#475569;font-size:11px;text-transform:uppercase;text-align:left;padding:12px;border-bottom:1px solid #dbe5f0}td{padding:12px;border-bottom:1px solid #edf2f7}.btn{min-height:38px;display:inline-flex;align-items:center;border-radius:10px;background:#fff;color:#1d4ed8;border:1px solid #bfdbfe;padding:0 12px;text-decoration:none;font-weight:800}.links{margin-top:18px}@media(max-width:860px){.app-content{padding-top:76px}}
    </style>
</head>
<body><div class="app-shell">@include('materiales.partials.sidebar')<main class="app-content"><div class="container">
    <a class="btn btn-soft" href="{{ route('admin.proveedores.index') }}">Volver</a>
    <h1>{{ $proveedor }}</h1>
    <div class="muted">Productos comprados a este proveedor.</div>
    <div class="table-wrap"><table><thead><tr><th>Descripcion</th><th>No. parte</th><th>Almacen</th><th>Stock</th><th>Precio</th><th>Clave SAT</th><th>Factura</th></tr></thead><tbody>
        @foreach($materiales as $material)
            <tr>
                <td><strong>{{ $material->descripcion }}</strong></td>
                <td>{{ $material->numero_parte ?? 'N/A' }}</td>
                <td>{{ $material->almacen ?? 'Sin almacen' }}</td>
                <td>{{ $material->stock }}</td>
                <td>${{ number_format((float) $material->costo_unitario, 2) }}</td>
                <td>{{ $material->clave_sat ?? 'N/A' }}</td>
                <td>{{ $material->factura_folio ?? $material->factura_uuid ?? 'N/A' }}</td>
            </tr>
        @endforeach
    </tbody></table></div>
    <div class="links">{{ $materiales->links() }}</div>
</div></main></div></body></html>
