<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Material - Inventario</title>
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
        }

        .container {
            width: min(860px, 100%);
            margin: 0 auto;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .page-header {
            padding: 24px 30px;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
        }

        h1 {
            margin: 0;
            color: var(--blue-dark);
            font-size: 26px;
            line-height: 1.2;
        }

        .page-header p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .alert-danger {
            margin: 24px 30px 0;
            background-color: #fff1f0;
            color: #842029;
            padding: 14px 16px;
            border-radius: 6px;
            border: 1px solid #f2b8b5;
            font-weight: 700;
        }

        .alert-danger ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        form.material-form {
            padding: 28px 30px 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px 20px;
        }

        .form-group {
            min-width: 0;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--ink);
            font-size: 14px;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            min-height: 46px;
            padding: 11px 12px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fff;
            color: var(--ink);
            font-family: inherit;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 168, 0.16);
        }

        textarea {
            min-height: 112px;
            resize: vertical;
        }

        .input-group {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: stretch;
        }

        .btn-scan,
        .btn-save,
        .btn-back,
        .close-btn {
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 800;
            font-family: inherit;
            text-decoration: none;
            transition: background 0.2s, box-shadow 0.2s;
        }

        .btn-scan {
            background-color: var(--amber);
            color: #fff;
            padding: 0 18px;
            white-space: nowrap;
        }

        .btn-scan:hover {
            background-color: #a8620c;
            box-shadow: 0 8px 18px rgba(199, 121, 16, 0.22);
        }

        .code-status {
            display: none;
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
        }

        .code-status.success {
            display: block;
            background: #eaf8f0;
            color: #0f6b3e;
            border: 1px solid #a9dfbf;
        }

        .code-status.warning {
            display: block;
            background: #fff7e6;
            color: #8a5700;
            border: 1px solid #ffd98a;
        }

        .code-status.error {
            display: block;
            background: #fff1f0;
            color: #842029;
            border: 1px solid #f2b8b5;
        }

        .foto-actual {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 13px;
        }

        .foto-actual img {
            width: 74px;
            height: 74px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--line);
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-save {
            flex: 1;
            min-height: 48px;
            background-color: var(--blue);
            color: #fff;
            font-size: 16px;
        }

        .btn-save:hover {
            background-color: var(--blue-dark);
            box-shadow: 0 10px 24px rgba(37, 99, 168, 0.22);
        }

        .btn-back {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 18px;
            background-color: #e6ecf2;
            color: var(--ink);
        }

        .btn-back:hover {
            background-color: #d5dee8;
        }

        .error {
            display: block;
            margin-top: 6px;
            color: var(--red);
            font-size: 13px;
            font-weight: 700;
        }

        .field-help {
            margin-top: 7px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.35;
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

        @media (max-width: 680px) {
            .page-header,
            form.material-form {
                padding-left: 18px;
                padding-right: 18px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .input-group,
            .form-actions {
                grid-template-columns: 1fr;
                flex-direction: column;
            }

            .btn-scan {
                min-height: 44px;
            }
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
            @endif

            <form action="{{ route('materiales.update', $material) }}" method="POST" enctype="multipart/form-data" class="material-form">
                @csrf
                @method('PUT')

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

                    <div class="form-group full">
                        <label for="descripcion">Descripción *</label>
                        <textarea name="descripcion" id="descripcion" required>{{ old('descripcion', $material->descripcion) }}</textarea>
                        @error('descripcion') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="categoria">Categoria *</label>
                        <select name="categoria" id="categoria" required>
                            @foreach([
                                'IMPORTADO XML',
                                'EQUIPO ACERO AL CARBON',
                                'EQUIPO ACERO INOXIDABLE',
                                'EQUIPO TIPO ASA INOXIDABLE',
                                'EQUIPO AC SIST DSPCH MEC FILL',
                                'EQUIPO AC SIST DSPCH MEC LIQUID',
                                'EQUIPO ACERO AL CARBON UPV',
                            ] as $cat)
                                <option value="{{ $cat }}" {{ old('categoria', $material->categoria) === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                        @error('categoria') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="numero_parte">No. de Parte</label>
                        <input type="text" name="numero_parte" id="numero_parte" value="{{ old('numero_parte', $material->numero_parte) }}">
                        @error('numero_parte') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="marca">Marca</label>
                        <input type="text" name="marca" id="marca" value="{{ old('marca', $material->marca) }}">
                        @error('marca') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="proveedor">Proveedor</label>
                        <input type="text" name="proveedor" id="proveedor" value="{{ old('proveedor', $material->proveedor) }}">
                        @error('proveedor') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock *</label>
                        <input type="number" name="stock" id="stock" value="{{ old('stock', $material->stock) }}" min="0" required>
                        <div class="field-help">Escribe el stock total actual del material, no la cantidad a sumar.</div>
                        @error('stock') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="fotografia">Fotografía</label>
                        @if($material->fotografia)
                            <div class="foto-actual">
                                <img src="{{ asset('storage/' . $material->fotografia) }}" alt="Foto actual">
                                <span>Foto actual. Sube una nueva para reemplazarla.</span>
                            </div>
                        @endif
                        <input type="file" name="fotografia" id="fotografia" accept="image/*">
                        <div class="field-help">Formatos permitidos: JPG, PNG o WEBP. Máximo 2 MB.</div>
                        @error('fotografia') <span class="error">{{ $message }}</span> @enderror
                    </div>
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
    }

    function cerrarEscaner() {
        document.getElementById('scannerModal').style.display = 'none';

        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }
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
