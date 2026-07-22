<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiquetas seleccionadas - Inventario Lugarth</title>
    <style>
        *{box-sizing:border-box}body{margin:0;padding:24px;font-family:Arial,sans-serif;background:#eef2f6;color:#10243a}.toolbar{display:flex;justify-content:center;gap:10px;margin-bottom:20px}.toolbar button,.toolbar a{padding:10px 15px;color:#fff;background:#1769d2;border:0;border-radius:7px;font-weight:700;text-decoration:none;cursor:pointer}.labels{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px;max-width:1200px;margin:auto}.label{break-inside:avoid;display:grid;grid-template-columns:150px minmax(0,1fr);gap:14px;align-items:center;padding:14px;background:#fff;border:2px solid #122d47;border-radius:8px}.qr svg{display:block;width:100%;height:auto}.brand{font-size:11px;font-weight:900;text-transform:uppercase;color:#1769d2}.name{margin-top:7px;font-size:15px;font-weight:900;line-height:1.25}.code{margin-top:8px;font:700 12px Consolas,monospace;overflow-wrap:anywhere}.meta{margin-top:6px;color:#526a80;font-size:11px;line-height:1.4}@media print{body{padding:0;background:#fff}.toolbar{display:none}.labels{grid-template-columns:repeat(2,1fr);gap:4mm}.label{border-radius:0;page-break-inside:avoid}}@media(max-width:640px){body{padding:12px}.labels{grid-template-columns:1fr}.label{grid-template-columns:110px minmax(0,1fr)}}
    </style>
</head>
<body>
<div class="toolbar"><button type="button" onclick="window.print()">Imprimir etiquetas</button><a href="{{ route('materiales.index') }}">Volver al inventario</a></div>
<main class="labels">
    @foreach($materiales as $item)
        @php($material = $item['material'])
        <article class="label">
            <div class="qr">{!! $item['qrSvg'] !!}</div>
            <div><div class="brand">Inventario Lugarth</div><div class="name">{{ $material->descripcion }}</div><div class="code">{{ $material->codigo_barras ?: 'LUG-'.str_pad((string)$material->id, 6, '0', STR_PAD_LEFT) }}</div><div class="meta">{{ $material->numero_parte ?: 'Sin no. parte' }} · {{ $material->almacen ?: 'Sin almacén' }} · {{ $material->apodo ?: 'Sin apodo' }}</div></div>
        </article>
    @endforeach
</main>
</body>
</html>
