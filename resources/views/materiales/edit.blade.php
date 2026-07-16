<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Material - AppLugarth</title>

    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    
    <style>
        /* --- ESTILOS ULTRA-FUTURISTAS MASTER (Modo Edición) --- */
        :root {
            --bg: #030712; 
            --surface: rgba(15, 23, 42, 0.7); 
            --ink: #ffffff; 
            --muted: #94a3b8; 
            --cyan-glow: #06b6d4;
            --blue-glow: #3b82f6;
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
        
        /* AQUI ESTÁ EL CAMBIO CLAVE: display: block para evitar aplastamientos */
        .app-content { 
            flex: 1; 
            padding: 40px 20px; 
            overflow-y: auto; 
            display: block; 
        }

        /* --- CONTENEDOR DEL FORMULARIO --- */
        .container {
            width: 100%;
            max-width: 900px;
            /* Se centra automáticamente con margin auto y se deja espacio abajo */
            margin: 0 auto 60px auto; 
            background: var(--surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow-glass), inset 0 0 20px rgba(56, 189, 248, 0.05);
            padding: 40px;
            height: auto; /* Fuerza a que crezca con el contenido */
            display: block;
            overflow: visible;
        }

        .page-header { margin-bottom: 30px; border-bottom: 1px solid var(--line); padding-bottom: 20px; }

        .page-header h1 {
            margin: 0 0 8px 0;
            background: linear-gradient(to right, #00f2fe, #4facfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(0, 242, 254, 0.2); 
        }

        .page-header p { margin: 0; color: var(--muted); font-size: 14px; }

        /* --- ALERTAS --- */
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #fca5a5;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        /* --- FORMULARIOS --- */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .form-group { display: flex; flex-direction: column; margin-bottom: 24px; }
        .form-group.full { grid-column: span 2; }
        
        label { color: #38bdf8; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }

        input, select, textarea {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ffffff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--cyan-glow);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.3);
            background: rgba(0, 0, 0, 0.6);
        }

        select option {
            background-color: #0f172a;
            color: #ffffff;
        }

        /* --- BOTONES --- */
        .form-actions { display: flex; gap: 16px; margin-top: 32px; }

        .btn-save {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 16px 32px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 14px;
            cursor: pointer;
            flex: 1;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }

        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(37, 99, 235, 0.6); }

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            color: #94a3b8;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 14px 24px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-back:hover { border-color: #fff; color: #fff; }

        /* --- COMPONENTES ADICIONALES --- */
        .input-group { display: flex; gap: 12px; }
        .btn-scan {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 0 24px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
        }
        .code-status { margin-top: 10px; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 600; display: none; }
        .code-status.success { display: block; background: rgba(16, 185, 129, 0.15); color: #6ee7b7; border-left: 3px solid #10b981; }
        .code-status.error { display: block; background: rgba(239, 68, 68, 0.15); color: #f87171; border-left: 3px solid #ef4444; }
        .field-help { margin-top: 7px; color: var(--muted); font-size: 12px; line-height: 1.4; }
        .error { margin-top: 7px; color: #fca5a5; font-size: 12px; font-weight: 800; }
        .modal { display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.84); backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 1000; padding: 20px; }
        .modal-content { background: #0f172a; border: 1px solid rgba(6, 182, 212, 0.32); padding: 26px; border-radius: 18px; width: min(460px, 100%); box-shadow: var(--shadow-glass); }
        .modal-content h3 { text-align: center; margin: 0 0 14px; }
        .close-btn { width: 100%; margin-top: 16px; padding: 14px; border: none; border-radius: 10px; background: rgba(239, 68, 68, 0.14); color: #fca5a5; font-weight: 900; cursor: pointer; }
        #reader { width: 100%; min-height: 250px; border-radius: 12px; overflow: hidden; border: 2px dashed rgba(6, 182, 212, 0.5); }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full { grid-column: span 1; }
        }
    </style>
</head>
<body>

<div class="app-shell">
    @include('materiales.partials.sidebar')

    <main class="app-content">
        <div class="container">
            <div class="page-header">
                <h1>Editar Material</h1>
                <p>Actualiza datos, stock y código de barras. Puedes escanear con cámara o pistolita USB.</p>
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

                {{--
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="codigo_barras">Código de Barras</label>
                        <div class="input-group">
                            <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras', $material->codigo_barras) }}" placeholder="Escanea o escribe el código" autocomplete="off" autofocus>
                            <button type="button" class="btn-scan" onclick="abrirEscaner()">Escanear cámara</button>
                        </div>
                        <div class="field-help">Si el código pertenece a otro material, el sistema te avisará antes de guardar.</div>
                        <div id="codigo_status" class="code-status"></div>
                        @error('codigo_barras') <span class="error">{{ $message }}</span> @enderror
                    </div>
                --}}
            @endif

                <form action="{{ route('materiales.update', $material) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Código de Barras</label>
                            <div class="input-group">
                                <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras', $material->codigo_barras) }}">
                                <button type="button" class="btn-scan" onclick="abrirEscaner()">Escanear</button>
                            </div>
                            <div class="field-help">Si el codigo pertenece a otro material, el sistema te avisara antes de guardar.</div>
                            <div id="codigo_status" class="code-status"></div>
                            @error('codigo_barras') <span class="error">{{ $message }}</span> @enderror
                        </div>

                    <div class="form-group full">
                        <label for="descripcion">Descripción *</label>
                        <textarea name="descripcion" id="descripcion" required>{{ old('descripcion', $material->descripcion) }}</textarea>
                        @error('descripcion') <span class="error">{{ $message }}</span> @enderror
                    </div>

                        <div class="form-group">
                            <label>Apodo / nombre comun</label>
                            <input type="text" name="apodo" value="{{ old('apodo', $material->apodo) }}" placeholder="Como lo conocen en almacen">
                            <div class="field-help">Tambien se usa en el buscador.</div>
                        </div>

                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="categoria">
                                <option value="">Sin categoria</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat }}" {{ old('categoria', $material->categoria) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()?->puedeAdministrarCatalogo())
                                <div class="field-help">Puedes crear mas categorias en Administracion > Categorias.</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>No. de Parte</label>
                            <input type="text" name="numero_parte" value="{{ old('numero_parte', $material->numero_parte) }}">
                        </div>

                        <div class="form-group">
                            <label>Almacen</label>
                            <input type="text" name="almacen" value="{{ old('almacen', $material->almacen) }}">
                        </div>

                        <div class="form-group">
                            <label>Marca</label>
                            <input type="text" name="marca" value="{{ old('marca', $material->marca) }}">
                        </div>

                        <div class="form-group">
                            <label>Proveedor</label>
                            <input type="text" name="proveedor" value="{{ old('proveedor', $material->proveedor) }}">
                        </div>

                        <div class="form-group">
                            <label>RFC proveedor</label>
                            <input type="text" name="proveedor_rfc" value="{{ old('proveedor_rfc', $material->proveedor_rfc) }}">
                        </div>

                        <div class="form-group">
                            <label>Clave SAT</label>
                            <input type="text" name="clave_sat" value="{{ old('clave_sat', $material->clave_sat) }}">
                        </div>

                        <div class="form-group">
                            <label>Unidad</label>
                            <input type="text" name="unidad" value="{{ old('unidad', $material->unidad) }}">
                        </div>

                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" name="stock" id="stock" value="{{ old('stock', $material->stock) }}" min="0">
                        <div class="field-help">Escribe el stock total actual del material, no la cantidad a sumar.</div>
                        @error('stock') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="stock_minimo">Stock minimo</label>
                        <input type="number" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', $material->stock_minimo) }}" min="0">
                        <div class="field-help">El sistema lo marcara en rojo cuando el stock llegue a este numero.</div>
                        @error('stock_minimo') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="stock_maximo">Stock maximo</label>
                        <input type="number" name="stock_maximo" id="stock_maximo" value="{{ old('stock_maximo', $material->stock_maximo) }}" min="0">
                        <div class="field-help">Ayuda a detectar exceso de inventario.</div>
                        @error('stock_maximo') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="costo_unitario">Costo unitario</label>
                        <input type="number" name="costo_unitario" id="costo_unitario" value="{{ old('costo_unitario', $material->costo_unitario) }}" min="0" step="0.01">
                        <div class="field-help">Se usa para calcular valor de inventario y reportes.</div>
                        @error('costo_unitario') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
    <label>Fotografía</label>
    
    @if($material->fotografia)
        <div style="margin-bottom: 15px; padding: 12px; background: rgba(0,0,0,0.4); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 12px; display: flex; align-items: center; gap: 15px; width: max-content;">
            <img src="{{ asset('storage/' . $material->fotografia) }}" alt="Foto actual" style="width: 90px; height: 90px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1);">
            <div style="display: flex; flex-direction: column;">
                <span style="color: #38bdf8; font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Imagen actual</span>
                <span style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Sube una nueva para reemplazarla</span>
            </div>
        </div>
    @endif

    <input type="file" name="fotografia" accept="image/*">
</div>

                <div class="form-actions">
                    <a href="{{ route('materiales.index') }}" class="btn-back">Cancelar</a>
                    <button type="submit" class="btn-save">Guardar Cambios</button>
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
    const materialId = {{ $material->id }};
    const codigoInput = document.getElementById('codigo_barras');
    const codigoStatus = document.getElementById('codigo_status');
    let html5QrcodeScanner = null;
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
            codigoInput.value = textoDecodificado.trim();
            cerrarEscaner();
            validarCodigoEscaneado();
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

    function setCodigoStatus(mensaje, tipo) {
        codigoStatus.textContent = mensaje;
        codigoStatus.className = `code-status ${tipo}`;
    }

    function validarCodigoEscaneado() {
        const codigo = codigoInput.value.trim();

        if (!codigo) {
            codigoStatus.className = 'code-status';
            codigoStatus.textContent = '';
            return;
        }

        fetch(`{{ route('materiales.buscarPorCodigo') }}?codigo=${encodeURIComponent(codigo)}`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.encontrado) {
                    setCodigoStatus('Código disponible para este material.', 'success');
                    return;
                }

                if (Number(data.id) === Number(materialId)) {
                    setCodigoStatus('Este código ya pertenece a este material.', 'success');
                    return;
                }

                setCodigoStatus(`Cuidado: este código ya pertenece a "${data.descripcion}".`, 'error');
            })
            .catch(() => {
                setCodigoStatus('No se pudo validar el código ahora. Laravel lo revisará al guardar.', 'warning');
            });
    }

    codigoInput.addEventListener('blur', validarCodigoEscaneado);

    codigoInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            validarCodigoEscaneado();
        }
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
            codigoInput.value = scannerBuffer;
            scannerBuffer = '';
            validarCodigoEscaneado();
            codigoInput.focus();
        }
    });
</script>

</body>
</html>
