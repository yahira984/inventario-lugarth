<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #17426f; padding-bottom: 12px; margin-bottom: 18px; }
        h1 { margin: 0; color: #17426f; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #eef6ff; }
        .low { background: #fee2e2; }
        .actions { margin-bottom: 16px; }
        .btn { border: none; background: #2563eb; color: #fff; border-radius: 6px; padding: 10px 14px; font-weight: 800; cursor: pointer; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
<div class="actions"><button class="btn" onclick="window.print()">Imprimir / Guardar como PDF</button></div>
<div class="header">
    <div><h1>Inventario Lugarth</h1><strong>Reporte general de inventario</strong></div>
    <div>{{ now()->format('d/m/Y H:i') }}</div>
</div>
<table>
    <thead><tr><th>Categoría</th><th>No. Parte</th><th>Código</th><th>Descripción</th><th>Stock</th><th>Mínimo</th><th>Costo</th><th>Valor</th></tr></thead>
    <tbody>
    @foreach($materiales as $material)
        <tr class="{{ $material->requiereReposicion() ? 'low' : '' }}">
            <td>{{ $material->categoria }}</td>
            <td>{{ $material->numero_parte }}</td>
            <td>{{ $material->codigo_barras }}</td>
            <td>{{ $material->descripcion }}</td>
            <td>{{ $material->stock }}</td>
            <td>{{ $material->stock_minimo }}</td>
            <td>${{ number_format($material->costo_unitario, 2) }}</td>
            <td>${{ number_format($material->stock * $material->costo_unitario, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
