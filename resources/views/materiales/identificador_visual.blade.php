<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identificador Visual - Inventario</title>
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
            --amber: #b66a08;
            --red: #b42318;
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

        .app-content {
            min-width: 0;
            padding: 24px;
        }

        .container {
            width: min(1180px, 100%);
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0;
            color: var(--blue-dark);
            font-size: 30px;
            line-height: 1.15;
        }

        .header-meta {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
        }

        .scanner {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .scanner-body {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 22px;
            padding: 24px;
            align-items: stretch;
        }

        .drop-area {
            min-height: 360px;
            border: 2px dashed #abc0d4;
            border-radius: 8px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.18s ease, background 0.18s ease;
        }

        .drop-area:hover,
        .drop-area.loading {
            border-color: var(--blue);
            background: #eef6ff;
        }

        .file-input {
            position: absolute;
            inset: 0;
            width: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-state {
            padding: 28px;
            text-align: center;
            pointer-events: none;
        }

        .upload-icon {
            width: 74px;
            height: 74px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: var(--blue);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 900;
        }

        .upload-title {
            display: block;
            font-size: 22px;
            font-weight: 900;
            color: var(--blue-dark);
            margin-bottom: 6px;
        }

        .upload-subtitle {
            display: block;
            color: var(--muted);
            font-weight: 700;
        }

        .main-preview {
            width: 100%;
            height: 100%;
            min-height: 360px;
            object-fit: contain;
            background: #0f172a;
        }

        .side-panel {
            border-left: 1px solid var(--line);
            padding-left: 22px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 14px;
        }

        .status-box {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 15px;
            background: #fff;
        }

        .status-box strong {
            display: block;
            color: var(--blue-dark);
            margin-bottom: 6px;
            font-size: 15px;
        }

        .status-box span,
        .status-box p {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
            margin: 0;
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 10px;
            background: #eef6ff;
            border: 1px solid #b7d9ff;
            color: #17426f;
            font-size: 12px;
            font-weight: 900;
        }

        .error-box {
            border: 1px solid #fecaca;
            background: #fff1f2;
            color: var(--red);
            border-radius: 8px;
            padding: 12px 14px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .results-shell {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .results-header {
            padding: 18px 22px;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .results-header strong {
            color: var(--blue-dark);
            font-size: 18px;
        }

        .result-count {
            color: var(--muted);
            font-size: 13px;
            font-weight: 900;
        }

        .results-body {
            padding: 22px;
        }

        .empty-result {
            border: 1px solid #ffd98a;
            background: #fff7e6;
            color: #855000;
            border-radius: 8px;
            padding: 16px;
            font-weight: 800;
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .result-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(245px, 1fr));
            gap: 14px;
        }

        .result-card {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .result-photo,
        .no-photo {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            background: #f3f6f9;
        }

        .no-photo {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-weight: 900;
        }

        .result-info {
            padding: 14px;
            display: grid;
            gap: 8px;
        }

        .result-title {
            color: var(--ink);
            font-size: 15px;
            font-weight: 900;
            line-height: 1.25;
            min-height: 38px;
        }

        .result-meta {
            display: grid;
            gap: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }

        .score-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 4px;
        }

        .score {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 9px;
            background: #eaf7ef;
            border: 1px solid #b8e2c8;
            color: #11643e;
            font-size: 12px;
            font-weight: 900;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 8px 10px;
            border-radius: 6px;
            background: #e6ecf2;
            color: var(--ink);
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
        }

        .loading-note {
            display: none;
            color: var(--blue-dark);
            font-weight: 900;
        }

        .drop-area.loading .loading-note {
            display: block;
            margin-top: 10px;
        }

        @media (max-width: 980px) {
            .scanner-body {
                grid-template-columns: 1fr;
            }

            .side-panel {
                border-left: none;
                border-top: 1px solid var(--line);
                padding-left: 0;
                padding-top: 18px;
            }
        }

        @media (max-width: 640px) {
            .app-content {
                padding: 14px 10px;
            }

            .page-header {
                display: block;
            }

            h1 {
                font-size: 24px;
            }

            .scanner-body,
            .results-body {
                padding: 16px;
            }

            .drop-area,
            .main-preview {
                min-height: 280px;
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
                <div>
                    <h1>Identificador Visual</h1>
                    <p class="header-meta">Foto de la pieza y sugerencias del inventario.</p>
                </div>
            </div>

            @if($errors->any())
                <div class="error-box">
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
                                    <span class="upload-icon">+</span>
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
                                @if($analisis)
                                    <span>{{ implode(', ', $analisis['observaciones']) ?: 'Imagen procesada' }}</span>
                                    <div class="chips">
                                        @foreach(array_slice(array_keys($analisis['terminos']), 0, 7) as $termino)
                                            <span class="chip">{{ $termino }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <p>Sin imagen analizada.</p>
                                @endif
                            </div>

                            <div class="status-box">
                                <strong>Resultado</strong>
                                @if($busquedaRealizada)
                                    <span>{{ $resultados->count() }} sugerencias encontradas.</span>
                                @else
                                    <span>Selecciona una imagen para iniciar.</span>
                                @endif
                            </div>
                        </aside>
                    </div>
                </form>
            </section>

            <section class="results-shell">
                <div class="results-header">
                    <strong>Sugerencias</strong>
                    <span class="result-count">{{ $busquedaRealizada ? $resultados->count() . ' resultados' : 'esperando imagen' }}</span>
                </div>

                <div class="results-body">
                    @if($busquedaRealizada && $resultados->isEmpty())
                        <div class="empty-result">
                            No encontre una coincidencia clara con esa imagen.
                        </div>
                    @elseif(!$busquedaRealizada)
                        <p class="muted">Aqui apareceran los materiales mas parecidos.</p>
                    @else
                        <div class="result-grid">
                            @foreach($resultados as $material)
                                <article class="result-card">
                                    @if($material->fotografia)
                                        <img src="{{ asset('storage/' . $material->fotografia) }}" class="result-photo" alt="Foto de material">
                                    @else
                                        <div class="no-photo">Sin foto</div>
                                    @endif

                                    <div class="result-info">
                                        <div class="result-title">{{ $material->descripcion }}</div>

                                        <div class="result-meta">
                                            <span>Categoria: {{ $material->categoria ?? 'N/A' }}</span>
                                            <span>No. parte: {{ $material->numero_parte ?? 'N/A' }}</span>
                                            <span>Marca: {{ $material->marca ?? 'N/A' }}</span>
                                            <span>Stock: {{ $material->stock }} pzas</span>
                                        </div>

                                        <div class="score-row">
                                            <span class="score">{{ $material->puntaje_visual }} pts</span>
                                            <a href="{{ route('materiales.edit', $material) }}" class="btn-secondary">Ver</a>
                                        </div>

                                        <div class="muted">{{ implode(', ', $material->motivos_visual) }}</div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </main>
</div>

<script>
    const input = document.getElementById('fotografia');
    const form = document.getElementById('visualForm');
    const dropArea = document.getElementById('dropArea');

    input.addEventListener('change', () => {
        if (!input.files.length) {
            return;
        }

        dropArea.classList.add('loading');
        form.submit();
    });
</script>

</body>
</html>
