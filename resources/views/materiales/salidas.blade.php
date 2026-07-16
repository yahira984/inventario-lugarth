<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Salida - Inventario</title>

    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    
    <style>
        :root {
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.72);
            --panel: rgba(30, 41, 59, 0.72);
            --ink: #ffffff;
            --muted: #94a3b8;
            --cyan: #06b6d4;
            --blue: #3b82f6;
            --green: #10b981;
            --amber: #f59e0b;
            --red: #ef4444;
            --line: rgba(56, 189, 248, 0.22);
            --shadow: 0 18px 55px rgba(0, 0, 0, 0.55);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top left, #0a192f 0%, #030712 100%);
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .app-shell { display: flex; min-height: 100vh; }
        .app-content { flex: 1; padding: 34px 20px; overflow-x: hidden; }

        .container {
            width: min(1180px, 100%);
            margin: 0 auto;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 30px;
            backdrop-filter: blur(16px);
        }

        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 20px;
            margin-bottom: 22px;
        }

        h1, h2, h3 { margin: 0; }

        h1 {
            font-size: 32px;
            font-weight: 900;
            background: linear-gradient(to right, #00f2fe, #4facfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-meta {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(320px, 0.8fr) minmax(0, 1.2fr);
            gap: 20px;
            align-items: start;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 20px;
        }

        .panel h2 {
            font-size: 18px;
            color: #bae6fd;
            margin-bottom: 14px;
        }

        label {
            display: block;
            color: #7dd3fc;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            min-height: 46px;
            padding: 12px 14px;
            background: rgba(0, 0, 0, 0.36);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: #fff;
            font-family: inherit;
            font-size: 14px;
            outline: none;
        }

        textarea { min-height: 88px; resize: vertical; }

        input:focus,
        textarea:focus {
            border-color: var(--cyan);
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.18);
        }

        .field { margin-bottom: 15px; }
        .field-help { color: var(--muted); font-size: 12px; margin-top: 7px; line-height: 1.4; }
        .field-error { color: #fca5a5; font-size: 12px; font-weight: 800; margin-top: 7px; }

        .input-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
        }

        .btn {
            min-height: 44px;
            border: none;
            border-radius: 10px;
            padding: 0 16px;
            color: #fff;
            font-family: inherit;
            font-weight: 900;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s, filter 0.2s;
        }

        .btn:hover { transform: translateY(-1px); filter: brightness(1.08); }
        
        /* --- NUEVOS COLORES DE BOTONES --- */
        .btn-primary { background: linear-gradient(135deg, #cdc8d8, #5b21b6); } /* Púrpura */
        .btn-blue { background: linear-gradient(135deg, #f59e0b, #d97706); } /* Ámbar/Naranja */
        .btn-green { background: linear-gradient(135deg, #14b8a6, #0f766e); } /* Verde azulado (Teal) */
        .btn-soft { background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); color: #ddd6fe; } /* Suave púrpura */
        
        .header-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; }

        .selected-card {
            display: none;
            gap: 12px;
            align-items: center;
            border: 1px solid rgba(16, 185, 129, 0.38);
            background: rgba(16, 185, 129, 0.1);
            border-radius: 14px;
            padding: 13px;
            margin-bottom: 16px;
        }

        .selected-card.active { display: flex; }

        .selected-card img,
        .manual-card img,
        .history-photo {
            width: 62px;
            height: 62px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.06);
        }

        .no-photo {
            width: 62px;
            height: 62px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1px dashed rgba(125, 211, 252, 0.45);
            color: #7dd3fc;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .selected-name,
        .manual-title {
            font-weight: 900;
            line-height: 1.25;
        }

        .muted {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .category-badge,
        .storage-badge {
            display: inline-flex;
            width: fit-content;
            max-width: 100%;
            border-radius: 8px;
            padding: 5px 8px;
            font-size: 11px;
            font-weight: 900;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .category-badge {
            margin-bottom: 7px;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: #ffffff;
            border: 1px solid rgba(37, 99, 235, 0.55);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
        }

        .storage-badge {
            margin-top: 7px;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: #ffffff;
            border: 1px solid rgba(37, 99, 235, 0.55);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
        }

        .status {
            display: none;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13px;
            font-weight: 800;
            margin-top: 10px;
        }

        .status:not(:empty) { display: block; }
        .status.info { background: rgba(6, 182, 212, 0.12); color: #7dd3fc; border: 1px solid rgba(6, 182, 212, 0.28); }
        .status.success { background: rgba(16, 185, 129, 0.12); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.28); }
        .status.warning { background: rgba(245, 158, 11, 0.12); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.28); }
        .status.error { background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.28); }

        .alert-success,
        .alert-danger {
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
            font-weight: 800;
        }

        .alert-success { background: rgba(16, 185, 129, 0.12); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); }
        .alert-danger { background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .alert-danger ul { margin: 8px 0 0; padding-left: 18px; }

        .manual-search {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            margin-bottom: 14px;
        }

        .manual-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 12px;
            max-height: 520px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .manual-card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.72);
            border-radius: 14px;
            padding: 12px;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 10px;
            align-items: center;
        }

        .manual-card.out {
            opacity: 0.58;
        }

        .stock-pill {
            display: inline-flex;
            width: fit-content;
            border-radius: 999px;
            padding: 4px 9px;
            margin-top: 6px;
            font-size: 12px;
            font-weight: 900;
            background: rgba(16, 185, 129, 0.14);
            color: #6ee7b7;
        }

        .stock-pill.empty {
            background: rgba(239, 68, 68, 0.14);
            color: #fca5a5;
        }

        .history {
            margin-top: 20px;
        }

        .history-list {
            display: grid;
            gap: 10px;
        }

        .history-item {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            background: rgba(15, 23, 42, 0.62);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 10px;
        }

        .history-qty {
            color: #fca5a5;
            font-size: 18px;
            font-weight: 900;
            white-space: nowrap;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.84);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-content {
            background: #0f172a;
            border: 1px solid rgba(139, 92, 246, 0.32);
            padding: 26px;
            border-radius: 18px;
            width: min(460px, 100%);
            box-shadow: var(--shadow);
        }

        .modal-content h3 {
            text-align: center;
            margin-bottom: 14px;
        }

        #reader {
            width: 100%;
            min-height: 250px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px dashed rgba(139, 92, 246, 0.5);
        }

        #reader button,
        #reader a {
            background: rgba(139, 92, 246, 0.14) !important;
            border: 1px solid rgba(139, 92, 246, 0.45) !important;
            color: #ddd6fe !important;
            border-radius: 8px !important;
            padding: 9px 12px !important;
            font-family: inherit !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            text-decoration: none !important;
            cursor: pointer !important;
        }

        @media (max-width: 980px) {
            .grid { grid-template-columns: 1fr; }
            .page-header { display: block; }
        }

        @media (max-width: 640px) {
            .app-content { padding: 16px 10px; }
            .container { padding: 18px 14px; border-radius: 16px; }
            h1 { font-size: 26px; line-height: 1.12; }
            .header-actions { display: grid; grid-template-columns: 1fr; margin-top: 16px; }
            .header-actions .btn { width: 100%; }
            .input-row,
            .manual-search { grid-template-columns: 1fr; }
            .manual-grid { grid-template-columns: 1fr; max-height: none; }
            .panel { padding: 16px; }
            .btn { width: 100%; }
            .history-item { grid-template-columns: auto minmax(0, 1fr); }
            .history-qty { grid-column: 2; }
        }
    </style>
</head>
<body>

<div class="app-shell">
    @include('materiales.partials.sidebar')

    <main class="app-content">
        <div class="container">
            <div class="page-header">
                <div>
                    <h1>Registrar salida</h1>
                    <p class="header-meta">Escanea código, usa pistola USB o selecciona el producto manualmente.</p>
                </div>

                <div class="header-actions">
                    <a href="{{ route('reportes.salidas.csv') }}" class="btn btn-green">Excel</a>
                    <a href="{{ route('reportes.salidas.pdf') }}" class="btn btn-blue">PDF</a>
                    <a href="{{ route('materiales.index') }}" class="btn btn-soft">Volver al inventario</a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-danger">
                    <strong>No se pudo registrar la salida:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid">
                <section class="panel">
                    <h2>Salida del producto</h2>

                    <form action="{{ route('materiales.salidas.store') }}" method="POST" id="salidaForm">
                        @csrf
                        <input type="hidden" name="material_id" id="material_id" value="{{ old('material_id') }}">

                        <div class="field">
                            <label for="codigo_barras">Código de barras</label>
                            <div class="input-row">
                                <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras') }}" placeholder="Escanea o escribe el código" autocomplete="off" autofocus>
                                <button type="button" class="btn btn-blue" onclick="abrirEscaner()">Cámara</button>
                            </div>
                            <div class="field-help">También funciona con pistola USB: apunta al código y dispara.</div>
                            <div id="codigo_status" class="status"></div>
                            @error('codigo_barras') <div class="field-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="selected-card" id="selectedCard">
                            <div id="selectedPhoto" class="no-photo">Sin foto</div>
                            <div>
                                <div class="selected-name" id="selectedName">Sin producto seleccionado</div>
                                <div class="muted" id="selectedMeta"></div>
                                <div class="storage-badge" id="selectedCategory" style="display:none;"></div>
                            </div>
                        </div>

                        <div class="field">
                            <label for="cantidad">Cantidad a sacar *</label>
                            <input type="number" name="cantidad" id="cantidad" value="{{ old('cantidad', 1) }}" min="1" required>
                            <div class="field-help" id="cantidadHelp">El sistema no permitirá sacar más piezas que el stock disponible.</div>
                            @error('cantidad') <div class="field-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="field">
                            <label for="referencia">Referencia / orden</label>
                            <input type="text" name="referencia" id="referencia" value="{{ old('referencia') }}" placeholder="Ej. OT-1025, mantenimiento, venta">
                            @error('referencia') <div class="field-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="field">
                            <label for="motivo">Notas de salida</label>
                            <textarea name="motivo" id="motivo" placeholder="Quién lo pidió, para qué equipo o cualquier detalle útil">{{ old('motivo') }}</textarea>
                            @error('motivo') <div class="field-error">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Registrar salida y descontar stock
                        </button>
                    </form>
                </section>

                <section class="panel">
                    <h2>Buscar manualmente</h2>

                    <form action="{{ route('materiales.salidas.create') }}" method="GET" class="manual-search">
                        <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Descripcion, no. parte, categoria, almacen, marca o codigo">
                        <button type="submit" class="btn btn-green">Buscar</button>
                    </form>

                    <div class="manual-grid">
                        @forelse($materiales as $material)
                            <article class="manual-card {{ $material->stock <= 0 ? 'out' : '' }}">
                                @if($material->fotografia)
                                    <img src="{{ asset('storage/' . $material->fotografia) }}" alt="Foto">
                                @else
                                    <div class="no-photo">Sin foto</div>
                                @endif

                                <div>
                                    <div class="category-badge">{{ $material->categoria ?: 'Sin categoria' }}</div>
                                    <div class="manual-title">{{ $material->descripcion }}</div>
                                    <div class="muted">{{ $material->numero_parte ?? 'N/A' }} · {{ $material->marca ?? 'Sin marca' }}</div>
                                    <div class="muted">Almacen: {{ $material->almacen ?: 'Sin definir' }}</div>
                                    <span class="stock-pill {{ $material->stock <= 0 ? 'empty' : '' }}">{{ $material->stock }} pzas</span>
                                    <button
                                        type="button"
                                        class="btn btn-soft"
                                        style="width: 100%; margin-top: 10px;"
                                        data-id="{{ $material->id }}"
                                        data-descripcion="{{ $material->descripcion }}"
                                        data-numero-parte="{{ $material->numero_parte }}"
                                        data-codigo="{{ $material->codigo_barras }}"
                                        data-marca="{{ $material->marca }}"
                                        data-categoria="{{ $material->categoria }}"
                                        data-almacen="{{ $material->almacen }}"
                                        data-stock="{{ $material->stock }}"
                                        data-foto="{{ $material->fotografia ? asset('storage/' . $material->fotografia) : '' }}"
                                        onclick="seleccionarDesdeBoton(this)"
                                        {{ $material->stock <= 0 ? 'disabled' : '' }}
                                    >
                                        {{ $material->stock <= 0 ? 'Sin stock' : 'Seleccionar' }}
                                    </button>
                                </div>
                            </article>
                        @empty
                            <p class="muted">No se encontraron materiales con esa búsqueda.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <section class="panel history">
                <h2>Salidas recientes</h2>

                <div class="history-list">
                    @forelse($salidasRecientes as $salida)
                        <article class="history-item">
                            @if($salida->material?->fotografia)
                                <img src="{{ asset('storage/' . $salida->material->fotografia) }}" class="history-photo" alt="Foto">
                            @else
                                <div class="no-photo">Sin foto</div>
                            @endif

                            <div>
                                <div class="manual-title">{{ $salida->material?->descripcion ?? 'Material eliminado' }}</div>
                                <div class="muted">
                                    {{ $salida->created_at->format('d/m/Y H:i') }}
                                    · {{ $salida->user?->name ?? 'Usuario no disponible' }}
                                    @if($salida->material?->categoria)
                                        · {{ $salida->material->categoria }}
                                    @endif
                                    @if($salida->referencia)
                                        · {{ $salida->referencia }}
                                    @endif
                                </div>
                                @if($salida->motivo)
                                    <div class="muted">{{ $salida->motivo }}</div>
                                @endif
                            </div>

                            <div class="history-qty">-{{ $salida->cantidad }} pzas</div>
                        </article>
                    @empty
                        <p class="muted">Aún no hay salidas registradas.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
</div>

<div id="scannerModal" class="modal">
    <div class="modal-content">
        <h3>Escanear código</h3>
        <div id="reader"></div>
        <button type="button" class="btn btn-primary" style="width: 100%; margin-top: 16px;" onclick="cerrarEscaner()">Cancelar</button>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const codigoInput = document.getElementById('codigo_barras');
    const materialInput = document.getElementById('material_id');
    const cantidadInput = document.getElementById('cantidad');
    const cantidadHelp = document.getElementById('cantidadHelp');
    const statusBox = document.getElementById('codigo_status');
    const selectedCard = document.getElementById('selectedCard');
    const selectedName = document.getElementById('selectedName');
    const selectedMeta = document.getElementById('selectedMeta');
    const selectedCategory = document.getElementById('selectedCategory');
    let html5QrcodeScanner = null;
    let busquedaTimer = null;
    let scannerBuffer = '';
    let scannerBufferInicio = 0;
    let scannerUltimaTecla = 0;
    let scannerResetTimer = null;

    function setStatus(mensaje, tipo) {
        statusBox.textContent = mensaje;
        statusBox.className = `status ${tipo}`;
    }

    function seleccionarMaterial(material) {
        materialInput.value = material.id;
        codigoInput.value = material.codigo_barras || codigoInput.value;
        selectedName.textContent = material.descripcion;
        selectedMeta.textContent = `No. parte: ${material.numero_parte || 'N/A'} · Marca: ${material.marca || 'N/A'} · Stock: ${material.stock} pzas`;
        selectedCategory.textContent = `Categoria: ${material.categoria || 'Sin categoria'} · Almacen: ${material.almacen || 'Sin definir'}`;
        selectedCategory.style.display = 'inline-flex';
        cantidadInput.max = material.stock;
        cantidadHelp.textContent = `Disponible: ${material.stock} pzas. No podrás registrar una salida mayor.`;
        selectedCard.classList.add('active');

        const fotoActual = document.getElementById('selectedPhoto');

        if (material.foto) {
            fotoActual.outerHTML = `<img src="${material.foto}" id="selectedPhoto" alt="Foto">`;
        } else {
            fotoActual.outerHTML = '<div id="selectedPhoto" class="no-photo">Sin foto</div>';
        }

        if (Number(cantidadInput.value || 0) > Number(material.stock)) {
            cantidadInput.value = material.stock;
        }

        setStatus(`Producto seleccionado: ${material.descripcion}`, 'success');
        cantidadInput.focus();
    }

    function seleccionarDesdeBoton(button) {
        seleccionarMaterial({
            id: button.dataset.id,
            descripcion: button.dataset.descripcion,
            numero_parte: button.dataset.numeroParte,
            codigo_barras: button.dataset.codigo,
            marca: button.dataset.marca,
            categoria: button.dataset.categoria,
            almacen: button.dataset.almacen,
            stock: Number(button.dataset.stock),
            foto: button.dataset.foto
        });
    }

    function consultarCodigo(codigo, forzar = false) {
        const codigoLimpio = codigo.trim();

        if (!codigoLimpio) {
            return;
        }

        setStatus('Buscando código en inventario...', 'info');

        fetch(`{{ route('materiales.buscarPorCodigo') }}?codigo=${encodeURIComponent(codigoLimpio)}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error('No se pudo consultar el código.');
                }

                return response.json();
            })
            .then((data) => {
                if (!data.encontrado) {
                    materialInput.value = '';
                    selectedCard.classList.remove('active');
                    if (data.multiples) {
                        setStatus(data.mensaje, 'warning');
                        return;
                    }
                    setStatus('No encontramos ese codigo. Busca el material manualmente.', 'warning');
                    return;
                }

                if (Number(data.stock) <= 0) {
                    materialInput.value = data.id;
                    selectedCard.classList.remove('active');
                    setStatus(`"${data.descripcion}" no tiene stock disponible.`, 'error');
                    return;
                }

                seleccionarMaterial({
                    id: data.id,
                    descripcion: data.descripcion,
                    numero_parte: data.numero_parte,
                    codigo_barras: data.codigo_barras,
                    marca: data.marca,
                    categoria: data.categoria,
                    almacen: data.almacen,
                    stock: Number(data.stock),
                    foto: data.fotografia ? `{{ asset('storage') }}/${data.fotografia}` : ''
                });
            })
            .catch((error) => {
                console.error(error);
                setStatus('No se pudo consultar el código. Intenta otra vez.', 'error');
            });
    }

    function abrirEscaner() {
        document.getElementById('scannerModal').style.display = 'flex';

        html5QrcodeScanner = new Html5QrcodeScanner(
            'reader',
            { fps: 10, qrbox: { width: 250, height: 250 } },
            false
        );

        html5QrcodeScanner.render((textoDecodificado) => {
            codigoInput.value = textoDecodificado.trim();
            cerrarEscaner();
            consultarCodigo(textoDecodificado, true);
        }, () => {});

        observarTraduccionEscaner();
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

    codigoInput.addEventListener('input', () => {
        clearTimeout(busquedaTimer);
        busquedaTimer = setTimeout(() => {
            if (codigoInput.value.trim().length >= 4) {
                consultarCodigo(codigoInput.value);
            }
        }, 350);
    });

    codigoInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            consultarCodigo(codigoInput.value, true);
        }
    });

    cantidadInput.addEventListener('input', () => {
        const max = Number(cantidadInput.max || 0);

        if (max > 0 && Number(cantidadInput.value) > max) {
            cantidadHelp.textContent = `No puedes sacar más de ${max} pzas disponibles.`;
            cantidadHelp.style.color = '#fca5a5';
            return;
        }

        cantidadHelp.style.color = '';
    });

    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey || event.altKey || event.metaKey || document.activeElement === codigoInput) {
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
            codigoInput.value = codigo;
            consultarCodigo(codigo, true);
        }
    });
</script>

</body>
</html>