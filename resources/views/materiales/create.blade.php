<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Material - Inventario</title>

    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    
    <style>
        /* --- ESTILOS ULTRA-FUTURISTAS MASTER --- */
        :root {
            --bg: #030712; 
            --surface: rgba(15, 23, 42, 0.6); 
            --ink: #ffffff; 
            --muted: #94a3b8; 
            --cyan-glow: #06b6d4;
            --blue-glow: #3b82f6;
            --emerald-glow: #10b981;
            --orange-glow: #f97316;
            --red-glow: #ef4444;
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
            box-shadow: var(--shadow-glass), inset 0 0 20px rgba(56, 189, 248, 0.05);
            padding: 40px;
        }

        .form-header {
            margin-bottom: 30px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 20px;
        }

        .form-header h2 {
            margin: 0 0 8px 0;
            background: linear-gradient(to right, #00f2fe, #4facfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(0, 242, 254, 0.2); 
        }

        .header-meta {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        /* --- ALERTAS --- */
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--red-glow);
            color: #fca5a5;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            box-shadow: inset 0 0 15px rgba(239, 68, 68, 0.1);
        }
        .alert-danger ul { margin: 8px 0 0 0; padding-left: 20px; }
        .field-error {
            margin-top: 8px;
            color: #fca5a5;
            font-size: 12px;
            font-weight: 800;
        }
        .field-help {
            margin-top: 7px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.35;
        }

        .scan-status {
            margin-top: 10px;
            font-size: 13px;
            font-weight: 600;
            padding: 12px 16px;
            border-radius: 10px;
            display: none; /* Oculto por defecto, se muestra por JS */
        }
        .scan-status:not(:empty) { display: block; }
        .scan-status.info { background: rgba(56, 189, 248, 0.1); color: #7dd3fc; border-left: 3px solid var(--cyan-glow); }
        .scan-status.success { background: rgba(16, 185, 129, 0.1); color: #6ee7b7; border-left: 3px solid var(--emerald-glow); }
        .scan-status.warning { background: rgba(245, 158, 11, 0.1); color: #fcd34d; border-left: 3px solid var(--orange-glow); }
        .scan-status.error { background: rgba(239, 68, 68, 0.1); color: #fca5a5; border-left: 3px solid var(--red-glow); }

        /* --- ESTRUCTURA GRID DEL FORMULARIO --- */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full { grid-column: span 2; }
        
        .input-group {
            display: flex;
            gap: 12px;
        }
        .input-group input { flex: 1; }

        /* --- ESTILOS DE INPUTS --- */
        label {
            color: #38bdf8; 
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        input[type="text"], 
        input[type="number"], 
        input[type="file"], 
        select, 
        textarea {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 14px;
            color: #ffffff;
            font-family: inherit;
            transition: all 0.3s ease;
            outline: none;
        }

        input::placeholder, textarea::placeholder { color: #64748b; }

        input:focus, select:focus, textarea:focus {
            border-color: var(--cyan-glow);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.3);
            background: rgba(0, 0, 0, 0.6);
        }

        input:disabled, select:disabled, textarea:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        select option {
            background-color: #0f172a;
            color: #ffffff;
        }

        textarea { resize: vertical; min-height: 100px; }

        /* --- BOTONES --- */
        .btn-scan {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 0 24px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            white-space: nowrap;
        }

        .btn-scan:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.5);
            filter: brightness(1.1);
        }

        .form-actions {
            display: flex;
            gap: 16px;
            margin-top: 32px;
            align-items: center;
        }

        .btn-submit {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 16px 32px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
            flex: 1; /* Ocupa el espacio disponible */
            text-align: center;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.6);
            filter: brightness(1.1);
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: transparent;
            color: var(--muted);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 14px 24px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-cancel:hover {
            border-color: #ffffff;
            color: #ffffff;
        }

        /* --- MODAL --- */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(7, 14, 23, 0.85);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-content {
            background-color: #0f172a;
            border: 1px solid rgba(56, 189, 248, 0.2);
            padding: 24px;
            border-radius: 16px;
            width: min(500px, 100%);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.6), 0 0 20px rgba(56, 189, 248, 0.1);
        }

        .modal-content h3 { margin: 0 0 16px; color: #ffffff; text-align: center;}
        
        .close-btn {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            padding: 12px 18px;
            width: 100%;
            margin-top: 16px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .close-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.2);
            color: #fff;
        }

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

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.3); }
        ::-webkit-scrollbar-thumb { background: rgba(56, 189, 248, 0.5); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(56, 189, 248, 0.8); }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; gap: 16px; }
            .form-group.full { grid-column: span 1; }
            .input-group { flex-direction: column; }
            .btn-scan { padding: 14px; width: 100%; }
            .form-actions { flex-direction: column; }
            .btn-submit, .btn-cancel { width: 100%; }
        }
    </style>
</head>
<body>

<div class="app-shell">
@include('materiales.partials.sidebar')

<main class="app-content">
<div class="container">
    <div class="form-header">
        <h2>Entrada de Material</h2>
        <p class="header-meta">Cámara o escáner USB, el mismo código manda.</p>
    </div>

    @if ($errors->any())
        <div class="alert-danger">
            <strong>Revisa estos datos:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('materiales.store') }}" method="POST" enctype="multipart/form-data" id="materialForm">
        @csrf

        <div class="form-grid">
            <div class="form-group full">
                <label for="codigo_barras">Código de Barras / SKU</label>
                <div class="input-group">
                    <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras') }}" placeholder="Escanea o escribe el código" autocomplete="off" autofocus>
                    <button type="button" class="btn-scan" onclick="abrirEscaner()">Escanear cámara</button>
                </div>
                <div class="field-help">Puedes escanear con cámara, pistola USB o escribirlo manualmente.</div>
                <div id="codigo_status" class="scan-status"></div>
                @error('codigo_barras') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="categoria">Categoria / Tipo de Equipo *</label>
                <select name="categoria" id="categoria" required data-material-field>
                    <option value="">-- Selecciona una categoria --</option>
                    @foreach([
                        'EQUIPO ACERO AL CARBON',
                        'EQUIPO ACERO INOXIDABLE',
                        'EQUIPO TIPO ASA INOXIDABLE',
                        'EQUIPO AC SIST DSPCH MEC FILL',
                        'EQUIPO AC SIST DSPCH MEC LIQUID',
                        'EQUIPO ACERO AL CARBON UPV',
                    ] as $categoria)
                        <option value="{{ $categoria }}" {{ old('categoria') === $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
                    @endforeach
                </select>
                @error('categoria') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="numero_parte">No. de Parte / Código</label>
                <input type="text" name="numero_parte" id="numero_parte" value="{{ old('numero_parte') }}" placeholder="Ej. 3176MS" data-material-field>
                @error('numero_parte') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group full">
                <label for="descripcion">Descripción del Material *</label>
                <textarea name="descripcion" id="descripcion" placeholder="Detalles del componente" required data-material-field>{{ old('descripcion') }}</textarea>
                @error('descripcion') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="marca">Marca</label>
                <input type="text" name="marca" id="marca" value="{{ old('marca') }}" placeholder="Ej. BETTS" data-material-field>
                @error('marca') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="proveedor">Proveedor</label>
                <input type="text" name="proveedor" id="proveedor" value="{{ old('proveedor') }}" placeholder="Ej. Promotora Industrial RG" data-material-field>
                @error('proveedor') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="stock">Cantidad de Entrada *</label>
                <input type="number" name="stock" id="stock" value="{{ old('stock') }}" placeholder="0" min="0" required>
                <div class="field-help">Escribe solo números enteros. Ejemplo: 1, 5, 20.</div>
                @error('stock') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="stock_minimo">Stock mínimo</label>
                <input type="number" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', 0) }}" placeholder="0" min="0" data-material-field>
                <div class="field-help">Cuando el stock llegue a este número, el inventario lo marcará en rojo.</div>
                @error('stock_minimo') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="costo_unitario">Costo unitario</label>
                <input type="number" name="costo_unitario" id="costo_unitario" value="{{ old('costo_unitario', 0) }}" placeholder="0.00" min="0" step="0.01" data-material-field>
                <div class="field-help">Sirve para calcular el valor del inventario en el dashboard y reportes.</div>
                @error('costo_unitario') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="fotografia">Fotografía</label>
                <input type="file" name="fotografia" id="fotografia" accept="image/*" data-material-field>
                <div class="field-help">Formatos permitidos: JPG, PNG o WEBP. Máximo 2 MB.</div>
                @error('fotografia') <div class="field-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit" id="submitButton">Guardar Material en Inventario</button>
            <a href="{{ route('materiales.index') }}" class="btn-cancel">Cancelar y regresar al listado</a>
        </div>
    </form>
</div>

</main>
</div>

<div id="scannerModal" class="modal">
    <div class="modal-content">
        <h3>Escanear Código de Barras</h3>
        <div id="reader"></div>
        <button type="button" class="close-btn" onclick="cerrarEscaner()">Cancelar</button>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const codigoBarrasInput = document.getElementById('codigo_barras');
    const stockInput = document.getElementById('stock');
    const submitButton = document.getElementById('submitButton');
    const materialFields = document.querySelectorAll('[data-material-field]');
    let html5QrcodeScanner = null;
    let ultimoCodigoConsultado = '';
    let busquedaTimer = null;
    let scannerBuffer = '';
    let scannerBufferInicio = 0;
    let scannerUltimaTecla = 0;
    let scannerResetTimer = null;

    function abrirEscaner() {
        document.getElementById('scannerModal').style.display = 'flex';

        html5QrcodeScanner = new Html5QrcodeScanner(
            'reader',
            { fps: 10, qrbox: { width: 250, height: 250 } },
            false
        );

        html5QrcodeScanner.render((textoDecodificado) => {
            codigoBarrasInput.value = textoDecodificado;
            cerrarEscaner();
            consultarCodigoLocal(textoDecodificado, true);
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

    function setEstadoCodigo(mensaje, tipo) {
        const estado = document.getElementById('codigo_status');
        estado.textContent = mensaje;
        estado.className = `scan-status ${tipo}`;
    }

    function bloquearCamposMaterial(bloquear) {
        materialFields.forEach((field) => {
            field.disabled = bloquear;
        });
    }

    function limpiarDatosMaterial() {
        document.getElementById('categoria').value = '';
        document.getElementById('numero_parte').value = '';
        document.getElementById('descripcion').value = '';
        document.getElementById('marca').value = '';
        document.getElementById('proveedor').value = '';
    }

    function cargarDatosMaterial(data) {
        document.getElementById('categoria').value = data.categoria || '';
        document.getElementById('numero_parte').value = data.numero_parte || '';
        document.getElementById('descripcion').value = data.descripcion || '';
        document.getElementById('marca').value = data.marca || '';
        document.getElementById('proveedor').value = data.proveedor || '';
        stockInput.value = '';
        stockInput.placeholder = 'Cantidad nueva a sumar';
        bloquearCamposMaterial(true);
        submitButton.textContent = 'Registrar Entrada al Stock';
        setEstadoCodigo(`Código ya registrado: ${data.descripcion}. Stock actual: ${data.stock} pzas. Al guardar se sumará la cantidad capturada.`, 'success');
        stockInput.focus();
    }

    function prepararMaterialNuevo() {
        bloquearCamposMaterial(false);
        limpiarDatosMaterial();
        stockInput.value = '';
        stockInput.placeholder = 'Cantidad inicial';
        submitButton.textContent = 'Guardar Material en Inventario';
        setEstadoCodigo('Código nuevo detectado. Captura los datos para registrarlo por primera vez.', 'warning');
        document.getElementById('categoria').focus();
    }

    function consultarCodigoLocal(codigo, forzar = false) {
        const codigoLimpio = codigo.trim();

        if (!codigoLimpio) {
            return;
        }

        if (!forzar && codigoLimpio === ultimoCodigoConsultado) {
            return;
        }

        ultimoCodigoConsultado = codigoLimpio;
        codigoBarrasInput.value = codigoLimpio;
        setEstadoCodigo('Buscando código en inventario...', 'info');

        fetch(`{{ route('materiales.buscarPorCodigo') }}?codigo=${encodeURIComponent(codigoLimpio)}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error('No se pudo consultar el código.');
                }

                return response.json();
            })
            .then((data) => {
                if (data.encontrado) {
                    cargarDatosMaterial(data);
                    return;
                }

                prepararMaterialNuevo();
            })
            .catch((error) => {
                console.error('Error al consultar el código:', error);
                ultimoCodigoConsultado = '';
                bloquearCamposMaterial(false);
                setEstadoCodigo('No se pudo consultar el código. Intenta otra vez.', 'error');
            });
    }

    function programarBusquedaPorEscritura() {
        clearTimeout(busquedaTimer);

        busquedaTimer = setTimeout(() => {
            const codigo = codigoBarrasInput.value.trim();

            if (codigo.length >= 4) {
                consultarCodigoLocal(codigo);
            }
        }, 350);
    }

    function limpiarCodigoDeCampoActivo(codigo) {
        const active = document.activeElement;

        if (!active || active === codigoBarrasInput || typeof active.value !== 'string') {
            return;
        }

        if (active.value.endsWith(codigo)) {
            active.value = active.value.slice(0, -codigo.length);
        }
    }

    codigoBarrasInput.addEventListener('input', programarBusquedaPorEscritura);

    codigoBarrasInput.addEventListener('blur', () => {
        consultarCodigoLocal(codigoBarrasInput.value);
    });

    codigoBarrasInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            consultarCodigoLocal(codigoBarrasInput.value, true);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey || event.altKey || event.metaKey || document.activeElement === codigoBarrasInput) {
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
            limpiarCodigoDeCampoActivo(codigo);
            codigoBarrasInput.value = codigo;
            consultarCodigoLocal(codigo, true);
        }
    });
</script>

</body>
</html>
