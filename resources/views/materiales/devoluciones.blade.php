<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devoluciones y mermas - Inventario</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; font-family:"Segoe UI", Tahoma, sans-serif; background:#eef5fb; color:#08233f; }
        .app-shell { display:flex; min-height:100vh; }
        .app-content { flex:1; padding:32px 20px; overflow-x:hidden; }
        .wrap { width:min(1220px,100%); margin:0 auto; }
        .hero,.panel { background:#fff; border:1px solid #cfe0f2; border-radius:18px; box-shadow:0 18px 50px rgba(15,60,105,.10); }
        .hero { padding:26px; margin-bottom:18px; display:flex; justify-content:space-between; gap:14px; align-items:flex-start; }
        h1,h2 { margin:0; color:#062443; }
        h1 { font-size:clamp(28px,4vw,42px); }
        .muted { color:#58718a; font-size:13px; font-weight:700; line-height:1.45; }
        .grid { display:grid; grid-template-columns:minmax(320px,.8fr) minmax(0,1.2fr); gap:18px; align-items:start; }
        .panel { padding:20px; }
        label { display:block; color:#075985; font-size:12px; font-weight:900; text-transform:uppercase; margin-bottom:7px; }
        input,select,textarea { width:100%; min-height:46px; border:1px solid #bfd2e6; border-radius:11px; padding:12px 14px; font:inherit; color:#08233f; background:#fff; outline:none; }
        input:focus,select:focus,textarea:focus { border-color:#0ea5e9; box-shadow:0 0 0 3px rgba(14,165,233,.16); }
        textarea { min-height:90px; resize:vertical; }
        .field { margin-bottom:14px; }
        .btn { min-height:44px; border:0; border-radius:11px; padding:0 16px; color:#fff; font-weight:900; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
        .btn-blue { background:linear-gradient(135deg,#0ea5e9,#2563eb); }
        .btn-green { background:linear-gradient(135deg,#16a34a,#15803d); }
        .btn-red { background:linear-gradient(135deg,#ef4444,#b91c1c); }
        .search { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:10px; margin-bottom:14px; }
        .selected-card { display:none; grid-template-columns:78px minmax(0,1fr); gap:12px; align-items:center; padding:12px; border:1px solid #a7f3d0; background:#ecfdf5; border-radius:14px; margin-bottom:15px; }
        .selected-card.active { display:grid; }
        .photo,.no-photo { width:78px; height:78px; border-radius:12px; border:1px solid #cfe0f2; object-fit:cover; background:#fff; }
        .no-photo { display:flex; align-items:center; justify-content:center; color:#58718a; font-size:11px; font-weight:900; text-transform:uppercase; border-style:dashed; }


        /* Imagenes ampliables: conserva el tamaño actual y agrega el visor */
        .photo-zoom-btn {
            appearance:none;
            border:0;
            background:transparent;
            padding:0;
            cursor:zoom-in;
            border-radius:12px;
            position:relative;
            display:inline-flex;
            align-items:center;
            justify-content:center;
        }

        .photo-zoom-btn::after {
            content:"Ver";
            position:absolute;
            right:-7px;
            bottom:-7px;
            padding:4px 8px;
            border-radius:999px;
            background:linear-gradient(135deg,#0ea5e9,#2563eb);
            color:#fff;
            font-size:10px;
            font-weight:900;
            opacity:0;
            transform:translateY(4px) scale(.92);
            transition:opacity .22s ease,transform .22s ease;
            box-shadow:0 8px 20px rgba(37,99,235,.35);
            pointer-events:none;
        }

        .photo-zoom-btn:hover .photo,
        .photo-zoom-btn:focus-visible .photo {
            transform:scale(1.06);
            border-color:#38bdf8;
            box-shadow:0 0 0 4px rgba(56,189,248,.18),0 12px 30px rgba(14,165,233,.28);
        }

        .photo-zoom-btn:hover::after,
        .photo-zoom-btn:focus-visible::after {
            opacity:1;
            transform:translateY(0) scale(1);
        }

        .photo {
            object-fit:contain;
            background-color:#ffffff;
            padding:2px;
            transition:transform .22s ease,border-color .22s ease,box-shadow .22s ease;
        }

        .image-viewer {
            position:fixed;
            inset:0;
            z-index:9999;
            display:none;
            align-items:center;
            justify-content:center;
            padding:28px;
            background:radial-gradient(circle at 50% 20%,rgba(56,189,248,.18),transparent 34%),rgba(2,6,23,.82);
            backdrop-filter:blur(14px);
            -webkit-backdrop-filter:blur(14px);
            opacity:0;
            transition:opacity .24s ease;
        }

        .image-viewer.open {
            display:flex;
            opacity:1;
        }

        .image-viewer-panel {
            width:min(920px,94vw);
            max-height:92vh;
            padding:16px;
            border-radius:24px;
            background:linear-gradient(145deg,rgba(255,255,255,.96),rgba(239,246,255,.92));
            border:1px solid rgba(191,219,254,.95);
            box-shadow:0 28px 80px rgba(2,6,23,.55);
            transform:translateY(18px) scale(.96);
            transition:transform .28s cubic-bezier(.16,1,.3,1);
        }

        .image-viewer.open .image-viewer-panel {
            transform:translateY(0) scale(1);
        }

        .image-viewer-top {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            padding:4px 4px 14px;
        }

        .image-viewer-title { min-width:0; }

        .image-viewer-title strong {
            display:block;
            color:#08233f;
            font-size:clamp(18px,3vw,26px);
            font-weight:950;
            line-height:1.1;
            overflow-wrap:anywhere;
        }

        .image-viewer-title span {
            display:block;
            margin-top:4px;
            color:#52708f;
            font-size:13px;
            font-weight:800;
        }

        .image-viewer-close {
            width:44px;
            height:44px;
            border:0;
            border-radius:14px;
            cursor:pointer;
            background:#0f172a;
            color:#fff;
            font-size:24px;
            line-height:1;
            font-weight:900;
            box-shadow:0 12px 28px rgba(15,23,42,.28);
            transition:transform .2s ease,background .2s ease;
        }

        .image-viewer-close:hover {
            transform:translateY(-2px) scale(1.04);
            background:#1d4ed8;
        }

        .image-viewer-frame {
            border-radius:20px;
            background:linear-gradient(45deg,rgba(14,165,233,.12),rgba(37,99,235,.05)),#fff;
            padding:12px;
            min-height:220px;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
        }

        .image-viewer-frame img {
            max-width:100%;
            max-height:min(72vh,720px);
            object-fit:contain;
            border-radius:16px;
            box-shadow:0 18px 45px rgba(15,23,42,.2);
            animation:imagePop .34s cubic-bezier(.16,1,.3,1);
        }

        @keyframes imagePop {
            from { opacity:0; transform:scale(.92); }
            to { opacity:1; transform:scale(1); }
        }
        .material-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:12px; max-height:620px; overflow:auto; padding-right:4px; }
        .material-card { display:grid; grid-template-columns:74px minmax(0,1fr); gap:11px; align-items:center; border:1px solid #cfe0f2; border-radius:14px; background:#fff; padding:12px; }
        .material-title { font-weight:950; line-height:1.15; }
        .pill { display:inline-flex; width:fit-content; margin-top:6px; padding:5px 9px; border-radius:999px; background:#dcfce7; color:#166534; font-size:12px; font-weight:900; }
        .history { display:grid; gap:10px; margin-top:12px; }
        .history-row { display:grid; grid-template-columns:62px minmax(0,1fr); gap:10px; align-items:center; border:1px solid #d8e8f7; background:#f8fbff; border-radius:12px; padding:10px; }
        .alert { padding:14px 16px; border-radius:12px; margin-bottom:16px; font-weight:800; }
        .alert-ok { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .alert-bad { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        @media (max-width: 940px) { .hero,.grid { display:block; } .panel { margin-bottom:16px; } .search { grid-template-columns:1fr; } .btn { width:100%; } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="wrap">
            <section class="hero">
                <div>
                    <h1>Devoluciones y mermas</h1>
                    <p class="muted">Regresa piezas al inventario o registra scrap con evidencia fotografica para auditoria.</p>
                </div>
                <a class="btn btn-soft" href="{{ route('materiales.index') }}">Volver al inventario</a>
            </section>

            @if(session('success')) <div class="alert alert-ok">{{ session('success') }}</div> @endif
            @if($errors->any()) <div class="alert alert-bad">{{ $errors->first() }}</div> @endif

            <div class="grid">
                <section class="panel">
                    <h2>Movimiento</h2>
                    <p class="muted">Selecciona el material de la derecha. La devolucion suma stock; la merma descuenta stock y pide foto.</p>
                    <form method="POST" action="{{ route('materiales.devoluciones.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="material_id" id="material_id" value="{{ old('material_id') }}">

                        <div class="selected-card" id="selectedCard">
                            <div id="selectedPhoto" class="no-photo">Sin foto</div>
                            <div>
                                <strong id="selectedName">Material seleccionado</strong>
                                <div class="muted" id="selectedMeta"></div>
                            </div>
                        </div>

                        <div class="field">
                            <label>Tipo</label>
                            <select name="tipo" id="tipoMovimiento" required>
                                <option value="devolucion" {{ old('tipo') === 'devolucion' ? 'selected' : '' }}>Devolucion: regresa al stock</option>
                                <option value="merma" {{ old('tipo') === 'merma' ? 'selected' : '' }}>Merma / scrap: baja por daño</option>
                            </select>
                        </div>
                        <div class="field"><label>Cantidad</label><input type="number" name="cantidad" min="1" value="{{ old('cantidad', 1) }}" required></div>
                        <div class="field"><label>Referencia</label><input name="referencia" value="{{ old('referencia') }}" placeholder="OT, tecnico, factura, reporte"></div>
                        <div class="field"><label>Motivo</label><textarea name="motivo" placeholder="Ej. devolvio sobrante, pieza rota, defecto de proveedor">{{ old('motivo') }}</textarea></div>
                        <div class="field" id="evidenciaField">
                            <label>Foto de evidencia</label>
                            <input type="file" name="evidencia_foto" accept="image/*" capture="environment">
                            <p class="muted">Obligatoria para merma/scrap.</p>
                        </div>
                        <button class="btn btn-green" type="submit" id="submitMovimiento">Registrar devolucion</button>
                    </form>
                </section>

                <section class="panel">
                    <form class="search" method="GET" action="{{ route('materiales.devoluciones.create') }}">
                        <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Buscar por descripcion, apodo, no. parte, codigo o almacen">
                        <button class="btn btn-blue" type="submit">Buscar</button>
                    </form>

                    <div class="material-grid">
                        @forelse($materiales as $material)
                            <article class="material-card">
                                @if($material->fotografia)
                                    <button
                                        type="button"
                                        class="photo-zoom-btn"
                                        data-workspace-lightbox
                                        data-lightbox-title="{{ $material->descripcion }}"
                                        data-lightbox-caption="Apodo / código: {{ $material->apodo ?: $material->numero_parte ?: 'Sin apodo' }}"
                                        aria-label="Ver foto de {{ $material->descripcion }}"
                                    >
                                        <img src="{{ asset('storage/' . $material->fotografia) }}" class="photo" alt="Foto de {{ $material->descripcion }}">
                                    </button>
                                @else
                                    <div class="no-photo">Sin foto</div>
                                @endif
                                <div>
                                    <div class="material-title">{{ $material->descripcion }}</div>
                                    <div class="muted">{{ $material->apodo ? 'Apodo: '.$material->apodo.' - ' : '' }}{{ $material->numero_parte ?: 'N/A' }}</div>
                                    <div class="muted">Almacen: {{ $material->almacen ?: 'Sin definir' }}</div>
                                    <span class="pill">{{ $material->stock }} pzas</span>
                                    <button type="button" class="btn btn-blue" style="width:100%;margin-top:8px;" onclick="seleccionarMaterial(this)" data-id="{{ $material->id }}" data-nombre="{{ $material->descripcion }}" data-meta="{{ ($material->apodo ? 'Apodo: '.$material->apodo.' - ' : '').'No. parte: '.($material->numero_parte ?: 'N/A').' - Stock: '.$material->stock.' pzas' }}" data-foto="{{ $material->fotografia ? asset('storage/' . $material->fotografia) : '' }}">Seleccionar</button>
                                </div>
                            </article>
                        @empty
                            <p class="muted">No se encontraron materiales.</p>
                        @endforelse
                    </div>

                    <h2 style="margin-top:20px;">Movimientos recientes</h2>
                    <div class="history">
                        @forelse($movimientosRecientes as $movimiento)
                            <div class="history-row">
                                @if($movimiento->evidencia_foto)
                                    <button
                                        type="button"
                                        class="photo-zoom-btn"
                                        data-workspace-lightbox
                                        data-lightbox-title="{{ $movimiento->tipo === 'merma' ? 'Evidencia de merma' : 'Evidencia de devolución' }}"
                                        data-lightbox-caption="{{ $movimiento->material?->descripcion ?? 'Material eliminado' }}"
                                        aria-label="Ver evidencia del movimiento"
                                    >
                                        <img src="{{ asset('storage/' . $movimiento->evidencia_foto) }}" class="photo" alt="Evidencia del movimiento">
                                    </button>
                                @elseif($movimiento->material?->fotografia)
                                    <button
                                        type="button"
                                        class="photo-zoom-btn"
                                        data-workspace-lightbox
                                        data-lightbox-title="{{ $movimiento->material?->descripcion ?? 'Foto del material' }}"
                                        data-lightbox-caption="{{ $movimiento->tipo === 'merma' ? 'Merma' : 'Devolución' }}"
                                        aria-label="Ver foto del material"
                                    >
                                        <img src="{{ asset('storage/' . $movimiento->material->fotografia) }}" class="photo" alt="Foto del material">
                                    </button>
                                @else
                                    <div class="no-photo">Sin foto</div>
                                @endif
                                <div>
                                    <strong>{{ $movimiento->tipo === 'merma' ? 'Merma' : 'Devolucion' }}: {{ $movimiento->cantidad }} pzas</strong>
                                    <div class="muted">{{ $movimiento->material?->descripcion ?? 'Material eliminado' }}</div>
                                    <div class="muted">{{ $movimiento->created_at->format('d/m/Y H:i') }} - {{ $movimiento->user?->name ?? 'Usuario no disponible' }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="muted">Aun no hay devoluciones ni mermas.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>

<script>
    const tipoMovimiento = document.getElementById('tipoMovimiento');
    const submitMovimiento = document.getElementById('submitMovimiento');

    function crearFotoSeleccionada(src, titulo, subtitulo) {
        const fotoActual = document.getElementById('selectedPhoto');

        if (!src) {
            fotoActual.outerHTML = '<div id="selectedPhoto" class="no-photo">Sin foto</div>';
            return;
        }

        const boton = document.createElement('button');
        boton.type = 'button';
        boton.id = 'selectedPhoto';
        boton.className = 'photo-zoom-btn';
        boton.dataset.workspaceLightbox = src;
        boton.dataset.lightboxTitle = titulo || 'Foto del material';
        boton.dataset.lightboxCaption = subtitulo || '';
        boton.setAttribute('aria-label', `Ver foto de ${titulo || 'material'}`);

        const imagen = document.createElement('img');
        imagen.src = src;
        imagen.className = 'photo';
        imagen.alt = `Foto de ${titulo || 'material'}`;

        boton.appendChild(imagen);
        fotoActual.replaceWith(boton);
    }

    function actualizarTipo() {
        const esMerma = tipoMovimiento.value === 'merma';
        submitMovimiento.textContent = esMerma ? 'Registrar merma y descontar stock' : 'Registrar devolucion';
        submitMovimiento.classList.toggle('btn-red', esMerma);
        submitMovimiento.classList.toggle('btn-green', !esMerma);
    }

    function seleccionarMaterial(button) {
        document.getElementById('material_id').value = button.dataset.id;
        document.getElementById('selectedName').textContent = button.dataset.nombre;
        document.getElementById('selectedMeta').textContent = button.dataset.meta;
        document.getElementById('selectedCard').classList.add('active');
        crearFotoSeleccionada(button.dataset.foto, button.dataset.nombre, button.dataset.meta);
    }

    tipoMovimiento.addEventListener('change', actualizarTipo);

    actualizarTipo();
</script>
</body>
</html>
