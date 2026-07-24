<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identificador Visual - AppLugarth</title>

    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    
    <!-- LIBRERÍAS DE CROPPER.JS PARA EL RECORTE MANUAL -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    
    <style>
        :root {
            --visual-ink: #092743;
            --visual-muted: #61788e;
            --visual-line: #d6e3ed;
            --visual-blue: #1769d2;
            --visual-blue-dark: #0f55ad;
            --visual-blue-soft: #eaf4ff;
            --visual-green: #079669;
            --visual-green-soft: #eafbf4;
            --visual-red: #dc2626;
            --visual-shadow: 0 16px 38px rgba(19, 57, 87, .08);
        }

        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: var(--visual-ink); background: #f2f7fa; font-family: "Segoe UI", Tahoma, sans-serif; }
        button, input { font: inherit; }
        [hidden] { display: none !important; }
        .app-shell { display: flex; min-height: 100vh; }
        .visual-page.app-content { min-width: 0; flex: 1; padding: 34px clamp(18px, 3vw, 48px) 80px; overflow: visible; }
        .visual-workspace { width: min(1440px, 100%); margin: 0 auto; display: grid; gap: 16px; }
        .visual-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 22px 24px;
            background: #fff;
            border: 1px solid var(--visual-line);
            border-radius: 8px;
            box-shadow: var(--visual-shadow);
        }

        /* 100dvh previene el bug de la barra de direcciones en celulares */
        .app-shell { display: flex; height: 100dvh; width: 100vw; overflow: hidden; }
        
        /* CORRECCIÓN PRINCIPAL: display block restaura el scroll correctamente */
        .app-content { 
            flex: 1; 
            padding: 40px 20px; 
            overflow-y: auto; 
            display: block; 
        }

        .container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto 80px auto; /* Centrado clásico y margen inferior para que no estorbe el menú */
            background: var(--surface);
            backdrop-filter: blur(16px);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow-glass);
            padding: 40px;
        }
        .visual-title-mark svg { width: 25px; height: 25px; }
        .visual-header h1 { margin: 0 0 5px; font-size: 34px; }
        .visual-header p { margin: 0; color: var(--visual-muted); font-size: 13px; font-weight: 600; }
        .visual-state {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 11px;
            color: #075e47;
            background: var(--visual-green-soft);
            border: 1px solid #a7ebd1;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .scanner-body { display: grid; grid-template-columns: 1fr 320px; gap: 30px; margin-top: 30px; }

        .drop-area {
            border: 2px dashed rgba(6, 182, 212, 0.4);
            border-radius: 20px;
            background: rgba(6, 182, 212, 0.03);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--visual-blue);
            background: #fff;
            border: 1px solid #b9d8f3;
            border-radius: 8px;
            box-shadow: 0 9px 22px rgba(23, 105, 210, .1);
        }
        .drop-area:hover { background: rgba(6, 182, 212, 0.08); border-color: var(--cyan-glow); }

        .main-preview { max-width: 100%; max-height: 250px; border-radius: 12px; }

        .upload-state { text-align: center; color: var(--ink); }
        .upload-icon { font-size: 0; color: var(--cyan-glow); display: block; }
        .upload-icon::before { content: "Camara"; font-size: 28px; font-weight: 900; }
        .upload-title { font-weight: 800; font-size: 18px; margin: 10px 0; display: block; }
        .upload-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 14px; }
        
        .upload-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 0 14px;
            color: #ffffff;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            font-size: 13px;
            font-weight: 900;
            transition: transform 0.2s, filter 0.2s;
            border: none;
            cursor: pointer;
        }
        .upload-action:hover { filter: brightness(1.2); transform: translateY(-2px); }
        .upload-action.secondary { background: linear-gradient(135deg, #16a34a, #15803d); }
        
        .loading-note { color: var(--cyan-glow); font-weight: bold; display: none; margin-top: 10px; }
        .loading .loading-note { display: block; }

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
        .preview-actions {
            position: absolute;
            right: 12px;
            bottom: 12px;
            left: 12px;
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 9px;
            background: rgba(255, 255, 255, .94);
            border: 1px solid rgba(185, 205, 222, .9);
            border-radius: 7px;
            box-shadow: 0 10px 24px rgba(12, 42, 67, .12);
            backdrop-filter: blur(10px);
        }
        .status-box strong { color: var(--cyan-glow); font-size: 12px; text-transform: uppercase; display: block; margin-bottom: 8px; text-shadow: 0 0 10px rgba(6, 182, 212, 0.5); }
        .status-box span { font-size: 14px; color: #fff; display: block; line-height: 1.4; }

        .results-shell { margin-top: 40px; border-top: 1px solid var(--line); padding-top: 30px; }
        .results-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .result-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }

        .result-card { background: rgba(30, 41, 59, 0.6); border: 1px solid var(--line); border-radius: 16px; padding: 16px; display: flex; gap: 12px; transition: all 0.3s; }
        .result-card:hover { border-color: var(--cyan-glow); transform: translateY(-5px); }
        .result-photo { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; }
        .result-title { font-weight: 800; font-size: 14px; margin-bottom: 8px; }
        .result-meta { font-size: 11px; color: var(--muted); display: grid; gap: 2px; }
        .category-badge { display: inline-flex; width: fit-content; border-radius: 8px; padding: 5px 8px; margin-bottom: 8px; background: linear-gradient(135deg, #0ea5e9, #2563eb); color: #ffffff; font-size: 10px; font-weight: 900; text-transform: uppercase; }
        .score-row { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
        .score { color: var(--emerald-glow); font-weight: bold; font-size: 12px; }
        .btn-secondary { background: rgba(255,255,255,0.05); color: #fff; padding: 4px 12px; border-radius: 6px; font-size: 11px; text-decoration: none; border: 1px solid rgba(255,255,255,0.1); }
        .btn-secondary:hover { background: var(--blue-glow); }
        .empty-result, .muted { color: var(--muted); border: 1px solid rgba(56, 189, 248, 0.22); background: rgba(15, 23, 42, 0.5); border-radius: 12px; padding: 16px; font-size: 13px; font-weight: 700; }

        /* --- MODALES --- */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(3, 7, 18, 0.95); backdrop-filter: blur(10px); z-index: 9999; align-items: center; justify-content: center; }
        .modal-content { background: var(--surface); border: 1px solid var(--cyan-glow); border-radius: 20px; padding: 25px; width: 95%; max-width: 700px; box-shadow: 0 0 50px rgba(6, 182, 212, 0.15); text-align: center; }
        .modal-title { color: var(--ink); margin-top: 0; margin-bottom: 5px; font-size: 22px; font-weight: 900; }
        .modal-subtitle { color: var(--muted); font-size: 14px; margin-bottom: 20px; }
        
        #videoElement { width: 100%; max-height: 50vh; border-radius: 12px; background: #000; border: 2px solid var(--line); margin-bottom: 20px; object-fit: cover; }
        .cropper-container-wrapper { width: 100%; max-height: 55vh; margin-bottom: 20px; background: #000; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); }
        #imageToCrop { max-width: 100%; display: block; }

        .btn-capture { background: linear-gradient(135deg, #10b981, #047857); color: #fff; border: none; padding: 14px 20px; font-size: 16px; border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; margin-bottom: 12px; }
        .btn-capture:hover { filter: brightness(1.2); }
        .btn-close { background: rgba(255,255,255,0.05); color: var(--muted); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; }
        .btn-close:hover { background: rgba(255,255,255,0.1); color: #fff; }

        /* AJUSTES PARA TELÉFONO */
        @media (max-width: 768px) { 
            .scanner-body { grid-template-columns: 1fr; }
            .result-grid { grid-template-columns: 1fr; } /* Aseguramos que sea una sola columna en pantallas muy chicas */
            .app-content { padding: 20px 15px; } 
            .container { padding: 20px; margin-bottom: 150px; } /* Añadimos mucho más margen extra al final para brincarnos por completo el menú inferior */
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
        }

        @keyframes visual-spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')

            @if($errors->any())
                <div style="padding: 15px; border-radius: 10px; margin-bottom: 20px; background: rgba(239,68,68,0.1); border: 1px solid #ef4444; color: #fca5a5; font-weight: bold;">
                    {{ $errors->first() }}
                </div>
                <span class="visual-state">
                    <i></i>
                    {{ ($iaActiva ?? false) ? 'IA local activa · fotos privadas' : 'Comparador clásico disponible' }}
                </span>
            </header>

            @if($errors->any())
                <div class="visual-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            <section class="capture-card">
                <form action="{{ route('materiales.visual.search') }}" method="POST" enctype="multipart/form-data" id="visualForm">
                    @csrf
                    <div class="scanner-body">
                        <!-- Convertimos el label en div para controlar los clics con JavaScript -->
                        <div class="drop-area" id="dropArea">
                            @if($preview)
                                <img
                                    src="{{ $preview }}"
                                    class="main-preview"
                                    alt="Foto analizada"
                                    data-workspace-lightbox
                                    data-lightbox-title="Foto analizada"
                                    data-lightbox-caption="Imagen utilizada para buscar coincidencias"
                                >
                                <div class="preview-actions">
                                    <button type="button" class="visual-button" id="openCamera">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.5 4 16 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3l1.5-3h5ZM12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                                        Tomar otra foto
                                    </button>
                                    <button type="button" class="visual-button visual-button-green" id="openFile">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg>
                                        Elegir otra imagen
                                    </button>
                                </div>
                            @else
                                <span class="upload-state" id="uploadState">
                                    <span class="upload-icon">📷</span>
                                    <span class="upload-title">Identificador Visual Inteligente</span>
                                    <span class="upload-subtitle">JPG, PNG o WEBP</span>
                                    
                                    <span class="upload-actions">
                                        <!-- BOTONES ORIGINALES RESTAURADOS -->
                                        <button type="button" class="upload-action" onclick="abrirCamaraWeb(event)">📸 Cámara PC/Tablet</button>
                                        <button type="button" class="upload-action secondary" onclick="document.getElementById('fotografiaRaw').click(); event.stopPropagation();">Subir imagen / Celular</button>
                                    </span>
                                    
                                    <span class="upload-actions loading-note" id="loadingText" style="display: none;">⚡ Procesando recorte...</span>
                                </span>
                            @endif
                            
                            <!-- Input desconectado para evitar el Auto-Submit fantasma -->
                            <input type="file" id="fotografiaRaw" accept="image/*" capture="environment" style="display: none;">
                            
                            <!-- Input oficial donde inyectaremos la foto ya recortada -->
                            <input type="file" name="fotografia" id="fotografiaFinal" style="display: none;">
                        </div>

                        <aside class="side-panel">
                            <article class="status-box" style="--status-color:#1769d2">
                                <strong>Lectura actual</strong>
                                <span>{{ $analisis ? 'Imagen procesada' : 'Esperando una imagen' }}</span>
                                <small>
                                    {{ $analisis['motor'] ?? (($iaActiva ?? false) ? 'CLIP + DINOv2 local preparado' : 'Sin IA local') }}.
                                    La foto no sale de esta computadora.
                                </small>
                            </article>
                            <article class="status-box" style="--status-color:#079669">
                                <strong>Resultado</strong>
                                <span>{{ $busquedaRealizada ? $resultados->count() . ' sugerencias' : 'Sin búsqueda todavía' }}</span>
                                <small>Se muestran únicamente coincidencias visuales fuertes.</small>
                            </article>
                            <div class="visual-tip">Después de tomar o subir la foto, encierra únicamente la pieza. La IA ignorará mucho mejor el taller, piso, manos y objetos cercanos.</div>
                        </aside>
                    </div>
                </form>
            </section>

            <section class="results-card">
                <div class="results-heading">
                    <h2>Sugerencias</h2>
                    <span>{{ $busquedaRealizada ? $resultados->count() . ' resultados encontrados' : 'Aún no se ha analizado una foto' }}</span>
                </div>

                @if($busquedaRealizada && $resultados->isEmpty())
                    <div class="empty-result">
                        No se encontró una coincidencia visual suficientemente confiable. Prueba con la pieza centrada, más cerca y con mejor iluminación.
                    </div>
                @elseif(!$busquedaRealizada)
                    <p class="muted">Aquí aparecerán solo las coincidencias fuertes.</p>
                @else
                    <div class="result-grid">
                        @foreach($resultados as $material)
                            @php
                                $materialInventoryUrl = route('materiales.index', [
                                    'material_id' => $material->id,
                                    'buscar' => $material->numero_parte ?: $material->descripcion,
                                    'destacar' => $material->id,
                                ]) . '#material-' . $material->id;
                            @endphp
                            <article class="result-card">
                                <button
                                    type="button"
                                    class="result-photo-button"
                                    data-workspace-lightbox
                                    data-lightbox-title="{{ $material->descripcion }}"
                                    data-lightbox-caption="{{ $material->categoria ?: 'Sin categoría' }} · {{ $material->puntaje_visual }} puntos"
                                >
                                    <img src="{{ asset('storage/' . $material->fotografia) }}" class="result-photo" alt="{{ $material->descripcion }}">
                                </button>
                                <div class="result-info">
                                    <span class="category-badge">{{ $material->categoria ?: 'Sin categoría' }}</span>
                                    @if(($material->motor_visual ?? null) === 'ia')
                                        <span class="ai-engine-badge">IA local</span>
                                    @endif
                                    <strong class="result-title">{{ $material->descripcion }}</strong>
                                    <div class="result-meta">
                                        <span>No. parte: {{ $material->numero_parte ?: 'N/A' }}</span>
                                        @if($material->apodo)<span>Apodo: {{ $material->apodo }}</span>@endif
                                        <span>Marca: {{ $material->marca }}</span>
                                        <span>Almacén: {{ $material->almacen ?: 'Sin definir' }}</span>
                                        <span>Stock: {{ $material->stock }} pzas</span>
                                    </div>
                                    <div class="score-row">
                                        <span class="score">{{ $material->puntaje_visual }} pts</span>
                                        <a href="{{ $materialInventoryUrl }}" class="result-link">Ver en inventario</a>
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

<!-- === MODAL 1: CÁMARA WEB (PC/Tablet) === -->
<div id="camaraModal" class="modal">
    <div class="modal-content">
        <h3 class="modal-title">Escáner de Componentes</h3>
        <p class="modal-subtitle">Enfoca la pieza frente a la cámara.</p>
        <video id="videoElement" autoplay playsinline></video>
        <canvas id="canvasElement" style="display: none;"></canvas>
        <button type="button" class="btn-capture" onclick="tomarFotoWeb()">📸 Tomar Fotografía</button>
        <button type="button" class="btn-close" onclick="cerrarCamaraWeb()">Cancelar</button>
    </div>
</div>

<!-- === MODAL 2: RECORTADOR MANUAL (Cropper.js) === -->
<div id="cropModal" class="modal">
    <div class="modal-content">
        <h3 class="modal-title">Aísla la pieza</h3>
        <p class="modal-subtitle">Arrastra las esquinas para encerrar ÚNICAMENTE el termo/pieza que deseas buscar, ignorando el fondo.</p>
        
        <div class="cropper-container-wrapper">
            <img id="imageToCrop" src="" alt="Imagen a recortar">
        </div>
        
        <button type="button" class="btn-capture" id="btnProcesarRecorte" onclick="recortarYEnviar()">✂️ Recortar y Buscar</button>
        <button type="button" class="btn-close" onclick="cerrarCropModal()">Cancelar</button>
    </div>
</section>

<section class="crop-modal" id="cropModal" hidden role="dialog" aria-modal="true" aria-labelledby="cropTitle">
    <div class="crop-dialog">
        <header class="camera-header">
            <div>
                <strong id="cropTitle">Encierra la pieza</strong>
                <small>Dibuja un cuadro alrededor del objeto que quieres identificar</small>
            </div>
            <button type="button" class="camera-close" id="closeCrop" aria-label="Cerrar recorte" title="Cerrar recorte">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </header>
        <div class="crop-stage" id="cropStage">
            <img class="crop-source" id="cropSource" alt="Fotografía para recortar">
            <div class="crop-selection" id="cropSelection"></div>
        </div>
        <div class="crop-controls">
            <div class="crop-help">
                <strong>Deja la menor cantidad de fondo posible</strong>
                <span>Puedes volver a dibujar el cuadro cuantas veces necesites.</span>
            </div>
            <div class="crop-actions">
                <button type="button" class="visual-button visual-button-green" id="analyzeCrop">
                    Analizar esta pieza
                </button>
                <button type="button" class="visual-button visual-button-orange" id="useFullImage">
                    Usar foto completa
                </button>
                <button type="button" class="visual-button visual-button-light" id="cancelCrop">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rawInput = document.getElementById('fotografiaRaw');
        const finalInput = document.getElementById('fotografiaFinal');
        const form = document.getElementById('visualForm');
        const dropArea = document.getElementById('dropArea');
        const cropModal = document.getElementById('cropModal');
        const imageToCrop = document.getElementById('imageToCrop');
        const loadingText = document.getElementById('loadingText');
        const btnProcesarRecorte = document.getElementById('btnProcesarRecorte');
        let cropper = null;

        // Si hacen clic en el área punteada, abre el selector (A menos que sea un botón)
        dropArea.addEventListener('click', (e) => {
            if(e.target.tagName !== 'BUTTON') {
                rawInput.click();
            }
        });

        // 1. Manejo de Subida Nativa (Celular y Archivos)
        rawInput.addEventListener('change', (e) => {
            if (!rawInput.files || rawInput.files.length === 0) return;
            const file = rawInput.files[0];
            
            // Si suben un PDF o algo raro, lo manda directo para que Laravel lance el error normal
            if (!file.type.startsWith('image/')) {
                finalInput.files = rawInput.files;
                form.submit();
                return;
            }

            // Leemos la imagen temporalmente para abrirla en el recortador
            const reader = new FileReader();
            reader.onload = function(event) {
                abrirCropper(event.target.result);
            };
            reader.readAsDataURL(file);
        });

        // 2. Funciones globales de Cropper.js
        window.abrirCropper = function(imageSrc) {
            imageToCrop.src = imageSrc;
            cropModal.style.display = 'flex';
            
            if (cropper) { cropper.destroy(); }
            
            cropper = new Cropper(imageToCrop, {
                viewMode: 1,
                dragMode: 'crop',
                autoCropArea: 0.8,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        };

        window.cerrarCropModal = function() {
            cropModal.style.display = 'none';
            rawInput.value = ''; // Limpiamos para poder subir la misma foto si se equivocan
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        };

        window.recortarYEnviar = function() {
            if (!cropper) return;
            
            // Cambiamos interfaz a "Cargando..."
            btnProcesarRecorte.innerHTML = '⚡ Procesando...';
            btnProcesarRecorte.disabled = true;
            if(loadingText) loadingText.style.display = 'block';
            dropArea.classList.add('loading');

            // Sacamos el recorte limpio en 1024x1024 máximo
            const canvas = cropper.getCroppedCanvas({
                maxWidth: 1024,
                maxHeight: 1024,
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob(function(blob) {
                const newFile = new File([blob], "pieza_recortada_" + Date.now() + ".jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(newFile);
                
                // Le pasamos el archivo limpio al input de verdad y hacemos submit
                finalInput.files = dataTransfer.files;
                cerrarCropModal();
                form.submit();
            }, 'image/jpeg', 0.90);
        };
    });

    // 3. Manejo de la Cámara Web (PC/Tablet)
    let streamVideo = null;
    function abrirCamaraWeb(e) {
        if(e) { e.preventDefault(); e.stopPropagation(); }
        const video = document.getElementById('videoElement');
        document.getElementById('camaraModal').style.display = 'flex';

        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
            .then(function(stream) {
                streamVideo = stream;
                video.srcObject = stream;
            })
            .catch(function(err) {
                alert("No se pudo acceder a la cámara. Verifica los permisos de tu computadora.");
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
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convertimos el frame en foto y lo mandamos al recortador
        const dataUrl = canvas.toDataURL('image/jpeg', 1.0);
        cerrarCamaraWeb();
        abrirCropper(dataUrl);
    }
</script>
</body>
</html>