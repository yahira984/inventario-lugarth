<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar XML CFDI - Inventario</title>
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
            width: min(820px, 100%);
            margin: 0 auto;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .header {
            padding: 24px 30px;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
        }

        h1 {
            margin: 0;
            color: var(--blue-dark);
            font-size: 26px;
        }

        .header p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .content {
            padding: 28px 30px 30px;
        }

        .notice {
            border: 1px solid #b7d9ff;
            background: #eef6ff;
            color: #17426f;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 20px;
            font-weight: 700;
            line-height: 1.45;
        }

        .alert {
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 18px;
            font-weight: 700;
        }

        .alert.error {
            background: #fff1f0;
            border: 1px solid #f2b8b5;
            color: #842029;
        }

        label {
            display: block;
            font-weight: 800;
            margin-bottom: 8px;
        }

        input[type="file"] {
            width: 100%;
            min-height: 48px;
            padding: 11px 12px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fff;
            font-family: inherit;
            font-size: 15px;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 22px;
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

        .fields {
            margin-top: 24px;
            border-top: 1px solid var(--line);
            padding-top: 18px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
        }
    </style>
</head>
<body>

<div class="app-shell">
@include('materiales.partials.sidebar')

<main class="app-content">
<div class="container">
    <div class="header">
        <h1>Importar factura XML</h1>
        <p>Lee CFDI del SAT y carga productos al inventario sin capturar uno por uno.</p>
    </div>

    <div class="content">
        <div class="notice">
            El XML no trae codigo de barras. Se usara NoIdentificacion como No. de Parte y la descripcion del concepto como nombre del material.
        </div>

        @if(session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('materiales.xml.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <label for="xml_file">Archivo XML de factura</label>
            <input type="file" name="xml_file" id="xml_file" accept=".xml,text/xml,application/xml" required>

            <div class="actions">
                <button type="submit" class="btn-primary">Leer XML</button>
                <a href="{{ route('materiales.index') }}" class="btn-secondary">Volver al inventario</a>
            </div>
        </form>

        <div class="fields">
            Se extrae: cantidad, NoIdentificacion, descripcion, ClaveProdServ, unidad, precio, importe, proveedor, folio fiscal UUID y folio de factura.
        </div>
    </div>
</div>

</main>
</div>

</body>
</html>
