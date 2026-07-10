<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Inventario - Almacen</title>
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
            padding: 28px 18px;
        }

        .container {
            width: min(1220px, 100%);
            margin: 0 auto;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            padding: 24px 28px;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }

        h1 {
            margin: 0;
            color: var(--blue-dark);
            font-size: 28px;
            line-height: 1.2;
        }

        .header-meta {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .btn-alta,
        .btn-filter,
        .btn-clear,
        .btn-scan,
        .close-btn {
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 800;
            font-family: inherit;
            text-decoration: none;
            transition: background 0.2s, box-shadow 0.2s;
            white-space: nowrap;
        }

        .btn-alta {
            background-color: var(--blue);
            color: #fff;
            padding: 12px 16px;
        }

        .btn-alta:hover {
            background-color: var(--blue-dark);
            box-shadow: 0 10px 24px rgba(37, 99, 168, 0.22);
        }

        .toolbar {
            padding: 20px 28px 0;
        }

        .filter-form {
            display: grid;
            grid-template-columns: auto minmax(220px, 1fr) minmax(210px, 280px) auto auto;
            gap: 10px;
            align-items: stretch;
        }

        .filter-form select,
        .filter-form input[type="text"] {
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            color: var(--ink);
            font-family: inherit;
        }

        .filter-form select:focus,
        .filter-form input[type="text"]:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 168, 0.16);
        }

        .btn-scan {
            background-color: var(--amber);
            color: white;
            padding: 0 14px;
        }

        .btn-scan:hover {
            background-color: #a8620c;
        }

        .btn-filter {
            background-color: var(--green);
            color: white;
            padding: 0 16px;
        }

        .btn-filter:hover {
            background-color: #116a40;
        }

        .btn-clear {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #e6ecf2;
            color: var(--ink);
            padding: 0 14px;
        }

        .btn-clear:hover {
            background-color: #d5dee8;
        }

        .alert-success {
            margin: 20px 28px 0;
            background-color: #eaf8f0;
            color: #0f6b3e;
            padding: 13px 15px;
            border-radius: 6px;
            border: 1px solid #a9dfbf;
            font-weight: 700;
        }

        .table-wrap {
            padding: 20px 28px 28px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 860px;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 13px 14px;
            text-align: left;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }

        th {
            background-color: #f8fafc;
            color: var(--blue-dark);
            font-size: 13px;
            text-transform: uppercase;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background-color: #f7fbff;
        }

        .img-material {
            width: 68px;
            height: 68px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--line);
            background-color: #f9fafb;
        }

        .no-photo {
            width: 68px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: var(--muted);
            background-color: #f3f6f9;
            border-radius: 6px;
            border: 1px dashed #b7c3cf;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.86em;
            display: inline-block;
        }

        .badge-success {
            background-color: #eaf8f0;
            color: #0f6b3e;
            border: 1px solid #a9dfbf;
        }

        .badge-danger {
            background-color: #fff1f0;
            color: #842029;
            border: 1px solid #f2b8b5;
        }

        .badge-category {
            background-color: #fff7e6;
            color: #8a5700;
            border: 1px solid #ffd98a;
        }

        .code-muted {
            display: block;
            color: var(--muted);
            font-size: 12px;
            margin-top: 4px;
        }

        .empty-row {
            text-align: center;
            color: var(--muted);
            padding: 44px;
            font-weight: 700;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(14, 23, 34, 0.68);
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: min(500px, 100%);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
        }

        .modal-content h3 {
            margin: 0 0 14px;
            color: var(--blue-dark);
        }

        .close-btn {
            background-color: var(--red);
            color: white;
            padding: 12px 18px;
            width: 100%;
            margin-top: 14px;
        }

        .close-btn:hover {
            background-color: #9f312b;
        }

        #reader {
            width: 100%;
            min-height: 250px;
        }

        /* ✅ NUEVO: Estilos para los botones de acción */
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-edit,
        .btn-delete {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 13px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
            font-family: inherit;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
            white-space: nowrap;
        }

        .btn-edit {
            background-color: #e8f0fb;
            color: var(--blue-dark);
            border: 1px solid #bdd0f0;
        }

        .btn-edit:hover {
            background-color: var(--blue);
            color: #fff;
            border-color: var(--blue);
            box-shadow: 0 4px 12px rgba(37, 99, 168, 0.22);
            transform: translateY(-1px);
        }

        .btn-delete {
            background-color: #fef1f0;
            color: var(--red);
            border: 1px solid #f2b8b5;
        }

        .btn-delete:hover {
            background-color: var(--red);
            color: #fff;
            border-color: var(--red);
            box-shadow: 0 4px 12px rgba(194, 65, 58, 0.22);
            transform: translateY(-1px);
        }

        /* ✅ NUEVO: Modal de confirmación de eliminación */
        .modal-confirm-content {
            background-color: #fff;
            padding: 28px 24px 24px;
            border-radius: 10px;
            width: min(420px, 100%);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
        }

        .modal-confirm-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background-color: #fff1f0;
            border: 2px solid #f2b8b5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 24px;
        }

        .modal-confirm-content h3 {
            margin: 0 0 8px;
            color: var(--ink);
            font-size: 18px;
            text-align: center;
        }

        .modal-confirm-content p {
            margin: 0 0 22px;
            color: var(--muted);
            font-size: 14px;
            text-align: center;
            line-height: 1.5;
        }

        .modal-confirm-content p strong {
            color: var(--ink);
        }

        .modal-confirm-actions {
            display: flex;
            gap: 10px;
        }

        .btn-confirm-cancel {
            flex: 1;
            padding: 11px;
            border-radius: 6px;
            border: 1px solid var(--line);
            background-color: #f3f6f9;
            color: var(--ink);
            font-weight: 800;
            font-family: inherit;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-confirm-cancel:hover {
            background-color: #e6ecf2;
        }

        .btn-confirm-delete {
            flex: 1;
            padding: 11px;
            border-radius: 6px;
            border: none;
            background-color: var(--red);
            color: #fff;
            font-weight: 800;
            font-family: inherit;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
        }

        .btn-confirm-delete:hover {
            background-color: #9f312b;
            box-shadow: 0 4px 14px rgba(194, 65, 58, 0.28);
        }

        @media (max-width: 900px) {
            .filter-form {
                grid-template-columns: 1fr 1fr;
            }

            .filter-form input[type="text"],
            .filter-form select {
                grid-column: span 2;
            }
        }

        @media (max-width: 620px) {
            body {
                padding: 14px 10px;
            }

            .page-header,
            .toolbar,
            .table-wrap {
                padding-left: 16px;
                padding-right: 16px;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .filter-form input[type="text"],
            .filter-form select {
                grid-column: auto;
            }

            .btn-scan,
            .btn-filter,
            .btn-clear {
                min-height: 44px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <div>
            <h1>Inventario de Almacen</h1>
            <p class="header-meta">Consulta por descripcion, no. de parte o codigo de barras.</p>
        </div>

        <a href="{{ route('materiales.create') }}" class="btn-alta">+ Registrar Entrada</a>
    </div>

    <div class="toolbar">
        <form action="{{ route('materiales.index') }}" method="GET" class="filter-form" id="searchForm">
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
                    {{-- ✅ NUEVO: Columna de acciones --}}
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
                        {{-- ✅ NUEVO: Botones editar y eliminar --}}
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('materiales.edit', ['material' => $material->id]) }}" class="btn-edit">
                                    ✏️ Editar
                                </a>
                                <button
                                    type="button"
                                    class="btn-delete"
                                    data-delete-url="{{ route('materiales.destroy', ['material' => $material->id]) }}"
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

{{-- Scanner modal (sin cambios) --}}
<div id="scannerModal" class="modal">
    <div class="modal-content">
        <h3>Escanear Codigo</h3>
        <div id="reader"></div>
        <button type="button" class="close-btn" onclick="cerrarEscaner()">Cancelar</button>
    </div>
</div>

{{-- ✅ NUEVO: Modal de confirmación de eliminación --}}
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

{{-- ✅ NUEVO: Formulario oculto para el DELETE --}}
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

    // ✅ NUEVO: Lógica del modal de eliminación
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