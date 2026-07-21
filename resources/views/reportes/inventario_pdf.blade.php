<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        body { font-family: Arial, sans-serif; color: #111827; }
        .actions { margin-bottom: 16px; }
        .btn { border: none; background: #2563eb; color: #fff; border-radius: 6px; padding: 10px 14px; font-weight: 800; cursor: pointer; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #17426f; padding-bottom: 12px; margin-bottom: 18px; gap: 16px; }
        .brand { display: flex; align-items: center; gap: 14px; }
        .logo { width: 120px; height: auto; object-fit: contain; }
        h1 { margin: 0; color: #17426f; }
        table { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        th, td { border: 1px solid #d1d5db; padding: 5px; text-align: left; vertical-align: top; }
        th { background: #eef6ff; }
        .low { background: #fee2e2; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
<div class="actions"><button class="btn btn-red" onclick="window.print()">Imprimir / Guardar como PDF</button></div>
<div class="header">
    <div class="brand">
        <img src="{{ asset('images/logo.png') }}" class="logo" alt="Lugarth">
        <div><h1>Inventario Lugarth</h1><strong>Reporte general de inventario</strong></div>
    </div>
    <div>{{ now()->format('d/m/Y H:i') }}</div>
</div>
<table>
    <thead><tr><th>Categoria</th><th>Almacen</th><th>No. Parte</th><th>Codigo</th><th>Clave SAT</th><th>Proveedor</th><th>Descripcion</th><th>Stock</th><th>Min</th><th>Max</th><th>Costo</th><th>Valor</th></tr></thead>
    <tbody>
    @foreach($materiales as $material)
        <tr class="{{ $material->requiereReposicion() ? 'low' : '' }}">
            <td>{{ $material->categoria }}</td>
            <td>{{ $material->almacen }}</td>
            <td>{{ $material->numero_parte }}</td>
            <td>{{ $material->codigo_barras }}</td>
            <td>{{ $material->clave_sat }}</td>
            <td>{{ $material->proveedor }}</td>
            <td>{{ $material->descripcion }}</td>
            <td>{{ $material->stock }}</td>
            <td>{{ $material->stock_minimo }}</td>
            <td>{{ $material->stock_maximo }}</td>
            <td>${{ number_format((float) $material->costo_unitario, 2) }}</td>
            <td>${{ number_format((float) $material->stock * (float) $material->costo_unitario, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
