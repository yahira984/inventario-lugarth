<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogo completo - Inventario</title>
    <style>
        body { margin: 0; font-family: "Segoe UI", Tahoma, sans-serif; background: #f6f8fb; color: #102033; }
        .app-shell { display: flex; min-height: 100vh; }
        .app-content { flex: 1; padding: 32px 18px; overflow-x: hidden; }
        .container { max-width: 1380px; margin: 0 auto; padding: 26px; border-radius: 16px; background: #fff; border: 1px solid #dbe5f0; box-shadow: 0 16px 40px rgba(15,23,42,.08); }
        .header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-end; margin-bottom: 20px; }
        h1 { margin: 0; font-size: 30px; }
        .muted { color: #64748b; font-size: 13px; font-weight: 600; }
        form.search { display: flex; gap: 10px; flex-wrap: wrap; }
        input { min-height: 42px; min-width: 260px; border: 1px solid #cbd5e1; border-radius: 10px; padding: 0 12px; }
        .btn { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid #1d4ed8; background: #2563eb; color: #fff; padding: 0 14px; text-decoration: none; font-weight: 800; cursor: pointer; }
        .btn:hover { background: #1d4ed8; transform: translateY(-1px); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; min-width: 1640px; border-collapse: collapse; }
        th { background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; text-align: left; padding: 12px; border-bottom: 1px solid #dbe5f0; }
        td { padding: 12px; border-bottom: 1px solid #edf2f7; vertical-align: top; }
        .material-photo { width: 64px; height: 64px; object-fit: cover; border-radius: 10px; border: 1px solid #dbe5f0; background: #f8fafc; box-shadow: 0 8px 18px rgba(15,23,42,.08); }
        .no-photo { width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px dashed #cbd5e1; background: #f8fafc; color: #64748b; font-size: 10px; font-weight: 900; text-transform: uppercase; text-align: center; }
        .links { margin-top: 18px; }
        @media (max-width: 860px) { .app-content { padding-top: 76px; } .header { display: block; } form.search { margin-top: 14px; } input, .btn { width: 100%; } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="container">
            <div class="header">
                <div>
                    <h1>Catalogo completo</h1>
                    <div class="muted">Todos los datos del material: almacen, proveedor, SAT, precios, factura y stock.</div>
                </div>
                <form class="search" method="GET">
                    <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Buscar producto, proveedor, SAT, almacen">
            <button class="btn btn-blue" type="submit">Buscar</button>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th><th>Descripcion</th><th>No. parte</th><th>Codigo barras</th><th>Almacen</th><th>Categoria</th><th>Marca</th><th>Proveedor</th><th>RFC</th><th>Clave SAT</th><th>Unidad</th><th>Stock</th><th>Min</th><th>Max</th><th>Precio</th><th>Factura</th><th>XML</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materiales as $material)
                            <tr>
                                <td>
                                    @if($material->fotografia)
                                        <img src="{{ asset('storage/' . $material->fotografia) }}" class="material-photo" alt="Foto de {{ $material->descripcion }}">
                                    @else
                                        <div class="no-photo">Sin foto</div>
                                    @endif
                                </td>
                                <td><strong>{{ $material->descripcion }}</strong></td>
                                <td>{{ $material->numero_parte ?? 'N/A' }}</td>
                                <td>{{ $material->codigo_barras ?? 'Sin codigo' }}</td>
                                <td>{{ $material->almacen ?? 'Sin almacen' }}</td>
                                <td>{{ $material->categoria ?? 'Sin categoria' }}</td>
                                <td>{{ $material->marca ?? 'N/A' }}</td>
                                <td>{{ $material->proveedor ?? 'Sin proveedor' }}</td>
                                <td>{{ $material->proveedor_rfc ?? 'N/A' }}</td>
                                <td>{{ $material->clave_sat ?? 'N/A' }}</td>
                                <td>{{ $material->unidad ?? $material->clave_unidad ?? 'N/A' }}</td>
                                <td>{{ $material->stock }}</td>
                                <td>{{ $material->stock_minimo }}</td>
                                <td>{{ $material->stock_maximo }}</td>
                                <td>${{ number_format((float) $material->costo_unitario, 2) }} {{ $material->moneda ?? 'MXN' }}</td>
                                <td>{{ $material->factura_folio ?? $material->factura_uuid ?? 'N/A' }}</td>
                                <td>{{ $material->xml_importado_at?->format('d/m/Y H:i') ?? 'Manual' }}</td>
                    <td><a class="btn btn-purple" href="{{ route('admin.materiales.historial', $material) }}">Historial</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="links">{{ $materiales->links() }}</div>
        </div>
    </main>
</div>
</body>
</html>
