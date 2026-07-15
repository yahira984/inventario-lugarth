<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista previa XML - Inventario</title>

    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    
    <style>
        /* --- ESTILOS ULTRA-FUTURISTAS MASTER (Vista Previa XML) --- */
        :root {
            --bg: #030712; 
            --surface: rgba(15, 23, 42, 0.7); 
            --ink: #ffffff; 
            --muted: #94a3b8; 
            --cyan-glow: #06b6d4;
            --blue-glow: #3b82f6;
            --emerald-glow: #10b981;
            --amber-glow: #f59e0b;
            --line: rgba(56, 189, 248, 0.2);
            --shadow-glass: 0 10px 40px rgba(0, 0, 0, 0.6); 
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top left, #0a192f 0%, #030712 100%);
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .app-shell { display: flex; height: 100vh; width: 100vw; overflow: hidden; }
        .app-content { flex: 1; padding: 40px 20px; overflow-y: auto; display: flex; justify-content: center; }

        /* --- CONTENEDOR PRINCIPAL --- */
        .container {
            width: 100%;
            max-width: 1180px;
            background: var(--surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow-glass);
            padding: 40px;
        }

        .header { margin-bottom: 30px; border-bottom: 1px solid var(--line); padding-bottom: 24px; }

        h1 {
            margin: 0;
            background: linear-gradient(to right, #00f2fe, #4facfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(0, 242, 254, 0.2); 
        }

        /* --- METADATOS DE FACTURA --- */
        .meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 24px;
        }

        .meta-item {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 12px;
            padding: 16px;
            transition: all 0.3s;
            box-shadow: inset 0 0 20px rgba(6, 182, 212, 0.05);
        }

        .meta-item:hover {
            border-color: var(--cyan-glow);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.2), inset 0 0 15px rgba(6, 182, 212, 0.1);
            transform: translateY(-2px);
        }

        .meta-item span {
            display: block;
            color: var(--cyan-glow);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
            text-shadow: 0 0 10px rgba(6, 182, 212, 0.5);
        }

        /* --- ALERTA --- */
        .notice {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.1));
            border-left: 4px solid var(--amber-glow);
            color: #fcd34d;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-weight: 600;
            line-height: 1.5;
            box-shadow: inset 0 0 20px rgba(245, 158, 11, 0.05);
        }

        /* --- TABLA FUTURISTA --- */
        .table-wrap { overflow-x: auto; padding-bottom: 10px; }
        
        table {
            width: 100%;
            min-width: 980px;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        th, td {
            padding: 16px 14px;
            text-align: left;
            vertical-align: middle;
            border: none;
        }

        th {
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding-bottom: 8px;
        }

        tbody tr {
            background: rgba(30, 41, 59, 0.6);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; border-left: 2px solid transparent; }
        td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

        tbody tr:hover {
            transform: translateY(-2px);
            background: rgba(30, 41, 59, 0.9);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }
        
        tbody tr:hover td:first-child {
            border-left: 2px solid var(--cyan-glow);
            box-shadow: inset 5px 0 15px rgba(6, 182, 212, 0.1);
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--cyan-glow);
            cursor: pointer;
        }

        select {
            min-width: 200px;
            padding: 10px 12px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #ffffff;
            font-size: 13px;
            outline: none;
            transition: all 0.3s;
        }

        select:focus {
            border-color: var(--cyan-glow);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.3);
            background: rgba(0, 0, 0, 0.6);
        }

        select option { background-color: #0f172a; color: #ffffff; }

        /* --- BADGES (Etiquetas) --- */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge.existing {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            border: 1px solid rgba(16, 185, 129, 0.4);
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
        }

        .badge.new {
            background: rgba(56, 189, 248, 0.15);
            color: #7dd3fc;
            border: 1px solid rgba(56, 189, 248, 0.4);
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.2);
        }

        td strong { color: #ffffff; font-size: 14px; letter-spacing: 0.5px; }

        /* --- BOTONES DE ACCIÓN --- */
        .actions {
            display: flex;
            gap: 16px;
            margin-top: 32px;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-secondary {
            border: none;
            border-radius: 12px;
            padding: 14px 24px;
            font-weight: 800;
            text-decoration: none;
            font-family: inherit;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 13px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.6);
        }

        .btn-secondary {
            background: transparent;
            color: var(--muted);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        .btn-secondary:hover {
            border-color: #fff;
            color: #fff;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.3); border-radius: 4px;}
        ::-webkit-scrollbar-thumb { background: rgba(56, 189, 248, 0.5); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(56, 189, 248, 0.8); }

        @media (max-width: 760px) {
            .meta { grid-template-columns: 1fr; gap: 12px; }
            .actions { flex-direction: column; }
            .btn-primary, .btn-secondary { width: 100%; text-align: center; }
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
                    Revisa antes de importar. Si el No. de Parte ya existe, se suma stock. Si no existe, se crea material nuevo sin código de barras[cite: 6].
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
                                    <th>Descripción</th>
                                    <th>Clave SAT</th>
                                    <th>Unidad</th>
                                    <th>Precio unitario</th>
                                    <th>Importe</th>
                                    <th>Categoría</th>
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
                                        <td>${{ number_format((float) ($concepto['valor_unitario'] ?? 0), 2) }}</td>
                                        <td>${{ number_format((float) ($concepto['importe'] ?? 0), 2) }}</td>
                                        <td>
                                            <select name="items[{{ $index }}][categoria]" required>
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

                    <div class="actions">
                        <button type="submit" class="btn-primary">Confirmar importación</button>
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
