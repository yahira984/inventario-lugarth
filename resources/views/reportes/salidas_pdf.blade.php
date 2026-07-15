<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Salidas</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; }
        .actions { margin-bottom: 16px; }
        .btn { border: none; background: #2563eb; color: #fff; border-radius: 6px; padding: 10px 14px; font-weight: 800; cursor: pointer; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #17426f; padding-bottom: 12px; margin-bottom: 18px; gap: 16px; }
        .brand { display: flex; align-items: center; gap: 14px; }
        .logo { width: 120px; height: auto; object-fit: contain; }
        h1 { margin: 0; color: #17426f; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #eef6ff; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
<div class="actions"><button class="btn" onclick="window.print()">Imprimir / Guardar como PDF</button></div>
<div class="header">
    <div class="brand">
        <img src="{{ asset('images/logo.png') }}" class="logo" alt="Lugarth">
        <div><h1>Inventario Lugarth</h1><strong>Reporte de salidas</strong></div>
    </div>
    <div>{{ now()->format('d/m/Y H:i') }}</div>
</div>
<table>
    <thead><tr><th>Fecha</th><th>Material</th><th>No. Parte</th><th>Codigo</th><th>Cantidad</th><th>Stock anterior</th><th>Stock nuevo</th><th>Referencia</th><th>Notas</th><th>Usuario</th></tr></thead>
    <tbody>
    @foreach($salidas as $salida)
        <tr>
            <td>{{ $salida->created_at?->format('d/m/Y H:i') }}</td>
            <td>{{ $salida->material?->descripcion }}</td>
            <td>{{ $salida->material?->numero_parte }}</td>
            <td>{{ $salida->codigo_barras }}</td>
            <td>{{ $salida->cantidad }}</td>
            <td>{{ $salida->stock_anterior }}</td>
            <td>{{ $salida->stock_nuevo }}</td>
            <td>{{ $salida->referencia }}</td>
            <td>{{ $salida->motivo }}</td>
            <td>{{ $salida->user?->name }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
