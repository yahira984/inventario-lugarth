<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Inventario - Almacen</title>
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
        .btn-alta, .btn-xml, .btn-filter, .btn-scan, .close-btn {
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

        .btn-alta:hover, .btn-xml:hover {
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

        .code-muted { display: block; color: var(--cyan-glow); font-size: 12px; margin-top: 6px; font-weight: 600;}
        td strong { font-size: 16px; letter-spacing: 0.5px; }

        /* --- BOTONES DE ACCIÓN FLOTANTES --- */
        .action-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-edit, .btn-code, .btn-delete {
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
        .modal-confirm-icon { width: 60px; height: 60px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); border: 2px solid rgba(239, 68, 68, 0.5); color: #f87171; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 28px; box-shadow: 0 0 20px rgba(239, 68, 68, 0.3); }
        .modal-confirm-content p { margin: 0 0 24px; color: var(--muted); font-size: 15px; text-align: center; line-height: 1.6; }
        .modal-confirm-content p strong { color: #fff; font-size: 16px;}
        .modal-confirm-actions { display: flex; gap: 12px; }
        .btn-confirm-cancel { flex: 1; padding: 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: #fff; font-weight: 800; cursor: pointer; transition: 0.3s; }
        .btn-confirm-cancel:hover { background: rgba(255,255,255,0.1); }
        .btn-confirm-delete { flex: 1; padding: 14px; border-radius: 10px; border: none; background: linear-gradient(135deg, #ef4444, #b91c1c); color: #fff; font-weight: 800; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4); }
        .btn-confirm-delete:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(239, 68, 68, 0.6); }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.3); border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: rgba(6, 182, 212, 0.5); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(6, 182, 212, 0.8); }
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
            <p class="header-meta">Consulta por descripcion, no. de parte o codigo de barras.</p>
        </div>

        <div class="header-actions">
            <a href="{{ route('materiales.xml.create') }}" class="btn-xml">Importar XML</a>
            <a href="{{ route('materiales.create') }}" class="btn-alta">+ Registrar Entrada</a>
        </div>
    </div>

    <div class="toolbar">
        <form action="{{ route('materiales.index') }}" method="GET" class="filter-form" id="searchForm">
            @if(request('sin_codigo'))
                <input type="hidden" name="sin_codigo" value="1">
            @endif

            <button type="button" class="btn-scan" onclick="abrirEscaner()">Escanear</button>

            <input type="text" name="buscar" id="buscarInput" placeholder="No. parte, codigo o descripcion" value="{{ request('buscar') }}" autocomplete="off" autofocus>

            <select name="filtrar_categoria">
                <option value="">Todas las categorias</option>
                <option value="EQUIPO ACERO AL CARBON" {{ request('filtrar_categoria') == 'EQUIPO ACERO AL CARBON' ? 'selected' : '' }}>EQUIPO ACERO AL CARBON</option>
                <option value="EQUIPO ACERO INOXIDABLE" {{ request('filtrar_categoria') == 'EQUIPO ACERO INOXIDABLE' ? 'selected' : '' }}>EQUIPO ACERO INOXIDABLE</option>
                <option value="EQUIPO TIPO ASA INOXIDABLE" {{ request('filtrar_categoria') == 'EQUIPO TIPO ASA INOXIDABLE' ? 'selected' : '' }}>EQUIPO TIPO ASA INOXIDABLE</option>
                <option value="EQUIPO AC SIST DSPCH MEC FILL" {{ request('filtrar_categoria') == 'EQUIPO AC SIST DSPCH MEC FILL' ? 'selected' : '' }}>EQUIPO AC SIST DSPCH MEC FILL</option>
                <option value="EQUIPO AC SIST DSPCH MEC LIQUID" {{ request('filtrar_categoria') == 'EQUIPO AC SIST DSPCH MEC LIQUID' ? 'selected' : '' }}>EQUIPO AC SIST DSPCH MEC LIQUID</option>
                <option value="EQUIPO ACERO AL CARBON UPV" {{ request('filtrar_categoria') == 'EQUIPO ACERO AL CARBON UPV' ? 'selected' : '' }}>EQUIPO ACERO AL CARBON UPV</option>
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
            Mostrando materiales sin codigo de barras. Usa "Agregar codigo" para completar cada registro.
        </div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Categoria</th>
                    <th>No. Parte / Codigo</th>
                    <th>Descripcion</th>
                    <th>Marca</th>
                    <th>Proveedor</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materiales as $material)
                    <tr>
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
                                <span class="code-muted">Sin codigo de barras</span>
                            @endif
                        </td>
                        <td>{{ $material->descripcion }}</td>
                        <td>{{ $material->marca ?? 'N/A' }}</td>
                        <td>{{ $material->proveedor ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $material->stock > 0 ? 'badge-success' : 'badge-danger' }}">
                                {{ $material->stock }} pzas
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                @unless($material->codigo_barras)
                                    <a href="{{ route('materiales.edit', $material) }}" class="btn-code">
                                        Agregar codigo
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
</div>

</main>
</div>

<div id="scannerModal" class="modal">
    <div class="modal-content">
        <h3>Escanear Codigo</h3>
        <div id="reader"></div>
        <button type="button" class="close-btn" onclick="cerrarEscaner()">Cancelar</button>
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
    let html5QrcodeScanner = null;
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
        document.getElementById('scannerModal').style.display = 'flex';

        html5QrcodeScanner = new Html5QrcodeScanner(
            'reader',
            { fps: 10, qrbox: { width: 250, height: 250 } },
            false
        );

        html5QrcodeScanner.render((textoDecodificado) => {
            buscarCodigoEscaneado(textoDecodificado);
        }, () => {});
    }

    function cerrarEscaner() {
        document.getElementById('scannerModal').style.display = 'none';

        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }
    }

    buscarInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            buscarCodigoEscaneado(buscarInput.value);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey || event.altKey || event.metaKey || document.activeElement === buscarInput) {
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
</script>

</body>
</html>