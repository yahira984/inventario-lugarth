<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar entrada - Inventario Lugarth</title>
    
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    
    <style>
        body { margin: 0; min-height: 100vh; font-family: "Segoe UI", Tahoma, sans-serif; background: #f6f8fb; color: #102033; }
        .app-shell { display: flex; min-height: 100vh; }
        .app-content { flex: 1; padding: 32px 18px; overflow-x: hidden; }
        .container { max-width: 1180px; margin: 0 auto; }
        .page-header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-end; margin-bottom: 18px; padding: 24px; background: #fff; border: 1px solid #dbe5f0; border-radius: 18px; box-shadow: 0 16px 40px rgba(15,23,42,.08); }
        h1 { margin: 0 0 6px; font-size: clamp(28px, 4vw, 40px); line-height: 1.05; letter-spacing: 0; color: #0f2742; }
        .meta { color: #64748b; font-size: 14px; font-weight: 700; }
        .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn, button { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid #1d4ed8; background: #2563eb; color: #fff; padding: 0 14px; font-family: inherit; font-weight: 900; text-decoration: none; cursor: pointer; transition: transform .16s ease, background .16s ease, box-shadow .16s ease; }
        .btn:hover, button:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 10px 22px rgba(37,99,235,.18); }
        .btn-soft { background: #fff; color: #1d4ed8; border-color: #bfdbfe; box-shadow: none; }
        .btn-soft:hover { background: #eff6ff; color: #0f3f88; }
        .btn-danger { background: #dc2626; border-color: #b91c1c; }
        .btn-danger:hover { background: #b91c1c; }
        .form-shell { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(320px, .65fr); gap: 18px; align-items: start; }
        .card { background: #fff; border: 1px solid #dbe5f0; border-radius: 18px; padding: 22px; box-shadow: 0 16px 40px rgba(15,23,42,.08); }
        .card + .card { margin-top: 18px; }
        .section-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 14px; margin-bottom: 18px; border-bottom: 1px solid #e2e8f0; }
        .section-title h2 { margin: 0; font-size: 20px; color: #0f2742; }
        .section-title span { color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .field { min-width: 0; }
        .field.full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 7px; color: #334155; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; }
        input, select, textarea { width: 100%; min-height: 46px; border: 1px solid #b8c8dc; border-radius: 11px; background: #fff; color: #102033; padding: 11px 13px; font-family: inherit; font-size: 15px; outline: none; box-sizing: border-box; }
        textarea { min-height: 112px; resize: vertical; line-height: 1.45; }
        input:focus, select:focus, textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,.13); }
        input::placeholder, textarea::placeholder { color: #64748b; opacity: 1; }
        .input-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; }
        .help { margin-top: 7px; color: #64748b; font-size: 12px; line-height: 1.45; font-weight: 600; }
        .alert-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 14px; padding: 14px 16px; margin-bottom: 18px; font-weight: 800; }
        .alert-danger ul { margin: 8px 0 0; padding-left: 18px; }
        .upload-box { border: 1px dashed #b8c8dc; border-radius: 14px; padding: 16px; background: #f8fafc; }
        .upload-box input[type="file"] { border-style: dashed; background: #fff; }
        .preview { display: none; width: 100%; max-width: 260px; aspect-ratio: 4 / 3; object-fit: cover; margin-top: 14px; border-radius: 12px; border: 1px solid #bfdbfe; box-shadow: 0 10px 24px rgba(15,23,42,.1); }
        .side-note { display: grid; gap: 12px; color: #475569; font-size: 13px; line-height: 1.5; }
        .note { padding: 14px; border-radius: 14px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .note strong { display: block; color: #0f2742; margin-bottom: 4px; }
        .actions { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; margin-top: 18px; }
        .modal { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, .72); align-items: center; justify-content: center; z-index: 1800; padding: 20px; backdrop-filter: blur(6px); }
        .modal-content { width: min(520px, 100%); background: #fff; border: 1px solid #dbe5f0; border-radius: 18px; padding: 24px; box-shadow: 0 24px 70px rgba(15,23,42,.28); text-align: center; }
        .modal-content h3 { margin: 0 0 14px; color: #0f2742; }
        #reader { width: 100%; min-height: 260px; border: 2px dashed #bfdbfe; border-radius: 14px; overflow: hidden; background: #f8fafc; }
        #videoElement { width: 100%; max-height: 360px; background: #020617; border-radius: 14px; object-fit: cover; }
        @media (max-width: 980px) { .app-content { padding-top: 76px; } .page-header { display: block; } .header-actions { margin-top: 14px; } .form-shell { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .app-content { padding: 76px 10px 18px; } .page-header, .card { padding: 18px; border-radius: 16px; } .grid, .actions, .input-row { grid-template-columns: 1fr; } .btn, button { width: 100%; } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')

    <main class="app-content">
        <div class="container">
            <header class="page-header">
                <div>
                    <h1>Registrar entrada</h1>
                    <div class="meta">Alta de material nuevo o entrada de stock usando codigo existente.</div>
                </div>
                <div class="header-actions">
                    @if(auth()->user()?->puedeAdministrarCatalogo())
                        <a href="{{ route('admin.categorias.index') }}" class="btn" style="background: #7c3aed !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;">Categorias</a>
                    @endif
                    <a href="{{ route('materiales.index') }}" class="btn" style="background: #202e42 !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;">Volver al inventario</a>
                </div>
            </header>

            @if ($errors->any())
                <div class="alert-danger">
                    <strong>Revisa estos datos antes de guardar:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('materiales.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-shell">
                    <div>
                        <section class="card">
                            <div class="section-title">
                                <h2>Identificacion</h2>
                                <span>Codigo y datos base</span>
                            </div>

                            <div class="grid">
                                <div class="field full">
                                    <label for="codigo_barras">Codigo de barras / SKU</label>
                                    <div class="input-row">
                                        <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras') }}" placeholder="Escanea con pistolita USB o escribe el codigo" autocomplete="off" autofocus>
                                        <button type="button" class="btn" onclick="abrirEscaner()" style="background: #e4c309 !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;">Escanear</button>
                                    </div>
                                    <div class="help">Si el codigo ya existe, el sistema llenara los datos y al guardar sumara la cantidad al stock.</div>
                                </div>

                                <div class="field">
                                    <label for="categoria">Categoria</label>
                                    <select name="categoria" id="categoria">
                                        <option value="">Sin categoria</option>
                                        @foreach($categorias as $categoria)
                                            <option value="{{ $categoria }}" {{ old('categoria') === $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="numero_parte">No. de parte</label>
                                    <input type="text" name="numero_parte" id="numero_parte" value="{{ old('numero_parte') }}" placeholder="Ej. 3176MS">
                                </div>

                                <div class="field full">
                                    <label for="descripcion">Descripcion del material *</label>
                                    <textarea name="descripcion" id="descripcion" placeholder="Nombre claro del producto, medida, material o uso" required>{{ old('descripcion') }}</textarea>
                                </div>

                                <div class="field">
                                    <label for="marca">Marca</label>
                                    <input type="text" name="marca" id="marca" value="{{ old('marca') }}" placeholder="Ej. BETTS">
                                </div>

                                <div class="field">
                                    <label for="unidad">Unidad</label>
                                    <input type="text" name="unidad" id="unidad" value="{{ old('unidad') }}" placeholder="Pieza, metro, juego...">
                                </div>
                            </div>
                        </section>

                        <section class="card">
                            <div class="section-title">
                                <h2>Almacen y stock</h2>
                                <span>Existencias</span>
                            </div>

                            <div class="grid">
                                <div class="field full">
                                    <label for="almacen">Almacen donde se guarda</label>
                                    <input type="text" name="almacen" id="almacen" value="{{ old('almacen') }}" placeholder="Ej. Almacen principal, rack A, caja 3">
                                </div>

                                <div class="field">
                                    <label for="stock">Cantidad de entrada</label>
                                    <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}" min="0">
                                </div>

                                <div class="field">
                                    <label for="costo_unitario">Precio por unidad</label>
                                    <input type="number" name="costo_unitario" id="costo_unitario" value="{{ old('costo_unitario', 0) }}" min="0" step="0.01">
                                </div>

                                <div class="field">
                                    <label for="stock_minimo">Stock minimo</label>
                                    <input type="number" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', 0) }}" min="0">
                                </div>

                                <div class="field">
                                    <label for="stock_maximo">Stock maximo</label>
                                    <input type="number" name="stock_maximo" id="stock_maximo" value="{{ old('stock_maximo', 0) }}" min="0">
                                </div>
                            </div>
                        </section>

                        <section class="card">
                            <div class="section-title">
                                <h2>Proveedor y SAT</h2>
                                <span>Datos administrativos</span>
                            </div>

                            <div class="grid">
                                <div class="field">
                                    <label for="proveedor">Proveedor</label>
                                    <input type="text" name="proveedor" id="proveedor" value="{{ old('proveedor') }}" placeholder="Ej. Promotora Industrial RG">
                                </div>

                                <div class="field">
                                    <label for="proveedor_rfc">RFC proveedor</label>
                                    <input type="text" name="proveedor_rfc" id="proveedor_rfc" value="{{ old('proveedor_rfc') }}" placeholder="RFC si viene de factura">
                                </div>

                                <div class="field">
                                    <label for="clave_sat">Clave SAT</label>
                                    <input type="text" name="clave_sat" id="clave_sat" value="{{ old('clave_sat') }}" placeholder="ClaveProdServ">
                                </div>

                                <div class="field">
                                    <label for="clave_unidad">Clave unidad SAT</label>
                                    <input type="text" name="clave_unidad" id="clave_unidad" value="{{ old('clave_unidad') }}" placeholder="Ej. H87">
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside>
                        <section class="card">
                            <div class="section-title">
                                <h2>Fotografias</h2>
                                <span>Evidencia</span>
                            </div>

                            <div class="side-note">
                                <div class="upload-box">
                                    <label for="fotografia">Foto del producto</label>
                                    <input type="file" name="fotografia" id="fotografia" accept="image/*" onchange="mostrarVistaPreviaArchivo(this, 'previewProducto')">
                                    <img id="previewProducto" class="preview" alt="Vista previa del producto">
                                </div>

                                <div class="upload-box">
                                    <label for="evidencia_foto">Evidencia de recepcion</label>
                                    <button type="button" class="btn" onclick="abrirCamaraWeb()" style="background: #dfee0a !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;">Tomar foto</button>
                                    <div class="help">Tambien puedes subir nota, remision, caja o etiqueta del proveedor.</div>
                                    <input type="file" name="evidencia_foto" id="evidencia_foto" accept="image/*" onchange="mostrarVistaPreviaArchivo(this, 'previewEvidencia')" style="margin-top: 10px;">
                                    <img id="previewEvidencia" class="preview" alt="Vista previa de evidencia">
                                </div>
                            </div>
                        </section>

                        <section class="card">
                            <div class="section-title">
                                <h2>Antes de guardar</h2>
                                <span>Guia rapida</span>
                            </div>
                            <div class="side-note">
                                <div class="note"><strong>Producto nuevo</strong>Solo la descripcion es obligatoria; lo demas se puede completar despues.</div>
                                <div class="note"><strong>Codigo existente</strong>Escanea el codigo, escribe la cantidad y guarda para sumar stock.</div>
                                <div class="note"><strong>Sin codigo fisico</strong>Despues puedes generar QR interno desde inventario.</div>
                            </div>
                        </section>

                        <div class="actions">
                            <button type="submit" class="btn" style="background: #16a34a !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;">Guardar entrada</button>
                            <a href="{{ route('materiales.index') }}" class="btn" style="background: #b60e1c !important; color: #e7ccce !important; border: 2px solid #cbd5e1 !important; box-shadow: none !important;">Cancelar</a>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
    </main>
</div>

<div id="scannerModal" class="modal">
    <div class="modal-content">
        <h3>Escanear codigo de barras</h3>
        <div id="reader"></div>
        <button type="button" class="btn" style="width:100%; margin-top:16px; background: #b91c1c !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;" onclick="cerrarEscaner()">Cancelar</button>
    </div>
</div>

<div id="camaraModal" class="modal">
    <div class="modal-content">
        <h3>Capturar evidencia</h3>
        <video id="videoElement" autoplay playsinline></video>
        <canvas id="canvasElement" style="display:none;"></canvas>
        <button type="button" class="btn" style="width:100%; margin-top:14px; background: #16a34a !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;" onclick="tomarFotoWeb()">Capturar imagen</button>
        <button type="button" class="btn" style="width:100%; margin-top:10px; background: #b91c1c !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;" onclick="cerrarCamaraWeb()">Cancelar</button>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrcodeScanner = null;
    let streamVideo = null;

    function mostrarVistaPreviaArchivo(input, previewId) {
        const preview = document.getElementById(previewId);

        if (!input.files || !input.files[0]) {
            preview.style.display = 'none';
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            preview.src = event.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }

    function abrirEscaner() {
        document.getElementById('scannerModal').style.display = 'flex';
        html5QrcodeScanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: { width: 250, height: 250 } }, false);
        html5QrcodeScanner.render((textoDecodificado) => {
            document.getElementById('codigo_barras').value = textoDecodificado.trim();
            cerrarEscaner();
            consultarCodigoLocal(textoDecodificado);
        }, () => {});
    }

    function cerrarEscaner() {
        document.getElementById('scannerModal').style.display = 'none';

        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
            html5QrcodeScanner = null;
        }
    }

    function consultarCodigoLocal(codigo) {
        fetch(`{{ route('materiales.buscarPorCodigo') }}?codigo=${encodeURIComponent(codigo.trim())}`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.encontrado) {
                    return;
                }

                document.getElementById('categoria').value = data.categoria || '';
                document.getElementById('numero_parte').value = data.numero_parte || '';
                document.getElementById('descripcion').value = data.descripcion || '';
                document.getElementById('marca').value = data.marca || '';
                document.getElementById('proveedor').value = data.proveedor || '';
                document.getElementById('almacen').value = data.almacen || '';
                document.getElementById('stock').focus();
                alert('Material identificado. Escribe cuantas piezas entraron para sumar stock.');
            })
            .catch((error) => console.error('Error al consultar codigo:', error));
    }

    function abrirCamaraWeb() {
        const video = document.getElementById('videoElement');
        document.getElementById('camaraModal').style.display = 'flex';

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then((stream) => {
                streamVideo = stream;
                video.srcObject = stream;
            })
            .catch(() => {
                alert('No se pudo acceder a la camara. Revisa permisos del navegador.');
                cerrarCamaraWeb();
            });
    }

    function cerrarCamaraWeb() {
        document.getElementById('camaraModal').style.display = 'none';

        if (streamVideo) {
            streamVideo.getTracks().forEach((track) => track.stop());
            streamVideo = null;
        }
    }

    function tomarFotoWeb() {
        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('canvasElement');
        const context = canvas.getContext('2d');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            const file = new File([blob], `evidencia_${Date.now()}.jpg`, { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);

            const input = document.getElementById('evidencia_foto');
            input.files = dataTransfer.files;
            mostrarVistaPreviaArchivo(input, 'previewEvidencia');
            cerrarCamaraWeb();
        }, 'image/jpeg', 0.9);
    }
</script>
</body>
</html>