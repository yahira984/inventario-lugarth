<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Material - ERP Inventario</title>
    <style>
        /* === ESTRUCTURA PRINCIPAL Y SIDEBAR === */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4f6f9; 
            margin: 0; 
            color: #333; 
            display: flex; /* Esto pone el sidebar y el contenido uno al lado del otro */
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1; /* Toma todo el espacio restante a la derecha del sidebar */
            padding: 40px 20px;
            box-sizing: border-box;
            height: 100vh;
            overflow-y: auto; /* Permite scroll solo en el contenido */
        }

        /* === DISEÑO DEL FORMULARIO GERENCIAL === */
        .container { 
            max-width: 700px; 
            margin: 0 auto; 
            background: #fff; 
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); 
        }
        h2 { 
            color: #2c3e50; 
            margin-top: 0; 
            margin-bottom: 30px; 
            border-bottom: 3px solid #3498db; 
            padding-bottom: 10px; 
            text-align: center;
            font-size: 24px;
        }
        .form-group { margin-bottom: 22px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #34495e; font-size: 14px; }
        
        input[type="text"], input[type="number"], textarea, select, input[type="file"] { 
            width: 100%; padding: 12px 15px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 15px; transition: all 0.3s ease; background-color: #fcfcfc;
        }
        input[type="text"]:focus, input[type="number"]:focus, textarea:focus, select:focus { 
            border-color: #3498db; outline: none; background-color: #fff; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); 
        }
        textarea { resize: vertical; min-height: 100px; }
        input[type="file"] { background-color: #fff; padding: 10px; cursor: pointer; border: 1px dashed #adb5bd; }
        
        /* === BOTONES === */
        .btn-submit { background-color: #2ecc71; color: white; border: none; padding: 14px 20px; font-size: 16px; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%; transition: background 0.3s; margin-top: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
        .btn-submit:hover { background-color: #27ae60; box-shadow: 0 4px 6px rgba(46, 204, 113, 0.3); }
        
        .btn-cancel { display: block; text-align: center; margin-top: 20px; color: #7f8c8d; text-decoration: none; font-weight: 500; transition: color 0.3s; }
        .btn-cancel:hover { color: #e74c3c; }

        .input-group { display: flex; gap: 10px; }
        .input-group input { flex: 1; }
        
        .btn-action { background-color: #f39c12; color: white; border: none; padding: 0 15px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: background 0.3s; }
        .btn-action:hover { background-color: #d68910; }
        
        .btn-camera { background-color: #34495e; color: white; border: none; padding: 12px 15px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: background 0.3s; width: 100%; margin-bottom: 12px; }
        .btn-camera:hover { background-color: #2c3e50; }

        /* === CAJAS DE EVIDENCIA === */
        .evidence-box { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; }
        .evidence-box.highlight { background: #f0fbf4; border-color: #c3e6cb; margin-top: 20px; }

        /* === MENSAJES DE ERROR === */
        .alert-danger { background-color: #f8d7da; color: #721c24; padding: 15px 20px; border-radius: 6px; border: 1px solid #f5c6cb; margin-bottom: 25px; }
        .alert-danger ul { margin: 10px 0 0 0; padding-left: 20px; }

        /* === MODALES (CÁMARA Y ESCÁNER) === */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.7); align-items: center; justify-content: center; z-index: 1000; backdrop-filter: blur(3px); }
        .modal-content { background-color: #fff; padding: 25px; border-radius: 10px; width: 90%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: center; }
        .close-btn { background-color: #e74c3c; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 20px; }
        #videoElement { width: 100%; max-height: 350px; background-color: #000; border-radius: 8px; object-fit: cover; }
        .btn-capture { background-color: #2ecc71; color: white; border: none; padding: 15px 20px; font-size: 18px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 15px; }
    </style>
</head>
<body>

    <!-- INCLUSIÓN DEL SIDEBAR ORIGINAL -->
    @include('materiales.partials.sidebar')

    <!-- CONTENEDOR PRINCIPAL A LA DERECHA -->
    <div class="main-content">
        <div class="container">
            <h2>Registrar Nuevo Material</h2>

            @if ($errors->any())
                <div class="alert-danger">
                    <strong>¡Atención! Hay un problema con los datos:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('materiales.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- CÓDIGO DE BARRAS -->
                <div class="form-group">
                    <label for="codigo_barras">Código de Barras / SKU (Escáner Inteligente)</label>
                    <div class="input-group">
                        <input type="text" name="codigo_barras" id="codigo_barras" placeholder="Escribe o escanea el código...">
                        <button type="button" class="btn-action" onclick="abrirEscaner()">📷 Escanear</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoría / Tipo de Equipo *</label>
                    <select name="categoria" id="categoria" required>
                        <option value="">-- Selecciona una Categoría --</option>
                        <option value="EQUIPO ACERO AL CARBON">EQUIPO ACERO AL CARBON</option>
                        <option value="EQUIPO ACERO INOXIDABLE">EQUIPO ACERO INOXIDABLE</option>
                        <option value="EQUIPO TIPO ASA INOXIDABLE">EQUIPO TIPO ASA INOXIDABLE</option>
                        <option value="EQUIPO AC SIST DSPCH MEC FILL">EQUIPO AC SIST DSPCH MEC FILL</option>
                        <option value="EQUIPO AC SIST DSPCH MEC LIQUID">EQUIPO AC SIST DSPCH MEC LIQUID</option>
                        <option value="EQUIPO ACERO AL CARBON UPV">EQUIPO ACERO AL CARBON UPV</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="numero_parte">No. de Parte / Código Interno</label>
                    <input type="text" name="numero_parte" id="numero_parte" placeholder="Ej. 3176MS">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción del Material *</label>
                    <textarea name="descripcion" id="descripcion" placeholder="Detalles precisos del componente..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="marca">Marca</label>
                    <input type="text" name="marca" id="marca" placeholder="Ej. BETTS">
                </div>

                <div class="form-group">
                    <label for="proveedor">Proveedor</label>
                    <input type="text" name="proveedor" id="proveedor" placeholder="Ej. Promotora Industrial RG">
                </div>

                <div class="form-group">
                    <label for="stock">Cantidad de Entrada (Stock Inicial) *</label>
                    <input type="number" name="stock" id="stock" placeholder="0" min="0" required>
                </div>
                
                <!-- FOTO 1: PRODUCTO -->
                <div class="form-group evidence-box">
                    <label for="fotografia">1. Fotografía del Producto (Catálogo)</label>
                    <input type="file" name="fotografia" id="fotografia" accept="image/*">
                </div>
                
                <!-- FOTO 2: EVIDENCIA (CÁMARA PC/TABLET/MÓVIL) -->
                <div class="form-group evidence-box highlight">
                    <label for="evidencia_foto">2. Evidencia de Recepción (Nota, Remisión o Caja) *</label>
                    
                    <button type="button" class="btn-camera" onclick="abrirCamaraWeb()">📸 Tomar Foto con el Dispositivo</button>
                    <div style="text-align: center; margin: 10px 0; font-size: 13px; color: #7f8c8d; text-transform: uppercase;">- Ó sube un archivo -</div>
                    
                    <input type="file" name="evidencia_foto" id="evidencia_foto" accept="image/*" onchange="mostrarVistaPrevia()">
                    
                    <div style="text-align: center;">
                        <img id="vista-previa" style="display: none; width: 100%; max-width: 250px; margin-top: 15px; border-radius: 8px; border: 2px solid #2ecc71;" alt="Vista previa">
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Guardar Registro en Almacén</button>
                <a href="{{ route('materiales.index') }}" class="btn-cancel">Cancelar y volver al listado</a>
            </form>
        </div>
    </div>

    <!-- === MODAL DEL ESCÁNER === -->
    <div id="scannerModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-top: 0; color: #2c3e50;">Escanear Código de Barras</h3>
            <div id="reader" style="width: 100%;"></div>
            <button type="button" class="close-btn" onclick="cerrarEscaner()">Cancelar Escaneo</button>
        </div>
    </div>

    <!-- === MODAL DE LA CÁMARA WEB === -->
    <div id="camaraModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-top: 0; color: #2c3e50;">Capturar Evidencia Visual</h3>
            <video id="videoElement" autoplay playsinline></video>
            <canvas id="canvasElement" style="display: none;"></canvas>
            
            <button type="button" class="btn-capture" onclick="tomarFotoWeb()">✔ Capturar Imagen</button>
            <button type="button" class="close-btn" onclick="cerrarCamaraWeb()" style="background-color: #95a5a6;">Cancelar</button>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        // --- 1. LÓGICA DE VISTA PREVIA (Evidencia) ---
        function mostrarVistaPrevia() {
            const input = document.getElementById('evidencia_foto');
            const vistaPrevia = document.getElementById('vista-previa');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    vistaPrevia.src = e.target.result;
                    vistaPrevia.style.display = 'inline-block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                vistaPrevia.style.display = 'none';
            }
        }

        // --- 2. LÓGICA DE CÁMARA TABLET/PC ---
        let streamVideo = null;
        function abrirCamaraWeb() {
            const video = document.getElementById('videoElement');
            document.getElementById('camaraModal').style.display = 'flex';

            navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                .then(function(stream) {
                    streamVideo = stream;
                    video.srcObject = stream;
                })
                .catch(function(err) {
                    alert("No se pudo acceder a la cámara. Verifica los permisos de tu navegador.");
                    cerrarCamaraWeb();
                });
        }

        function cerrarCamaraWeb() {
            document.getElementById('camaraModal').style.display = 'none';
            if (streamVideo) {
                streamVideo.getTracks().forEach(track => track.stop());
            }
        }

        function tomarFotoWeb() {
            const video = document.getElementById('videoElement');
            const canvas = document.getElementById('canvasElement');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function(blob) {
                const file = new File([blob], "evidencia_" + Date.now() + ".jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('evidencia_foto').files = dataTransfer.files;

                mostrarVistaPrevia();
                cerrarCamaraWeb();
            }, 'image/jpeg', 0.9);
        }

        // --- 3. LÓGICA DEL ESCÁNER INTELIGENTE ---
        let html5QrcodeScanner = null;
        function abrirEscaner() {
            document.getElementById('scannerModal').style.display = 'flex';
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 250} }, false);
            html5QrcodeScanner.render((textoDecodificado) => {
                document.getElementById('codigo_barras').value = textoDecodificado;
                cerrarEscaner();
                consultarCodigoLocal(textoDecodificado);
            }, (error) => {});
        }

        function cerrarEscaner() {
            document.getElementById('scannerModal').style.display = 'none';
            if (html5QrcodeScanner) { html5QrcodeScanner.clear(); }
        }

        function consultarCodigoLocal(codigo) {
            fetch(`/materiales/buscar-por-codigo?codigo=${codigo}`)
                .then(response => response.json())
                .then(data => {
                    if (data.encontrado) {
                        document.getElementById('categoria').value = data.categoria;
                        document.getElementById('numero_parte').value = data.numero_parte;
                        document.getElementById('descripcion').value = data.descripcion;
                        document.getElementById('marca').value = data.marca;
                        document.getElementById('proveedor').value = data.proveedor;
                        document.getElementById('stock').focus();
                        alert("✅ ¡Material identificado! Datos cargados automáticamente.");
                    }
                })
                .catch(err => console.error("Error al consultar:", err));
        }
    </script>
</body>
</html>