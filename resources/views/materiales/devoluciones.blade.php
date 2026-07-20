<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devoluciones y mermas - Inventario</title>
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
                                    <img src="{{ asset('storage/' . $material->fotografia) }}" class="photo" alt="Foto">
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
                                    <img src="{{ asset('storage/' . $movimiento->evidencia_foto) }}" class="photo" alt="Evidencia">
                                @elseif($movimiento->material?->fotografia)
                                    <img src="{{ asset('storage/' . $movimiento->material->fotografia) }}" class="photo" alt="Foto">
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
        document.getElementById('selectedPhoto').outerHTML = button.dataset.foto
            ? `<img src="${button.dataset.foto}" id="selectedPhoto" class="photo" alt="Foto">`
            : '<div id="selectedPhoto" class="no-photo">Sin foto</div>';
    }

    tipoMovimiento.addEventListener('change', actualizarTipo);
    actualizarTipo();
</script>
</body>
</html>
