<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista previa XML - Inventario</title>
    <style>
        :root {
            --bg: #edf1f5;
            --surface: #ffffff;
            --ink: #1f2933;
            --muted: #607080;
            --line: #d8e0e8;
            --blue: #2563a8;
            --blue-dark: #17426f;
            --green: #188653;
            --amber: #c77910;
            --red: #c2413a;
            --shadow: 0 18px 45px rgba(31, 41, 51, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
        }

        .container {
            width: min(1180px, 100%);
            margin: 0 auto;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .header {
            padding: 24px 28px;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
        }

        h1 {
            margin: 0;
            color: var(--blue-dark);
            font-size: 26px;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 16px;
        }

        .meta-item {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 12px;
        }

        .meta-item span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .content {
            padding: 22px 28px 28px;
        }

        .notice {
            background: #fff7e6;
            color: #8a5700;
            border: 1px solid #ffd98a;
            border-radius: 6px;
            padding: 13px 15px;
            margin-bottom: 18px;
            font-weight: 700;
            line-height: 1.45;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 980px;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #f8fafc;
            color: var(--blue-dark);
            font-size: 12px;
            text-transform: uppercase;
        }

        tr:last-child td {
            border-bottom: none;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        select {
            min-width: 220px;
            min-height: 40px;
            padding: 8px 10px;
            border: 1px solid var(--line);
            border-radius: 6px;
            font-family: inherit;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .badge.existing {
            background: #eaf8f0;
            color: #0f6b3e;
            border: 1px solid #a9dfbf;
        }

        .badge.new {
            background: #eef6ff;
            color: #17426f;
            border: 1px solid #b7d9ff;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-primary,
        .btn-secondary {
            border: none;
            border-radius: 6px;
            padding: 12px 16px;
            font-weight: 800;
            text-decoration: none;
            font-family: inherit;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--green);
            color: #fff;
        }

        .btn-secondary {
            background: #e6ecf2;
            color: var(--ink);
        }

        @media (max-width: 760px) {
            .meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="app-shell">
@include('materiales.partials.sidebar')

<main class="app-content">
<div class="container">
    <div class="header">
        <h1>Vista previa de factura XML</h1>

        <div class="meta">
            <div class="meta-item">
                <span>Factura</span>
                {{ $factura['serie'] }} {{ $factura['folio'] }}
            </div>
            <div class="meta-item">
                <span>UUID SAT</span>
                {{ $factura['uuid'] ?: 'Sin timbre detectado' }}
            </div>
            <div class="meta-item">
                <span>Proveedor</span>
                {{ $factura['emisor']['nombre'] ?: 'N/A' }}
            </div>
            <div class="meta-item">
                <span>Fecha</span>
                {{ $factura['fecha'] ?: 'N/A' }}
            </div>
            <div class="meta-item">
                <span>Total</span>
                {{ $factura['moneda'] }} ${{ number_format($factura['total'], 2) }}
            </div>
            <div class="meta-item">
                <span>Conceptos</span>
                {{ count($factura['conceptos']) }}
            </div>
        </div>
    </div>

    <div class="content">
        <div class="notice">
            Revisa antes de importar. Si el No. de Parte ya existe, se suma stock. Si no existe, se crea material nuevo sin codigo de barras.
        </div>

        <form action="{{ route('materiales.xml.store') }}" method="POST">
            @csrf
            <input type="hidden" name="payload" value="{{ $payload }}">

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Importar</th>
                            <th>Estado</th>
                            <th>Cantidad</th>
                            <th>No. Parte</th>
                            <th>Descripcion</th>
                            <th>Clave SAT</th>
                            <th>Unidad</th>
                            <th>Categoria</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($factura['conceptos'] as $index => $concepto)
                            <tr>
                                <td>
                                    <input type="checkbox" name="items[{{ $index }}][importar]" value="1" checked>
                                </td>
                                <td>
                                    @if($concepto['material_existente'])
                                        <span class="badge existing">Sumar stock</span>
                                    @else
                                        <span class="badge new">Crear nuevo</span>
                                    @endif
                                </td>
                                <td>{{ rtrim(rtrim(number_format($concepto['cantidad'], 4), '0'), '.') }}</td>
                                <td><strong>{{ $concepto['numero_parte'] ?: 'N/A' }}</strong></td>
                                <td>{{ $concepto['descripcion'] }}</td>
                                <td>{{ $concepto['clave_prod_serv'] ?: 'N/A' }}</td>
                                <td>{{ $concepto['unidad'] ?: 'N/A' }}</td>
                                <td>
                                    <select name="items[{{ $index }}][categoria]" required>
                                        <option value="IMPORTADO XML">IMPORTADO XML</option>
                                        <option value="EQUIPO ACERO AL CARBON">EQUIPO ACERO AL CARBON</option>
                                        <option value="EQUIPO ACERO INOXIDABLE">EQUIPO ACERO INOXIDABLE</option>
                                        <option value="EQUIPO TIPO ASA INOXIDABLE">EQUIPO TIPO ASA INOXIDABLE</option>
                                        <option value="EQUIPO AC SIST DSPCH MEC FILL">EQUIPO AC SIST DSPCH MEC FILL</option>
                                        <option value="EQUIPO AC SIST DSPCH MEC LIQUID">EQUIPO AC SIST DSPCH MEC LIQUID</option>
                                        <option value="EQUIPO ACERO AL CARBON UPV">EQUIPO ACERO AL CARBON UPV</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary">Confirmar importacion</button>
                <a href="{{ route('materiales.xml.create') }}" class="btn-secondary">Subir otro XML</a>
                <a href="{{ route('materiales.index') }}" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

</main>
</div>

</body>
</html>
