<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar XML CFDI - Inventario</title>

    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    
    <style>
        /* --- ESTILOS ULTRA-FUTURISTAS MASTER --- */
        :root {
            --bg: #030712; 
            --surface: rgba(15, 23, 42, 0.6); 
            --ink: #320b79; 
            --muted: #062858; 
            --cyan-glow: #140b9c;
            --blue-glow: #0c46a3;
            --emerald-glow: #331085;
            --orange-glow: #f97316;
            --red-glow: #ef4444;
            --line: rgba(49, 18, 134, 0.2);
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

        .app-shell {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        .app-content {
            flex: 1;
            padding: 40px 20px;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        /* --- CONTENEDOR DEL FORMULARIO --- */
        .container {
            width: 100%;
            max-width: 900px;
            background: var(--surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow-glass), inset 0 0 20px rgba(14, 9, 88, 0.05);
            padding: 40px;
        }

        .form-header {
            margin-bottom: 30px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 20px;
        }

        .form-header h2 {
            margin: 0 0 8px 0;
            background: linear-gradient(to right, #1d0d58, #0e1358);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(25, 13, 138, 0.2); 
        }

        .header-meta {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        /* --- ALERTAS --- */
        .alert-info {
            background: linear-gradient(135deg, rgba(3, 5, 90, 0.15), rgba(28, 12, 97, 0.1));
            border-left: 4px solid var(--cyan-glow);
            color: #25115c;
            padding: 20px 24px;
            border-radius: 12px;
            font-weight: 600;
            margin-bottom: 30px;
            box-shadow: inset 0 0 20px rgba(6, 182, 212, 0.05);
            font-size: 15px;
            line-height: 1.6;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--red-glow);
            color: #0d0e69;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            box-shadow: inset 0 0 15px rgba(239, 68, 68, 0.1);
        }

        /* --- ESTILOS DE INPUTS --- */
        .form-group { display: flex; flex-direction: column; margin-bottom: 24px; }

        label {
            color: #2b1569; 
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        input[type="file"] {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px dashed rgba(33, 17, 107, 0.3);
            border-radius: 12px;
            font-size: 14px;
            color: #1c0c61;
            font-family: inherit;
            transition: all 0.3s ease;
            outline: none;
            cursor: pointer;
        }

        input[type="file"]:hover, input[type="file"]:focus {
            border-color: var(--cyan-glow);
            box-shadow: 0 0 15px rgba(25, 15, 82, 0.3);
            background: rgba(0, 0, 0, 0.6);
        }
        
        /* Estilizar el botón interno del input file */
        input[type="file"]::file-selector-button {
            background: rgba(14, 15, 104, 0.1);
            border: 1px solid rgba(15, 16, 87, 0.4);
            color: #090f61;
            padding: 8px 16px;
            border-radius: 6px;
            margin-right: 16px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s;
        }
        input[type="file"]::file-selector-button:hover {
            background: var(--cyan-glow);
            color: #000;
        }

        /* --- BOTONES --- */
        .form-actions {
            display: flex;
            gap: 16px;
            margin-top: 32px;
            align-items: center;
        }

        .btn-submit {
            background: linear-gradient(135deg, #1c0974 0%, #1c0d61 100%);
            color: #ffff;
            border: none;
            border-radius: 12px;
            padding: 16px 32px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(39, 16, 104, 0.4);
            flex: 1;
            text-align: center;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(23, 12, 116, 0.6);
            filter: brightness(1.1);
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: transparent;
            color: var(--muted);
            border: 2px solid rgba(17, 27, 163, 0.1);
            border-radius: 12px;
            padding: 14px 24px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-cancel:hover {
            border-color: #2220be;
            color: #08075a;
        }

        .help-text {
            margin-top: 30px;
            font-size: 13px;
            color: #271163;
            text-align: center;
            line-height: 1.5;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.3); }
        ::-webkit-scrollbar-thumb { background: rgba(15, 14, 119, 0.5); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(9, 16, 110, 0.8); }

        @media (max-width: 768px) {
            .form-actions { flex-direction: column; }
            .btn-submit, .btn-cancel { width: 100%; }
            .app-content { padding: 20px 10px; }
            .container { padding: 24px; }
        }
    </style>
</head>
<body>

<div class="app-shell">
@include('materiales.partials.sidebar')

<main class="app-content">
<div class="container">
    <div class="form-header">
        <h2>Importar factura XML</h2>
        <p class="header-meta">Lee CFDI del SAT y carga productos al inventario sin capturar uno por uno.</p>
    </div>

    <!-- Alerta Informativa Futurista -->
    <div class="alert-info">
        El XML no trae código de barras. Se usará NoIdentificacion como No. de Parte y la descripción del concepto como nombre del material.
    </div>

    @if(session('error'))
        <div class="alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('materiales.xml.preview') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="xml_file">Archivo XML de factura</label>
            <input type="file" name="xml_file" id="xml_file" accept=".xml,text/xml,application/xml" required>
            <div class="help-text" style="margin-top: 10px; text-align: left;">
                Sube el archivo .xml descargado del SAT. No subas el PDF.
            </div>
        </div>

        <div class="form-actions">
            <!-- Botón unificado a la paleta -->
            <button type="submit" class="btn-submit">Leer XML</button>
            <a href="{{ route('materiales.index') }}" class="btn-cancel">Volver al inventario</a>
        </div>
    </form>

    <div class="help-text">
        Se extrae: cantidad, NoIdentificacion, descripción, ClaveProdServ, unidad, precio, importe, proveedor, folio fiscal UUID y folio de factura.
    </div>
</div>
</main>
</div>

</body>
</html>
