<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Inventario - Almacen</title>

    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
        
    <style>
        /* --- ESTILOS ULTRA-FUTURISTAS & CREATIVOS --- */
        :root {
            --bg: #030712; /* Fondo casi negro profundo */
            --surface: rgba(17, 24, 39, 0.7); 
            --ink: #ffffff; 
            --muted: #94a3b8; 
            
            /* Paleta Neón Vibrante */
            --cyan-glow: #06b6d4;
            --blue-glow: #3b82f6;
            --emerald-glow: #10b981;
            --pink-glow: #ec4899;
            --orange-glow: #f97316;
            
            --shadow-glass: 0 8px 32px 0 rgba(0, 0, 0, 0.5); 
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at 10% 20%, #081121 0%, #030712 100%);
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
        }

        .container {
            width: min(1220px, 100%);
            margin: 0 auto;
            background: var(--surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            box-shadow: var(--shadow-glass);
            overflow: hidden;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            padding: 30px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            flex-wrap: wrap;
        }

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

        .header-meta {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        /* Botones Principales con Degradados Premium */
        .btn-alta, .btn-xml, .btn-report, .btn-filter, .btn-scan, .close-btn {
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 800;
            font-family: inherit;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 13px;
        }

        .btn-alta {
            background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
            padding: 14px 24px;
            box-shadow: 0 4px 15px rgba(0, 114, 255, 0.4);
        }

        .btn-xml {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 14px 24px;
            box-shadow: 0 4px 15px rgba(56, 239, 125, 0.4);
        }

        .btn-report {
            background: linear-gradient(135deg, #334155 0%, #0f172a 100%);
            padding: 14px 18px;
            border: 1px solid rgba(125, 211, 252, 0.2);
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.45);
        }
        .btn-dashboard { background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 100%); }
        .btn-excel { background: linear-gradient(135deg, #10b981 0%, #047857 100%); }
        .btn-pdf { background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%); }

        .btn-alta:hover, .btn-xml:hover, .btn-report:hover {
            transform: translateY(-3px) scale(1.02);
            filter: brightness(1.1);
        }
        
        .btn-alta:hover { box-shadow: 0 8px 25px rgba(0, 114, 255, 0.6); }
        .btn-xml:hover { box-shadow: 0 8px 25px rgba(56, 239, 125, 0.6); }

        .header-actions { display: flex; gap: 15px; flex-wrap: wrap; }
        .legacy-logout { display: none; }
        .toolbar { padding: 24px 30px 0; }

        .filter-form {
            display: grid;
            grid-template-columns: auto minmax(220px, 1fr) minmax(210px, 280px) auto auto;
            gap: 12px;
            align-items: stretch;
        }

        /* Inputs de Búsqueda de Alta Gama */
        .filter-form select,
        .filter-form input[type="text"] {
            min-height: 48px;
            padding: 10px 16px;
            background: rgba(15, 23, 42, 0.8);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            color: var(--ink);
            font-family: inherit;
            transition: all 0.3s;
        }

        .filter-form input[type="text"]::placeholder { color: #64748b; }

        .filter-form select:focus,
        .filter-form input[type="text"]:focus {
            border-color: var(--cyan-glow);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.3);
            background: rgba(0, 0, 0, 0.6);
        }
        
        .filter-form select option { background-color: #0f172a; color: var(--ink); }

        .btn-scan {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            padding: 0 20px;
            box-shadow: 0 4px 15px rgba(253, 160, 133, 0.4);
            color: #000;
        }

        .btn-scan:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(253, 160, 133, 0.6);
        }

        .btn-filter {
            background: linear-gradient(135deg, #0cebeb 0%, #20e3b2 50%, #29ffc6 100%);
            padding: 0 24px;
            color: #000;
            box-shadow: 0 4px 15px rgba(32, 227, 178, 0.4);
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(32, 227, 178, 0.6);
        }

        .btn-clear {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: transparent;
            color: var(--muted);
            padding: 0 16px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-clear:hover {
            border-color: #fff;
            color: #fff;
        }

        .alert-success {
            margin: 24px 30px 0;
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: #34d399;
            padding: 16px 20px;
            border-radius: 12px;
            border-left: 4px solid #10b981;
            font-weight: 700;
        }

        /* --- LA NUEVA TABLA FLOTANTE (ESTILO CREATIVO) --- */
        .table-wrap {
            padding: 30px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: separate;
            border-spacing: 0 16px; /* Separación mágica entre filas */
            background: transparent; 
            border: none;
            box-shadow: none;
        }

        th, td {
            padding: 20px 16px;
            text-align: left;
            vertical-align: middle;
            border: none; /* Adiós bordes feos */
        }

        th {
            background-color: transparent;
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: none;
            padding-bottom: 0;
        }

        /* Filas convertidas en Tarjetas Flotantes */
        tbody tr {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.8));
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        /* Bordes redondeados para simular tarjetas independientes */
        td:first-child { border-top-left-radius: 16px; border-bottom-left-radius: 16px; border-left: 2px solid transparent; }
        td:last-child { border-top-right-radius: 16px; border-bottom-right-radius: 16px; border-right: 2px solid transparent; }

        /* Efecto Escáner y Elevación al Hover */
        tbody tr:hover {
            transform: translateY(-4px) scale(1.01);
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 1));
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }
        
        tbody tr:hover td:first-child {
            border-left: 2px solid var(--cyan-glow);
            box-shadow: inset 5px 0 15px rgba(6, 182, 212, 0.2);
        }

        .img-material {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            transition: transform 0.3s;
        }

        tbody tr:hover .img-material { transform: scale(1.1) rotate(2deg); border-color: var(--cyan-glow); }

        .no-photo {
            width: 72px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            color: var(--cyan-glow);
            background-color: rgba(6, 182, 212, 0.05);
            border-radius: 12px;
            border: 2px dashed rgba(6, 182, 212, 0.4);
        }

        /* --- ETIQUETAS DE COLOR SÚPER CREATIVAS --- */
        .badge {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 12px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
        }

        .badge-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.4));
            color: #6ee7b7;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.4));
            color: #fca5a5;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .badge-category {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.4));
            color: #fcd34d;
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.2);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-warning {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.24), rgba(220, 38, 38, 0.4));
            color: #fed7aa;
            box-shadow: 0 0 18px rgba(249, 115, 22, 0.26);
            border: 1px solid rgba(249, 115, 22, 0.42);
        }

        tr.stock-critical {
            background: linear-gradient(90deg, rgba(127, 29, 29, 0.34), rgba(15, 23, 42, 0.72));
        }

        tr.stock-critical td:first-child {
            border-left: 3px solid #ef4444;
            box-shadow: inset 7px 0 16px rgba(239, 68, 68, 0.24);
        }

        .code-muted { display: block; color: var(--cyan-glow); font-size: 12px; margin-top: 6px; font-weight: 600;}
        .stock-meta { display: block; margin-top: 7px; color: var(--muted); font-size: 11px; font-weight: 700; line-height: 1.45; }
        td strong { font-size: 16px; letter-spacing: 0.5px; }

        /* --- BOTONES DE ACCIÓN FLOTANTES --- */
        .action-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-edit, .btn-code, .btn-delete, .btn-label, .btn-barcode {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
        }

        /* Diseño Rosa/Morado Neón para Editar */
        .btn-edit {
            background: rgba(236, 72, 153, 0.1);
            color: #f472b6;
            border: 1px solid rgba(236, 72, 153, 0.3);
        }
        .btn-edit:hover {
            background: var(--pink-glow);
            color: #fff;
            box-shadow: 0 0 20px rgba(236, 72, 153, 0.6);
            transform: translateY(-2px);
        }

        /* Diseño Naranja para Código */
        .btn-code {
            background: rgba(249, 115, 22, 0.1);
            color: #fdba74;
            border: 1px solid rgba(249, 115, 22, 0.3);
        }
        .btn-code:hover {
            background: var(--orange-glow);
            color: #fff;
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.6);
            transform: translateY(-2px);
        }

        .btn-barcode {
            background: rgba(37, 99, 235, 0.12);
            color: #bfdbfe;
            border: 1px solid rgba(37, 99, 235, 0.35);
        }

        .btn-barcode:hover {
            background: #2563eb;
            color: #fff;
            box-shadow: 0 0 20px rgba(37, 99, 235, 0.45);
            transform: translateY(-2px);
        }

        .btn-label {
            background: rgba(14, 165, 233, 0.12);
            color: #7dd3fc;
            border: 1px solid rgba(14, 165, 233, 0.35);
        }
        .btn-label:hover {
            background: #0ea5e9;
            color: #fff;
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.48);
            transform: translateY(-2px);
        }

        .action-buttons form { margin: 0; }

        /* Diseño Rojo Intenso para Eliminar */
        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .btn-delete:hover {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.6);
            transform: translateY(-2px);
        }

        /* Resto de modales (ocultos por defecto) adaptados a la paleta */
        .modal { display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.85); backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 1000; padding: 20px; }
        .modal-content, .modal-confirm-content { background: #0f172a; border: 1px solid rgba(6, 182, 212, 0.3); padding: 30px; border-radius: 20px; width: min(450px, 100%); box-shadow: 0 24px 60px rgba(0, 0, 0, 0.8), inset 0 0 30px rgba(6, 182, 212, 0.05); }
        .modal-content h3, .modal-confirm-content h3 { margin: 0 0 14px; color: #fff; font-size: 22px; text-align: center; }
        .close-btn { background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 14px; width: 100%; margin-top: 20px; }
        .close-btn:hover { background: #ef4444; color: #fff; box-shadow: 0 0 20px rgba(239, 68, 68, 0.5); }
        #reader { width: 100%; min-height: 250px; border-radius: 12px; overflow: hidden; border: 2px dashed rgba(6, 182, 212, 0.5); }
        #reader button,
        #reader a {
            background: rgba(6, 182, 212, 0.14) !important;
            border: 1px solid rgba(6, 182, 212, 0.45) !important;
            color: #bae6fd !important;
            border-radius: 8px !important;
            padding: 9px 12px !important;
            font-family: inherit !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            text-decoration: none !important;
            cursor: pointer !important;
        }
        #reader button:hover,
        #reader a:hover { background: rgba(6, 182, 212, 0.28) !important; color: #fff !important; }
        .modal-confirm-icon { width: 60px; height: 60px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); border: 2px solid rgba(239, 68, 68, 0.5); color: #f87171; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 28px; box-shadow: 0 0 20px rgba(239, 68, 68, 0.3); }
        .modal-confirm-content p { margin: 0 0 24px; color: var(--muted); font-size: 15px; text-align: center; line-height: 1.6; }
        .modal-confirm-content p strong { color: #fff; font-size: 16px;}
        .modal-confirm-actions { display: flex; gap: 12px; }
        .barcode-form { display: grid; gap: 14px; }
        .barcode-form label { color: #e5edf8; font-weight: 800; font-size: 13px; }
        .barcode-form input { width: 100%; min-height: 48px; border-radius: 12px; border: 1px solid rgba(148, 163, 184, .35); background: rgba(15, 23, 42, .85); color: #fff; padding: 0 14px; font-size: 16px; box-sizing: border-box; }
        .barcode-form input:focus { outline: none; border-color: #38bdf8; box-shadow: 0 0 0 4px rgba(56, 189, 248, .16); }
        .barcode-help { margin: 0; color: var(--muted); font-size: 13px; line-height: 1.5; text-align: left; }
        .barcode-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn-save-code { min-height: 44px; border: 0; border-radius: 12px; background: #2563eb; color: #fff; font-weight: 900; cursor: pointer; }
        .btn-save-code:hover { background: #1d4ed8; transform: translateY(-1px); }
        .btn-camera-code { min-height: 44px; border: 1px solid rgba(56, 189, 248, .42); border-radius: 12px; background: rgba(56, 189, 248, .1); color: #bae6fd; font-weight: 900; cursor: pointer; }
        .btn-camera-code:hover { background: rgba(56, 189, 248, .22); color: #fff; transform: translateY(-1px); }
        .btn-confirm-cancel { flex: 1; padding: 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: #fff; font-weight: 800; cursor: pointer; transition: 0.3s; }
        .btn-confirm-cancel:hover { background: rgba(255,255,255,0.1); }
        .btn-confirm-delete { flex: 1; padding: 14px; border-radius: 10px; border: none; background: linear-gradient(135deg, #ef4444, #b91c1c); color: #fff; font-weight: 800; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4); }
        .btn-confirm-delete:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(239, 68, 68, 0.6); }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.3); border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: rgba(6, 182, 212, 0.5); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(6, 182, 212, 0.8); }

        @media (max-width: 860px) {
            .container {
                border-radius: 16px;
            }

            .page-header {
                display: block;
                padding: 22px 16px;
            }

            h1 {
                font-size: 26px;
                line-height: 1.12;
            }

            .header-actions {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-top: 16px;
            }

            .header-actions a,
            .header-actions button,
            .btn-alta,
            .btn-xml,
            .btn-report {
                width: 100%;
                justify-content: center;
                padding: 13px 10px;
                text-align: center;
                font-size: 12px;
            }

            .toolbar {
                padding: 16px;
            }

            .filter-form {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .filter-form select,
            .filter-form input[type="text"],
            .btn-scan,
            .btn-filter,
            .btn-clear {
                width: 100%;
                min-height: 46px;
            }

            .table-wrap {
                padding: 14px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                min-width: 820px;
            }

            .action-buttons {
                flex-wrap: wrap;
                gap: 8px;
            }
        }

        @media (max-width: 460px) {
            .header-actions {
                grid-template-columns: 1fr;
            }

            .container {
                border-radius: 14px;
            }

            .page-header {
                padding: 18px 14px;
            }

            .table-wrap {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="app-shell">
@include('materiales.partials.sidebar')

<main class="app-content">

<!-- Contenedor alineado a la derecha con estilos CSS en línea -->
<div class="legacy-logout" style="text-align: right; margin-bottom: 20px; width: 100%; padding-right: 20px;">
    <form method="POST" action="{{ route('logout') }}" style="display: inline-block;">
        @csrf
        <!-- Botón con estilos forzados para que combine con tu sistema -->
        <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            
            <!-- Icono con ancho y alto forzados a 18px -->
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" style="margin-right: 8px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            
            Cerrar Sesión
        </button>
    </form>
</div>

<div class="container">
    <div class="page-header">
        <div>
            <h1>Inventario de Almacen</h1>
            <p class="header-meta">Consulta por descripción, no. de parte o código de barras.</p>
        </div>

        <div class="header-actions">
            <a href="{{ route('dashboard') }}" class="btn-report btn-dashboard">Dashboard</a>
            <a href="{{ route('reportes.inventario.csv') }}" class="btn-report btn-excel">Excel</a>
            <a href="{{ route('reportes.inventario.pdf') }}" class="btn-report btn-pdf">PDF</a>
            @if(auth()->user()?->puedeMoverStock())
                <a href="{{ route('materiales.salidas.create') }}" class="btn-xml">Registrar Salida</a>
                <a href="{{ route('materiales.create') }}" class="btn-alta">+ Registrar Entrada</a>
            @endif
            @if(auth()->user()?->puedeAdministrarCatalogo())
                <a href="{{ route('materiales.xml.create') }}" class="btn-xml">Importar XML</a>
            @endif
        </div>
    </div>

    <div class="toolbar">
        <form action="{{ route('materiales.index') }}" method="GET" class="filter-form" id="searchForm">
            @if(request('sin_codigo'))
                <input type="hidden" name="sin_codigo" value="1">
            @endif

            <button type="button" class="btn-scan" onclick="abrirEscaner()">Escanear</button>

            <input type="text" name="buscar" id="buscarInput" placeholder="No. parte, código o descripción" value="{{ request('buscar') }}" autocomplete="off" autofocus>

            <select name="filtrar_categoria">
                <option value="">Todas las categorias</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria }}" {{ request('filtrar_categoria') == $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-filter">Buscar</button>

            @if(request('filtrar_categoria') || request('buscar'))
                <a href="{{ route('materiales.index') }}" class="btn-clear">Limpiar</a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(request('sin_codigo'))
        <div class="alert-success">
            Mostrando materiales sin código de barras. Usa "Agregar código" para completar cada registro.
        </div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Categoria</th>
                    <th>No. Parte / Código</th>
                    <th>Descripción</th>
                    <th>Marca</th>
                    <th>Proveedor</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materiales as $material)
                    <tr class="{{ $material->requiereReposicion() ? 'stock-critical' : '' }}">
                        <td>
                            @if($material->fotografia)
                                <img src="{{ asset('storage/' . $material->fotografia) }}" class="img-material" alt="Foto">
                            @else
                                <div class="no-photo">Sin foto</div>
                            @endif
                        </td>
                        <td><span class="badge badge-category">{{ $material->categoria }}</span></td>
                        <td>
                            <strong>{{ $material->numero_parte ?? 'N/A' }}</strong>
                            @if($material->codigo_barras)
                                <span class="code-muted">{{ $material->codigo_barras }}</span>
                            @else
                                <span class="code-muted">Sin código de barras</span>
                            @endif
                        </td>
                        <td>{{ $material->descripcion }}</td>
                        <td>{{ $material->marca ?? 'N/A' }}</td>
                        <td>{{ $material->proveedor ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $material->requiereReposicion() ? 'badge-warning' : ($material->stock > 0 ? 'badge-success' : 'badge-danger') }}">
                                {{ $material->stock }} pzas
                            </span>
                            <span class="stock-meta">
                                Min: {{ $material->stock_minimo ?? 0 }} pzas
                                @if(($material->costo_unitario ?? 0) > 0)
                                    <br>Valor: ${{ number_format($material->stock * $material->costo_unitario, 2) }}
                                @endif
                                @if($material->requiereReposicion())
                                    <br><strong style="color:#fca5a5;">Stock critico</strong>
                                @endif
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if($material->codigo_barras && auth()->user()?->puedeMoverStock())
                                    <a href="{{ route('materiales.etiqueta', $material) }}" class="btn-label">
                                        Etiqueta QR
                                    </a>
                                @endif

                                @if(! $material->codigo_barras && auth()->user()?->puedeAdministrarCatalogo())
                                    <button
                                        type="button"
                                        class="btn-barcode"
                                        onclick="abrirModalCodigo('{{ route('materiales.codigo.guardar', $material) }}', @js($material->descripcion))"
                                    >
                                        Agregar codigo de barras
                                    </button>

                                    <form action="{{ route('materiales.etiqueta.generar', $material) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-code">
                                            Generar QR interno
                                        </button>
                                    </form>
                                @elseif(! $material->codigo_barras)
                                    <span class="code-muted">Pendiente de codigo</span>
                                @endif

                                @if(auth()->user()?->puedeAdministrarCatalogo())
                                    <a href="{{ route('materiales.edit', $material) }}" class="btn-edit">
                                        Editar
                                    </a>
                                    <button
                                        type="button"
                                        class="btn-delete"
                                        data-delete-url="{{ route('materiales.destroy', $material) }}"
                                        data-material-name="{{ $material->descripcion }}"
                                        onclick="confirmarEliminar(this.dataset.deleteUrl, this.dataset.materialName)"
                                    >
                                        Eliminar
                                    </button>
                                @elseif(! auth()->user()?->puedeMoverStock())
                                    <span class="code-muted">Solo consulta</span>
                                @endif
                            </div>
                            <div class="action-buttons" style="display:none;">
                                @unless($material->codigo_barras)
                                    <a href="{{ route('materiales.edit', $material) }}" class="btn-code">
                                        Agregar código
                                    </a>
                                @endunless
                                <a href="{{ route('materiales.edit', $material) }}" class="btn-edit">
                                    ✏️ Editar
                                </a>
                                <button
                                    type="button"
                                    class="btn-delete"
                                    data-delete-url="{{ route('materiales.destroy', $material) }}"
                                    data-material-name="{{ $material->descripcion }}"
                                    onclick="confirmarEliminar( this.dataset.deleteUrl,this.dataset.materialName)"
                                >
                                    🗑️ Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-row">
                            No se encontraron materiales.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding: 0 30px 30px;">
        {{ $materiales->links() }}
    </div>
</div>

</main>
</div>

<div id="scannerModal" class="modal">
    <div class="modal-content">
        <h3 id="scannerTitle">Escanear Codigo</h3>
        <div id="reader"></div>
        <button type="button" class="close-btn" onclick="cerrarEscaner()">Cancelar</button>
    </div>
</div>

<div id="barcodeModal" class="modal">
    <div class="modal-content">
        <h3>Agregar codigo de barras</h3>
        <form method="POST" id="barcodeForm" class="barcode-form">
            @csrf
            @method('PATCH')
            <p class="barcode-help">
                Producto: <strong id="barcodeMaterialName"></strong>
            </p>
            <label for="barcodeInput">Codigo de barras real del producto</label>
            <input
                type="text"
                name="codigo_barras"
                id="barcodeInput"
                placeholder="Escanea con pistolita USB o escribe el codigo"
                autocomplete="off"
                required
            >
            <p class="barcode-help">
                Si usas pistolita USB, solo coloca el cursor aqui y escanea. Si estas en celular, puedes usar la camara.
            </p>
            <div class="barcode-actions">
                <button type="button" class="btn-camera-code" onclick="abrirEscanerParaCodigo()">Escanear con camara</button>
                <button type="submit" class="btn-save-code">Guardar codigo</button>
            </div>
        </form>
        <button type="button" class="close-btn" onclick="cerrarModalCodigo()">Cancelar</button>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-confirm-content">
        <div class="modal-confirm-icon">🗑️</div>
        <h3>Eliminar material</h3>
        <p>Estas a punto de eliminar: <br><strong id="deleteNombre"></strong><br>Esta accion no se puede deshacer.</p>
        <div class="modal-confirm-actions">
            <button type="button" class="btn-confirm-cancel" onclick="cerrarModalEliminar()">Cancelar</button>
            <button type="button" class="btn-confirm-delete" onclick="ejecutarEliminar()">Si, eliminar</button>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const buscarInput = document.getElementById('buscarInput');
    const searchForm = document.getElementById('searchForm');
    const barcodeModal = document.getElementById('barcodeModal');
    const barcodeForm = document.getElementById('barcodeForm');
    const barcodeInput = document.getElementById('barcodeInput');
    const barcodeMaterialName = document.getElementById('barcodeMaterialName');
    const scannerTitle = document.getElementById('scannerTitle');
    let html5QrcodeScanner = null;
    let scannerTarget = 'buscar';
    let scannerBuffer = '';
    let scannerBufferInicio = 0;
    let scannerUltimaTecla = 0;
    let scannerResetTimer = null;

    function buscarCodigoEscaneado(codigo) {
        const codigoLimpio = codigo.trim();

        if (!codigoLimpio) {
            return;
        }

        buscarInput.value = codigoLimpio;
        cerrarEscaner();
        searchForm.submit();
    }

    function abrirEscaner() {
        scannerTarget = 'buscar';
        scannerTitle.textContent = 'Escanear codigo para buscar';
        document.getElementById('scannerModal').style.display = 'flex';

        html5QrcodeScanner = new Html5QrcodeScanner(
            'reader',
            { fps: 10, qrbox: { width: 250, height: 250 } },
            false
        );

        html5QrcodeScanner.render((textoDecodificado) => {
            usarCodigoEscaneado(textoDecodificado);
        }, () => {});

        observarTraduccionEscaner();
    }

    function abrirEscanerParaCodigo() {
        scannerTarget = 'asignar';
        scannerTitle.textContent = 'Escanear codigo de barras';
        document.getElementById('scannerModal').style.display = 'flex';

        html5QrcodeScanner = new Html5QrcodeScanner(
            'reader',
            { fps: 10, qrbox: { width: 250, height: 250 } },
            false
        );

        html5QrcodeScanner.render((textoDecodificado) => {
            usarCodigoEscaneado(textoDecodificado);
        }, () => {});

        observarTraduccionEscaner();
    }

    function usarCodigoEscaneado(codigo) {
        const codigoLimpio = codigo.trim();

        if (!codigoLimpio) {
            return;
        }

        if (scannerTarget === 'asignar') {
            barcodeInput.value = codigoLimpio;
            cerrarEscaner();
            barcodeInput.focus();
            barcodeInput.select();
            return;
        }

        buscarCodigoEscaneado(codigoLimpio);
    }

    function cerrarEscaner() {
        document.getElementById('scannerModal').style.display = 'none';

        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }

        if (window.escanerTraductorObserver) {
            window.escanerTraductorObserver.disconnect();
            window.escanerTraductorObserver = null;
        }
    }

    function abrirModalCodigo(url, nombre) {
        barcodeForm.action = url;
        barcodeInput.value = '';
        barcodeMaterialName.textContent = nombre;
        barcodeModal.style.display = 'flex';

        setTimeout(() => barcodeInput.focus(), 80);
    }

    function cerrarModalCodigo() {
        barcodeForm.action = '';
        barcodeInput.value = '';
        barcodeModal.style.display = 'none';
    }

    function traducirEscanerHtml5() {
        const traducciones = {
            'Request Camera Permissions': 'Permitir cámara',
            'Scan an Image File': 'Escanear una imagen guardada',
            'Scan using camera directly': 'Escanear con cámara',
            'Select Camera': 'Seleccionar cámara',
            'Start Scanning': 'Iniciar escaneo',
            'Stop Scanning': 'Detener escaneo',
            'Choose Image': 'Elegir imagen',
            'Scanning': 'Escaneando',
            'Idle': 'Listo',
            'No camera found': 'No se encontró cámara',
            'Camera permission denied': 'Permiso de cámara denegado',
            'Unable to query supported devices.': 'No se pudieron consultar las cámaras disponibles.',
            'Camera access is only supported in secure context like https or localhost.': 'La cámara solo funciona en localhost o con una conexión segura HTTPS.',
        };

        const walker = document.createTreeWalker(
            document.getElementById('reader'),
            NodeFilter.SHOW_TEXT
        );

        const nodos = [];
        while (walker.nextNode()) {
            nodos.push(walker.currentNode);
        }

        nodos.forEach((nodo) => {
            const texto = nodo.nodeValue.trim();

            if (traducciones[texto]) {
                nodo.nodeValue = nodo.nodeValue.replace(texto, traducciones[texto]);
            }
        });
    }

    function observarTraduccionEscaner() {
        traducirEscanerHtml5();

        if (window.escanerTraductorObserver) {
            window.escanerTraductorObserver.disconnect();
        }

        const reader = document.getElementById('reader');
        window.escanerTraductorObserver = new MutationObserver(traducirEscanerHtml5);
        window.escanerTraductorObserver.observe(reader, {
            childList: true,
            subtree: true,
            characterData: true,
        });

        setTimeout(traducirEscanerHtml5, 100);
        setTimeout(traducirEscanerHtml5, 400);
        setTimeout(traducirEscanerHtml5, 1000);
    }

    buscarInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            buscarCodigoEscaneado(buscarInput.value);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (
            event.ctrlKey
            || event.altKey
            || event.metaKey
            || document.activeElement === buscarInput
            || document.activeElement === barcodeInput
        ) {
            return;
        }

        const ahora = Date.now();

        if (event.key.length === 1) {
            if (ahora - scannerUltimaTecla > 80) {
                scannerBuffer = '';
                scannerBufferInicio = ahora;
            }

            scannerBuffer += event.key;
            scannerUltimaTecla = ahora;
            clearTimeout(scannerResetTimer);
            scannerResetTimer = setTimeout(() => {
                scannerBuffer = '';
            }, 160);
            return;
        }

        if (event.key === 'Enter' && scannerBuffer.length >= 6 && ahora - scannerBufferInicio < 1200) {
            event.preventDefault();
            const codigo = scannerBuffer;
            scannerBuffer = '';

            if (barcodeModal.style.display === 'flex') {
                barcodeInput.value = codigo.trim();
                barcodeInput.focus();
                barcodeInput.select();
                return;
            }

            buscarCodigoEscaneado(codigo);
        }
    });

    let deleteUrl = null;

function confirmarEliminar(url, nombre) {
    deleteUrl = url;

    document.getElementById('deleteNombre').textContent = nombre;
    document.getElementById('deleteModal').style.display = 'flex';
}

function cerrarModalEliminar() {
    deleteUrl = null;
    document.getElementById('deleteModal').style.display = 'none';
}

function ejecutarEliminar() {
    if (!deleteUrl) {
        return;
    }

    const form = document.getElementById('deleteForm');
    form.action = deleteUrl;
    form.submit();
}

    // Cerrar modal al hacer clic fuera
    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) cerrarModalEliminar();
    });

    barcodeModal.addEventListener('click', function (e) {
        if (e.target === this) cerrarModalCodigo();
    });
</script>

</body>
</html>
