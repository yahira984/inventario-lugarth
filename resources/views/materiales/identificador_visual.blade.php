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
            --cyan-glow: #06b6d4;
            --blue-glow: #3b82f6;
            --emerald-glow: #10b981;
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
            background: linear-gradient(to right, #00f2fe, #4facfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(0, 242, 254, 0.2); 
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
        .upload-icon { font-size: 48px; color: var(--cyan-glow); display: block; }
        .upload-title { font-weight: 800; font-size: 18px; margin: 10px 0; display: block; }
        .loading-note { color: var(--cyan-glow); font-weight: bold; display: none; margin-top: 10px; }
        .loading .loading-note { display: block; }

        /* --- PANEL LATERAL --- */
        .side-panel { display: flex; flex-direction: column; gap: 20px; }
        /* --- ESTILO DE LOS BOXES CON BRILLO FUTURISTA --- */
.status-box {
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(56, 189, 248, 0.3); /* Borde con tinte azul */
    border-radius: 16px;
    padding: 20px;
    position: relative;
    transition: all 0.4s ease;
    /* Efecto de brillo sutil en el fondo */
    box-shadow: inset 0 0 20px rgba(6, 182, 212, 0.05);
}

/* Efecto de resplandor al "activarse" */
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
    text-shadow: 0 0 10px rgba(6, 182, 212, 0.5); /* Las letras brillan */
}

.status-box span { 
    font-size: 14px; 
    color: #fff; 
    display: block;
    line-height: 1.4;
}
        /* Chips */
        .chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .chip { background: rgba(56, 189, 248, 0.1); color: #7dd3fc; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid rgba(56, 189, 248, 0.2); }

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
        
        .score-row { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
        .score { color: var(--emerald-glow); font-weight: bold; font-size: 12px; }
        .btn-secondary { background: rgba(255,255,255,0.05); color: #fff; padding: 4px 12px; border-radius: 6px; font-size: 11px; text-decoration: none; border: 1px solid rgba(255,255,255,0.1); }
        .btn-secondary:hover { background: var(--blue-glow); }
        .empty-result, .muted {
            color: var(--muted);
            border: 1px solid rgba(56, 189, 248, 0.22);
            background: rgba(15, 23, 42, 0.5);
            border-radius: 12px;
            padding: 16px;
            font-size: 13px;
            font-weight: 700;
        }

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
                        <label class="drop-area" id="dropArea">
                            @if($preview)
                                <img src="{{ $preview }}" class="main-preview" alt="Foto analizada">
                            @else
                                <span class="upload-state" id="uploadState">
                                    <span class="upload-icon">📷</span>
                                    <span class="upload-title">Tomar foto o subir imagen</span>
                                    <span class="upload-subtitle">JPG, PNG o WEBP</span>
                                    <span class="loading-note">Analizando imagen...</span>
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
                                    <div class="result-title">{{ $material->descripcion }}</div>
                                    <div class="result-meta">
                                        <span>Marca: {{ $material->marca }}</span>
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

<script>
    const input = document.getElementById('fotografia');
    const form = document.getElementById('visualForm');
    const dropArea = document.getElementById('dropArea');
    input.addEventListener('change', () => {
        if (input.files.length) {
            dropArea.classList.add('loading');
            form.submit();
        }
    });
</script>

</body>
</html>
