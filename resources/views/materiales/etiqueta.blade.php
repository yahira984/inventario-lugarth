<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta QR - {{ $material->descripcion }}</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #e5e7eb; color: #111827; }
        .wrap { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .label { width: 380px; background: #fff; border: 2px solid #111827; border-radius: 12px; padding: 18px; text-align: center; }
        .brand { font-weight: 900; font-size: 18px; margin-bottom: 8px; }
        .qr { display: inline-block; padding: 10px; border: 1px solid #d1d5db; margin: 8px 0; }
        .name { font-weight: 900; font-size: 15px; line-height: 1.25; }
        .code { font-family: Consolas, monospace; font-size: 14px; margin-top: 8px; }
        .meta { color: #4b5563; font-size: 12px; margin-top: 6px; }
        .actions { margin-top: 18px; display: flex; gap: 10px; justify-content: center; }
        .btn { border: none; border-radius: 8px; padding: 10px 14px; background: #2563eb; color: #fff; font-weight: 800; text-decoration: none; cursor: pointer; }
        @media print { body { background: #fff; } .actions { display: none; } .wrap { min-height: auto; padding: 0; } .label { border-radius: 0; } }
    </style>
</head>
<body>
<div class="wrap">
    <div>
        <div class="label">
            <div class="brand">Inventario Lugarth</div>
            <div class="qr">{!! $qrSvg !!}</div>
            <div class="name">{{ $material->descripcion }}</div>
            <div class="code">{{ $material->codigo_barras }}</div>
            <div class="meta">{{ $material->numero_parte ?? 'Sin no. parte' }} · {{ $material->marca ?? 'Sin marca' }}</div>
        </div>
        <div class="actions">
    <button class="btn btn-green" onclick="window.print()">Imprimir etiqueta</button>
    <a class="btn btn-soft" href="{{ route('materiales.index') }}">Volver</a>
        </div>
    </div>
</div>
</body>
</html>
