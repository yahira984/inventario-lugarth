<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Material - Inventario</title>
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
            padding: 32px 18px;
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

        .form-header {
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
            padding: 24px 30px;
        }

        h2 {
            margin: 0;
            color: var(--blue-dark);
            font-size: 26px;
            line-height: 1.2;
        }

        .header-meta {
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
        }

        .alert-danger ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        form {
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
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--ink);
            font-size: 14px;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select,
        input[type="file"] {
            width: 100%;
            min-height: 46px;
            padding: 11px 12px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fff;
            color: var(--ink);
            font-family: inherit;
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 168, 0.16);
        }

        textarea {
            resize: vertical;
            min-height: 112px;
        }

        input[disabled],
        textarea[disabled],
        select[disabled] {
            background: #f4f7fa;
            color: #526170;
            cursor: not-allowed;
        }

        input[type="file"] {
            padding: 9px 12px;
            cursor: pointer;
        }

        .input-group {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: stretch;
        }

        .btn-scan,
        .btn-submit,
        .close-btn {
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 800;
            font-family: inherit;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
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

        .scan-status {
            display: none;
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
        }

        .scan-status.info {
            display: block;
            background: #eef6ff;
            color: #17426f;
            border: 1px solid #b7d9ff;
        }

        .scan-status.success {
            display: block;
            background: #eaf8f0;
            color: #0f6b3e;
            border: 1px solid #a9dfbf;
        }

        .scan-status.warning {
            display: block;
            background: #fff7e6;
            color: #8a5700;
            border: 1px solid #ffd98a;
        }

        .scan-status.error {
            display: block;
            background: #fff1f0;
            color: #842029;
            border: 1px solid #f2b8b5;
        }

        .form-actions {
            display: grid;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-submit {
            width: 100%;
            min-height: 48px;
            background-color: var(--green);
            color: #fff;
            font-size: 16px;
        }

        .btn-submit:hover {
            background-color: #116a40;
            box-shadow: 0 10px 24px rgba(24, 134, 83, 0.22);
        }

        .btn-cancel {
            display: block;
            color: var(--muted);
            text-align: center;
            text-decoration: none;
            font-weight: 700;
            padding: 8px;
        }

        .btn-cancel:hover {
            color: var(--red);
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
            body {
                padding: 16px 10px;
            }

            .form-header,
            form {
                padding-left: 18px;
                padding-right: 18px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .input-group {
                grid-template-columns: 1fr;
            }

            .btn-scan {
                min-height: 44px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-header">
        <h2>Entrada de Material</h2>
        <p class="header-meta">Camara o escaner USB, el mismo codigo manda.</p>
    </div>

    @if ($errors->any())
        <div class="alert-danger">
            <strong>Ocurrio un error:</strong>
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
                <label for="codigo_barras">Codigo de Barras / SKU</label>
                <div class="input-group">
                    <input type="text" name="codigo_barras" id="codigo_barras" placeholder="Escanea o escribe el codigo" autocomplete="off" autofocus>
                    <button type="button" class="btn-scan" onclick="abrirEscaner()">Escanear camara</button>
                </div>
                <div id="codigo_status" class="scan-status"></div>
            </div>

            <div class="form-group">
                <label for="categoria">Categoria / Tipo de Equipo *</label>
                <select name="categoria" id="categoria" required data-material-field>
                    <option value="">-- Selecciona una categoria --</option>
                    <option value="EQUIPO ACERO AL CARBON">EQUIPO ACERO AL CARBON</option>
                    <option value="EQUIPO ACERO INOXIDABLE">EQUIPO ACERO INOXIDABLE</option>
                    <option value="EQUIPO TIPO ASA INOXIDABLE">EQUIPO TIPO ASA INOXIDABLE</option>
                    <option value="EQUIPO AC SIST DSPCH MEC FILL">EQUIPO AC SIST DSPCH MEC FILL</option>
                    <option value="EQUIPO AC SIST DSPCH MEC LIQUID">EQUIPO AC SIST DSPCH MEC LIQUID</option>
                    <option value="EQUIPO ACERO AL CARBON UPV">EQUIPO ACERO AL CARBON UPV</option>
                </select>
            </div>

            <div class="form-group">
                <label for="numero_parte">No. de Parte / Codigo</label>
                <input type="text" name="numero_parte" id="numero_parte" placeholder="Ej. 3176MS" data-material-field>
            </div>

            <div class="form-group full">
                <label for="descripcion">Descripcion del Material *</label>
                <textarea name="descripcion" id="descripcion" placeholder="Detalles del componente" required data-material-field></textarea>
            </div>

            <div class="form-group">
                <label for="marca">Marca</label>
                <input type="text" name="marca" id="marca" placeholder="Ej. BETTS" data-material-field>
            </div>

            <div class="form-group">
                <label for="proveedor">Proveedor</label>
                <input type="text" name="proveedor" id="proveedor" placeholder="Ej. Promotora Industrial RG" data-material-field>
            </div>

            <div class="form-group">
                <label for="stock">Cantidad de Entrada *</label>
                <input type="number" name="stock" id="stock" placeholder="0" min="0" required>
            </div>

            <div class="form-group">
                <label for="fotografia">Fotografia</label>
                <input type="file" name="fotografia" id="fotografia" accept="image/*" data-material-field>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit" id="submitButton">Guardar Material en Inventario</button>
            <a href="{{ route('materiales.index') }}" class="btn-cancel">Cancelar y regresar al listado</a>
        </div>
    </form>
</div>

<div id="scannerModal" class="modal">
    <div class="modal-content">
        <h3>Escanear Codigo de Barras</h3>
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
    }

    function cerrarEscaner() {
        document.getElementById('scannerModal').style.display = 'none';

        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }
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
        setEstadoCodigo(`Codigo ya registrado: ${data.descripcion}. Stock actual: ${data.stock} pzas. Al guardar se sumara la cantidad capturada.`, 'success');
        stockInput.focus();
    }

    function prepararMaterialNuevo() {
        bloquearCamposMaterial(false);
        limpiarDatosMaterial();
        stockInput.value = '';
        stockInput.placeholder = 'Cantidad inicial';
        submitButton.textContent = 'Guardar Material en Inventario';
        setEstadoCodigo('Codigo nuevo detectado. Captura los datos para registrarlo por primera vez.', 'warning');
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
        setEstadoCodigo('Buscando codigo en inventario...', 'info');

        fetch(`{{ route('materiales.buscarPorCodigo') }}?codigo=${encodeURIComponent(codigoLimpio)}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error('No se pudo consultar el codigo.');
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
                console.error('Error al consultar el codigo:', error);
                ultimoCodigoConsultado = '';
                bloquearCamposMaterial(false);
                setEstadoCodigo('No se pudo consultar el codigo. Intenta otra vez.', 'error');
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
