<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Vista previa XML - Inventario</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        :root {
            --xml-ink: #10233f;
            --xml-muted: #5b7088;
            --xml-blue: #1261c9;
            --xml-blue-dark: #0b3a82;
            --xml-blue-soft: #edf5ff;
            --xml-line: #d7e4f2;
            --xml-surface: #ffffff;
            --xml-surface-soft: #f7faff;
            --xml-green: #15803d;
            --xml-amber: #b45309;
            --xml-red: #b91c1c;
        }

        * { box-sizing: border-box; }

        body.xml-preview-page {
            margin: 0;
            min-height: 100vh;
            color: var(--xml-ink);
            background: #f3f7fc;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .xml-preview-page .app-shell {
            display: flex;
            min-height: 100vh;
        }

        .xml-preview-page .app-content {
            min-width: 0;
            flex: 1;
            overflow-x: hidden;
            padding: 28px 18px 42px;
        }

        .xml-preview-page .xml-preview-container {
            width: min(1320px, 100%);
            margin: 0 auto;
            padding: 30px;
            color: var(--xml-ink);
            background: var(--xml-surface);
            border: 1px solid var(--xml-line);
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15, 35, 63, 0.09);
        }

        .xml-preview-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            padding-bottom: 22px;
            border-bottom: 1px solid var(--xml-line);
        }

        .xml-preview-page .xml-title {
            margin: 0;
            color: var(--xml-ink);
            font-size: clamp(27px, 4vw, 38px);
            font-weight: 950;
            line-height: 1.12;
        }

        .xml-subtitle {
            max-width: 720px;
            margin: 8px 0 0;
            color: var(--xml-muted);
            font-size: 14px;
            font-weight: 650;
            line-height: 1.55;
        }

        .xml-ready-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            padding: 9px 12px;
            color: #166534;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 900;
        }

        .xml-ready-badge::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #16a34a;
            box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.12);
        }

        .xml-ready-badge.duplicate {
            color: #991b1b;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .xml-ready-badge.duplicate::before {
            background: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12);
        }

        .xml-meta-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 22px;
        }

        .xml-meta-item {
            min-width: 0;
            padding: 15px 16px;
            color: var(--xml-ink);
            background: var(--xml-surface-soft);
            border: 1px solid var(--xml-line);
            border-radius: 8px;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .xml-meta-item:hover {
            transform: translateY(-2px);
            border-color: #8ec5ff;
            box-shadow: 0 10px 22px rgba(18, 97, 201, 0.10);
        }

        .xml-meta-item.wide { grid-column: span 2; }

        .xml-meta-label {
            display: block;
            margin-bottom: 6px;
            color: var(--xml-blue-dark);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .xml-meta-value {
            display: block;
            color: var(--xml-ink);
            font-size: 14px;
            font-weight: 750;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .xml-meta-item.total {
            color: #ffffff;
            background: var(--xml-blue-dark);
            border-color: var(--xml-blue-dark);
        }

        .xml-meta-item.total .xml-meta-label { color: #bfdbfe; }
        .xml-meta-item.total .xml-meta-value { color: #ffffff; font-size: 19px; font-weight: 950; }

        .xml-notice,
        .xml-error {
            margin: 20px 0;
            padding: 14px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.55;
        }

        .xml-notice {
            color: #854d0e;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
        }

        .xml-error {
            color: #991b1b;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
        }

        .xml-table-heading {
            margin: 24px 0 12px;
        }

        .xml-table-heading h2 {
            margin: 0;
            color: var(--xml-ink);
            font-size: 19px;
            font-weight: 900;
        }

        .xml-table-heading p {
            margin: 5px 0 0;
            color: var(--xml-muted);
            font-size: 13px;
            font-weight: 650;
        }

        .xml-table-wrap {
            width: 100%;
            overflow-x: auto;
            background: #ffffff;
            border: 1px solid var(--xml-line);
            border-radius: 10px;
        }

        .xml-preview-page .xml-preview-table {
            width: 100%;
            min-width: 1150px;
            margin: 0;
            background: #ffffff;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .xml-preview-table th,
        .xml-preview-table td {
            padding: 13px 12px;
            text-align: left;
            vertical-align: middle;
            border: 0;
            border-bottom: 1px solid #e7eef7;
        }

        .xml-preview-table th {
            color: #335171;
            background: #edf5ff;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .xml-preview-table td {
            color: #203750;
            background: #ffffff;
            font-size: 13px;
            line-height: 1.4;
        }

        .xml-preview-table tbody tr:last-child td { border-bottom: 0; }
        .xml-preview-table tbody tr:hover td { background: #f7fbff; }

        .xml-preview-table strong {
            color: #10233f;
            font-size: 13px;
        }

        .xml-preview-table input[type="checkbox"] {
            width: 19px;
            height: 19px;
            accent-color: var(--xml-blue);
            cursor: pointer;
        }

        .xml-preview-table select {
            width: 210px;
            min-height: 40px;
            padding: 8px 10px;
            color: var(--xml-ink);
            background: #ffffff;
            border: 1px solid #b9cde2;
            border-radius: 8px;
            font: inherit;
            font-size: 13px;
            outline: none;
        }

        .xml-preview-table select:focus {
            background: #ffffff;
            border-color: var(--xml-blue);
            box-shadow: 0 0 0 3px rgba(18, 97, 201, 0.13);
        }

        .xml-status {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 5px 9px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .xml-status.existing { color: #166534; background: #dcfce7; border: 1px solid #86efac; }
        .xml-status.new { color: #075985; background: #e0f2fe; border: 1px solid #7dd3fc; }

        .xml-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .xml-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .xml-action:hover { transform: translateY(-2px); }
        .xml-action.confirm { color: #ffffff; background: var(--xml-green); border-color: #166534; box-shadow: 0 8px 18px rgba(21, 128, 61, 0.20); }
        .xml-action.confirm:hover { background: #166534; }
        .xml-action.secondary { color: var(--xml-blue-dark); background: var(--xml-blue-soft); border-color: #b9d7f8; }
        .xml-action.secondary:hover { background: #dbeafe; }
        .xml-action.cancel { color: #475569; background: #ffffff; border-color: #cbd5e1; }
        .xml-action.cancel:hover { color: var(--xml-red); background: #fef2f2; border-color: #fecaca; }

        @media (max-width: 980px) {
            .xml-meta-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 640px) {
            .xml-preview-page .app-content { padding: 72px 8px 18px; }
            .xml-preview-page .xml-preview-container { padding: 18px 12px; border-radius: 12px; }
            .xml-preview-header { display: block; }
            .xml-ready-badge { margin-top: 14px; }
            .xml-meta-grid { grid-template-columns: 1fr; }
            .xml-meta-item.wide { grid-column: span 1; }
            .xml-actions { flex-direction: column; }
            .xml-action { width: 100%; }
        }
    </style>
</head>
<body class="xml-preview-page">
<div class="app-shell">
    @include('materiales.partials.sidebar')

    <main class="app-content">
        <div class="container xml-preview-container">
            <header class="xml-preview-header">
                <div>
                    <h1 class="xml-title">Vista previa de factura XML</h1>
                    <p class="xml-subtitle">
                        {{ $facturaYaImportada
                            ? 'Consulta nuevamente los datos fiscales y productos de esta factura.'
                            : 'Confirma los datos fiscales y selecciona la categoría de cada producto antes de sumar las existencias.' }}
                    </p>
                </div>
                <span class="xml-ready-badge {{ $facturaYaImportada ? 'duplicate' : '' }}">
                    {{ $facturaYaImportada ? 'Factura ya registrada' : 'XML leído correctamente' }}
                </span>
            </header>

            <section class="xml-meta-grid" aria-label="Resumen de la factura">
                <div class="xml-meta-item">
                    <span class="xml-meta-label">Factura</span>
                    <span class="xml-meta-value">{{ trim($factura['serie'].' '.$factura['folio']) ?: 'Sin folio' }}</span>
                </div>
                <div class="xml-meta-item wide">
                    <span class="xml-meta-label">Proveedor</span>
                    <span class="xml-meta-value">{{ $factura['emisor']['nombre'] ?: 'No especificado' }}</span>
                </div>
                <div class="xml-meta-item total">
                    <span class="xml-meta-label">Total de la factura</span>
                    <span class="xml-meta-value">${{ number_format((float) $factura['total'], 2) }} {{ $factura['moneda'] }}</span>
                </div>
                <div class="xml-meta-item wide">
                    <span class="xml-meta-label">UUID SAT</span>
                    <span class="xml-meta-value">{{ $factura['uuid'] ?: 'Sin timbre detectado' }}</span>
                </div>
                <div class="xml-meta-item">
                    <span class="xml-meta-label">RFC del proveedor</span>
                    <span class="xml-meta-value">{{ $factura['emisor']['rfc'] ?: 'No especificado' }}</span>
                </div>
                <div class="xml-meta-item">
                    <span class="xml-meta-label">Fecha</span>
                    <span class="xml-meta-value">{{ $factura['fecha'] ?: 'No especificada' }}</span>
                </div>
                <div class="xml-meta-item">
                    <span class="xml-meta-label">Subtotal</span>
                    <span class="xml-meta-value">${{ number_format((float) $factura['subtotal'], 2) }} {{ $factura['moneda'] }}</span>
                </div>
                <div class="xml-meta-item">
                    <span class="xml-meta-label">Descuento</span>
                    <span class="xml-meta-value">${{ number_format((float) ($factura['descuento'] ?? 0), 2) }} {{ $factura['moneda'] }}</span>
                </div>
                <div class="xml-meta-item">
                    <span class="xml-meta-label">Impuestos trasladados</span>
                    <span class="xml-meta-value">${{ number_format((float) ($factura['impuestos_trasladados'] ?? 0), 2) }} {{ $factura['moneda'] }}</span>
                </div>
                <div class="xml-meta-item">
                    <span class="xml-meta-label">Forma y método de pago</span>
                    <span class="xml-meta-value">Forma {{ $factura['forma_pago'] ?: 'N/A' }} · Método {{ $factura['metodo_pago'] ?: 'N/A' }}</span>
                </div>
                <div class="xml-meta-item">
                    <span class="xml-meta-label">Productos detectados</span>
                    <span class="xml-meta-value">{{ count($factura['conceptos']) }} conceptos</span>
                </div>
            </section>

            @if($facturaYaImportada)
                <div class="xml-error" role="alert">
                    Esta factura ya fue importada anteriormente. Puedes revisar toda su información, pero no volver a importarla; el stock no se modificará.
                </div>
            @elseif($errors->any())
                <div class="xml-error" role="alert">{{ $errors->first() }}</div>
            @else
                <div class="xml-notice">
                    Si el número de parte ya existe en el inventario real, el sistema sumará el stock. Si no existe, creará un material nuevo sin código de barras. La misma factura no puede importarse dos veces.
                </div>
            @endif

            <form action="{{ route('materiales.xml.store') }}" method="POST">
                @csrf
                <input type="hidden" name="payload" value="{{ $payload }}">
                <input type="hidden" name="payload_signature" value="{{ $payloadSignature }}">

                <div class="xml-table-heading">
                    <h2>Productos de la factura</h2>
                    <p>{{ $facturaYaImportada ? 'Vista de consulta. Estos productos ya fueron procesados.' : 'Desmarca cualquier renglón que no quieras agregar y confirma su categoría.' }}</p>
                </div>

                <div class="xml-table-wrap">
                    <table class="xml-preview-table">
                        <thead>
                            <tr>
                                <th>Importar</th>
                                <th>Acción</th>
                                <th>Cantidad</th>
                                <th>No. parte</th>
                                <th>Descripción</th>
                                <th>Clave SAT</th>
                                <th>Unidad</th>
                                <th>Precio unitario</th>
                                <th>Impuestos</th>
                                <th>Importe</th>
                                <th>Categoría</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($factura['conceptos'] as $index => $concepto)
                                <tr>
                                    <td><input type="checkbox" name="items[{{ $index }}][importar]" value="1" checked {{ $facturaYaImportada ? 'disabled' : '' }} aria-label="Importar {{ $concepto['descripcion'] }}"></td>
                                    <td>
                                        @if($facturaYaImportada)
                                            <span class="xml-status existing">Ya procesado</span>
                                        @elseif($concepto['material_existente'])
                                            <span class="xml-status existing">Sumar stock</span>
                                        @else
                                            <span class="xml-status new">Crear nuevo</span>
                                        @endif
                                    </td>
                                    <td>{{ rtrim(rtrim(number_format($concepto['cantidad'], 4), '0'), '.') }}</td>
                                    <td><strong>{{ $concepto['numero_parte'] ?: 'N/A' }}</strong></td>
                                    <td>{{ $concepto['descripcion'] }}</td>
                                    <td>{{ $concepto['clave_prod_serv'] ?: 'N/A' }}</td>
                                    <td>{{ $concepto['unidad'] ?: 'N/A' }}</td>
                                    <td>${{ number_format((float) ($concepto['valor_unitario'] ?? 0), 2) }}</td>
                                    <td>${{ number_format((float) ($concepto['impuestos_trasladados'] ?? 0), 2) }}</td>
                                    <td>${{ number_format((float) ($concepto['importe'] ?? 0), 2) }}</td>
                                    <td>
                                        <select name="items[{{ $index }}][categoria]" required {{ $facturaYaImportada ? 'disabled' : '' }} aria-label="Categoría de {{ $concepto['descripcion'] }}">
                                            @foreach($categorias as $categoria)
                                                <option value="{{ $categoria }}" {{ $categoria === 'IMPORTADO XML' ? 'selected' : '' }}>{{ $categoria }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="xml-actions">
                    <a href="{{ route('materiales.index') }}" class="xml-action cancel">Cancelar</a>
                    <a href="{{ route('materiales.xml.create') }}" class="xml-action secondary">Elegir otro XML</a>
                    @unless($facturaYaImportada)
                        <button type="submit" class="xml-action confirm">Confirmar importación</button>
                    @endunless
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
