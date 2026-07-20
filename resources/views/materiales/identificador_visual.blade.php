<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identificador Visual - AppLugarth</title>

    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    
    <style>
        /* --- ESTILOS ULTRA-FUTURISTAS MASTER (Modo Identificador) --- */
        :root {
            --bg: #030712; 
            --surface: rgba(15, 23, 42, 0.7); 
            --ink: #ffffff; 
            --muted: #94a3b8; 
            --cyan-glow: #0a1169;
            --blue-glow: #1a0b5e;
            --emerald-glow: #18117a;
            --line: rgba(114, 56, 248, 0.2);
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
        .app-content { flex: 1; padding: 40px 20px; overflow-y: auto; display: flex; justify-content: center; }

        /* --- CONTENEDOR PRINCIPAL --- */
        .container {
            width: 100%;
            max-width: 1000px;
            background: var(--surface);
            backdrop-filter: blur(16px);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow-glass);
            padding: 40px;
        }

        .page-header h1 {
            margin: 0 0 8px 0;
            background: linear-gradient(to right, #130d66, #1a0d53);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(13, 8, 77, 0.2); 
        }

        /* --- ZONA DE ESCANEO (Drop Area) --- */
        .scanner-body { display: grid; grid-template-columns: 1fr 320px; gap: 30px; margin-top: 30px; }

        .drop-area {
            border: 2px dashed rgba(6, 182, 212, 0.4);
            border-radius: 20px;
            background: rgba(6, 182, 212, 0.03);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            min-height: 300px;
        }
        .drop-area:hover { background: rgba(6, 182, 212, 0.08); border-color: var(--cyan-glow); }

        .file-input { display: none; }
        .main-preview { max-width: 100%; max-height: 250px; border-radius: 12px; }

        .upload-state { text-align: center; color: var(--ink); }
        .upload-icon { font-size: 0; color: var(--cyan-glow); display: block; }
        .upload-icon::before { content: "Camara"; font-size: 28px; font-weight: 900; }
        .upload-title { font-weight: 800; font-size: 18px; margin: 10px 0; display: block; }
        .upload-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 14px; }
        
        .upload-action {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 0 14px;
            color: #ffffff;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            font-size: 13px;
            font-weight: 900;
            transition: transform 0.2s, filter 0.2s;
        }
        .upload-action:hover { filter: brightness(1.2); transform: translateY(-2px); }
        .upload-action.secondary { background: linear-gradient(135deg, #16a34a, #15803d); }
        
        .loading-note { color: var(--cyan-glow); font-weight: bold; display: none; margin-top: 10px; }
        .loading .loading-note { display: block; }

        /* --- PANEL LATERAL --- */
        .side-panel { display: flex; flex-direction: column; gap: 20px; }
        .status-box {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 16px;
            padding: 20px;
            position: relative;
            transition: all 0.4s ease;
            box-shadow: inset 0 0 20px rgba(6, 182, 212, 0.05);
        }
        .status-box:hover {
            border-color: var(--cyan-glow);
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.2), inset 0 0 20px rgba(6, 182, 212, 0.1);
        }
        .status-box strong { 
            color: var(--cyan-glow); 
            font-size: 12px; 
            text-transform: uppercase; 
            display: block; 
            margin-bottom: 8px; 
            text-shadow: 0 0 10px rgba(6, 182, 212, 0.5); 
        }
        .status-box span { font-size: 14px; color: #fff; display: block; line-height: 1.4; }

        /* Chips */
        .chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .chip { background: rgba(33, 15, 110, 0.1); color: #100950; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid rgba(56, 189, 248, 0.2); }

        /* --- RESULTADOS --- */
        .results-shell { margin-top: 40px; border-top: 1px solid var(--line); padding-top: 30px; }
        .results-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .result-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }

        .result-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            gap: 12px;
            transition: all 0.3s;
        }
        .result-card:hover { border-color: var(--cyan-glow); transform: translateY(-5px); }
        .result-photo { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; }
        .result-title { font-weight: 800; font-size: 14px; margin-bottom: 8px; }
        .result-meta { font-size: 11px; color: var(--muted); display: grid; gap: 2px; }
        .category-badge {
            display: inline-flex; width: fit-content; max-width: 100%; border-radius: 8px; padding: 5px 8px; margin-bottom: 8px;
            background: linear-gradient(135deg, #0ea5e9, #2563eb); color: #ffffff; border: 1px solid rgba(37, 99, 235, 0.55);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18); font-size: 10px; font-weight: 900; line-height: 1.15; text-transform: uppercase;
        }
        
        .score-row { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
        .score { color: var(--emerald-glow); font-weight: bold; font-size: 12px; }
        .btn-secondary { background: rgba(255,255,255,0.05); color: #fff; padding: 4px 12px; border-radius: 6px; font-size: 11px; text-decoration: none; border: 1px solid rgba(255,255,255,0.1); }
        .btn-secondary:hover { background: var(--blue-glow); }
        .empty-result, .muted {
            color: var(--muted); border: 1px solid rgba(56, 189, 248, 0.22); background: rgba(15, 23, 42, 0.5);
            border-radius: 12px; padding: 16px; font-size: 13px; font-weight: 700;
        }

        /* --- MODAL CAMARA FUTURISTA --- */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(3, 7, 18, 0.85); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; }
        .modal-content { background: var(--surface); border: 1px solid var(--cyan-glow); border-radius: 20px; padding: 30px; width: 90%; max-width: 500px; box-shadow: 0 0 40px rgba(6, 182, 212, 0.2); text-align: center; }
        .modal-title { color: var(--ink); margin-top: 0; margin-bottom: 20px; font-size: 22px; font-weight: 900; }
        #videoElement { width: 100%; max-height: 350px; border-radius: 12px; background: #000; border: 2px solid var(--line); margin-bottom: 20px; object-fit: cover; }
        .btn-capture { background: linear-gradient(135deg, #10b981, #047857); color: #fff; border: none; padding: 14px 20px; font-size: 16px; border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; margin-bottom: 12px; transition: filter 0.3s; }
        .btn-capture:hover { filter: brightness(1.2); }
        .btn-close { background: rgba(255,255,255,0.05); color: var(--muted); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; transition: all 0.3s; }
        .btn-close:hover { background: rgba(255,255,255,0.1); color: #fff; }

        @media (max-width: 768px) {
            .scanner-body { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="container">
            <div class="page-header">
                <h1>Identificador Visual</h1>
                <p class="header-meta">Foto de la pieza y sugerencias del inventario.</p>
            </div>

            @if($errors->any())
                <div class="alert-danger" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="scanner">
                <form action="{{ route('materiales.visual.search') }}" method="POST" enctype="multipart/form-data" id="visualForm">
                    @csrf
                    <div class="scanner-body">
                        <!-- El input sigue envuelto en el label para mantener el click nativo -->
                        <label class="drop-area" id="dropArea">
                            @if($preview)
                                <img src="{{ $preview }}" class="main-preview" alt="Foto analizada">
                            @else
                                <span class="upload-state" id="uploadState">
                                    <span class="upload-icon">📷</span>
                                    <span class="upload-title">Tomar foto o subir imagen</span>
                                    <span class="upload-subtitle">JPG, PNG o WEBP</span>
                                    
                                    <span class="upload-actions">
                                        <!-- Botón para la PC/Tablet. El onclick y preventDefault evitan que se abra el buscador de Windows -->
                                        <span class="upload-action" onclick="abrirCamaraWeb(event)">📸 Cámara PC/Tablet</span>
                                        <!-- Este botón se deja nativo para el celular o explorar archivos -->
                                        <span class="upload-action secondary">Subir imagen / Celular</span>
                                    </span>
                                    
                                    <span class="upload-actions loading-note">⚡ Analizando estructura visual...</span>
                                </span>
                            @endif
                            <input type="file" name="fotografia" id="fotografia" class="file-input" accept="image/*" capture="environment">
                        </label>

                        <aside class="side-panel">
                            <div class="status-box">
                                <strong>Lectura actual</strong>
                                <span>{{ $analisis ? 'Imagen procesada' : 'Sin imagen analizada.' }}</span>
                            </div>
                            <div class="status-box">
                                <strong>Resultado</strong>
                                <span>{{ $busquedaRealizada ? $resultados->count() . ' sugerencias.' : 'Selecciona una imagen.' }}</span>
                            </div>
                        </aside>
                    </div>
                </form>
            </section>

            <section class="results-shell">
                <div class="results-header">
                    <strong>Sugerencias</strong>
                </div>
                @if($busquedaRealizada && $resultados->isEmpty())
                    <div class="empty-result">
                        No encontre una coincidencia visual confiable. Solo se muestran materiales con foto y parecido fuerte.
                    </div>
                @elseif(!$busquedaRealizada)
                    <p class="muted">Aqui apareceran solo las coincidencias fuertes.</p>
                @else
                    <div class="result-grid">
                        @foreach($resultados as $material)
                            <article class="result-card">
                                <img src="{{ asset('storage/' . $material->fotografia) }}" class="result-photo" alt="Foto">
                                <div class="result-info">
                                    <div class="category-badge">{{ $material->categoria ?: 'Sin categoria' }}</div>
                                    <div class="result-title">{{ $material->descripcion }}</div>
                                    <div class="result-meta">
                                        <span>No. parte: {{ $material->numero_parte ?: 'N/A' }}</span>
                                        @if($material->apodo)<span>Apodo: {{ $material->apodo }}</span>@endif
                                        <span>Marca: {{ $material->marca }}</span>
                                        <span>Almacen: {{ $material->almacen ?: 'Sin definir' }}</span>
                                        <span>Stock: {{ $material->stock }} pzas</span>
                                    </div>
                                    <div class="score-row">
                                        <span class="score">{{ $material->puntaje_visual }} pts</span>
                                        <a href="{{ route('materiales.edit', $material) }}" class="btn-secondary">Ver</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </main>
</div>

<!-- === MODAL DE LA CÁMARA WEB === -->
<div id="camaraModal" class="modal">
    <div class="modal-content">
        <h3 class="modal-title">Escáner de Componentes</h3>
        <video id="videoElement" autoplay playsinline></video>
        <canvas id="canvasElement" style="display: none;"></canvas>
        <button type="button" class="btn-capture" onclick="tomarFotoWeb()">⚡ Analizar Captura</button>
        <button type="button" class="btn-close" onclick="cerrarCamaraWeb()">Cancelar Escaneo</button>
    </div>
</div>

<script>
    const input = document.getElementById('fotografia');
    const form = document.getElementById('visualForm');
    const dropArea = document.getElementById('dropArea');
    
    // Auto-Submit original para celular / carga de archivos
    input.addEventListener('change', () => {
        if (input.files.length) {
            dropArea.classList.add('loading');
            form.submit();
        }
    });

    // --- LÓGICA DE LA CÁMARA WEB (PC/Tablet) ---
    let streamVideo = null;

    function abrirCamaraWeb(e) {
        // Evita que el clic abra el buscador de archivos del sistema operativo
        e.preventDefault(); 
        
        const video = document.getElementById('videoElement');
        document.getElementById('camaraModal').style.display = 'flex';

        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
            .then(function(stream) {
                streamVideo = stream;
                video.srcObject = stream;
            })
            .catch(function(err) {
                alert("No se pudo acceder a la cámara. Verifica los permisos de tu navegador en Windows.");
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
            const file = new File([blob], "busqueda_visual_" + Date.now() + ".jpg", { type: "image/jpeg" });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            
            // Metemos el archivo generado por la cámara web al input oculto
            input.files = dataTransfer.files;

            cerrarCamaraWeb();
            
            // Disparamos la animación y el formulario tal cual como lo tenías
            dropArea.classList.add('loading');
            form.submit();
        }, 'image/jpeg', 0.9);
    }
</script>

</body>
</html>