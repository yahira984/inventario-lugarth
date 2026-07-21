<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $equipo->nombre }} - Equipos</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; font-family:"Segoe UI", Tahoma, sans-serif; background:#eef5fb; color:#08233f; }
        .app-shell { display:flex; min-height:100vh; }
        .app-content { flex:1; padding:32px 20px; overflow-x:hidden; }
        .wrap { width:min(1220px,100%); margin:0 auto; }
        .hero,.card { background:#fff; border:1px solid #cfe0f2; border-radius:18px; box-shadow:0 18px 50px rgba(15,60,105,.10); }
        .hero { padding:26px; display:flex; justify-content:space-between; gap:18px; align-items:flex-start; margin-bottom:18px; }
        h1,h2,h3 { margin:0; }
        h1 { font-size:clamp(24px,4vw,38px); color:#062443; }
        .muted { color:#58718a; font-size:13px; font-weight:700; line-height:1.45; }
        .grid { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(310px,.65fr); gap:18px; align-items:start; }
        .card { padding:20px; margin-bottom:18px; }
        .btn { min-height:42px; border:0; border-radius:10px; padding:0 14px; color:#fff; font-weight:900; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
        .btn-blue { background:linear-gradient(135deg,#0ea5e9,#2563eb); }
        .btn-red { background:linear-gradient(135deg,#ef4444,#b91c1c); }
        .btn-green { background:linear-gradient(135deg,#16a34a,#15803d); }
        .btn-amber { background:linear-gradient(135deg,#f59e0b,#b45309); }
        .btn-soft { background:#e8f2ff; color:#075985; border:1px solid #a8d3ff; }
        .btn:disabled { cursor:not-allowed; opacity:.48; filter:grayscale(.2); transform:none !important; box-shadow:none !important; }
        label { display:block; color:#075985; font-size:12px; font-weight:900; text-transform:uppercase; margin-bottom:7px; }
        input,select,textarea { width:100%; min-height:42px; border:1px solid #bfd2e6; border-radius:10px; padding:10px 12px; font:inherit; color:#08233f; background:#fff; }
        textarea { min-height:78px; resize:vertical; }
        .field { margin-bottom:12px; }
        .form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .full { grid-column:1 / -1; }
        table { width:100%; border-collapse:separate; border-spacing:0 10px; }
        th { text-align:left; color:#075985; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
        td { background:#f8fbff; border-top:1px solid #d8e8f7; border-bottom:1px solid #d8e8f7; padding:12px; vertical-align:top; }
        td:first-child { border-left:1px solid #d8e8f7; border-radius:12px 0 0 12px; }
        td:last-child { border-right:1px solid #d8e8f7; border-radius:0 12px 12px 0; }
        .name { font-weight:950; }
        .pill { display:inline-flex; padding:5px 9px; border-radius:999px; font-size:12px; font-weight:900; }
        .ok { background:#dcfce7; color:#166534; }
        .bad { background:#fee2e2; color:#991b1b; }
        .alert { padding:14px 16px; border-radius:12px; margin-bottom:16px; font-weight:800; }
        .alert-ok { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .alert-bad { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .inline-form { display:grid; gap:8px; min-width:220px; }
        .history { display:grid; gap:10px; }
        .history-row { border:1px solid #d8e8f7; background:#f8fbff; border-radius:12px; padding:12px; }
        .piece-photo { width:82px; height:82px; object-fit:cover; border-radius:12px; border:1px solid #cfe0f2; background:#fff; box-shadow:0 8px 18px rgba(15,60,105,.12); margin-bottom:10px; display:block; }
        .piece-photo-empty { display:flex; align-items:center; justify-content:center; color:#58718a; font-size:11px; font-weight:900; text-transform:uppercase; border-style:dashed; }
        .auto-filled { background:#f1f8ff; color:#34506b; }
        .form-hint { margin:-4px 0 12px; color:#58718a; font-size:12px; font-weight:800; line-height:1.4; }
        .selected-preview { display:none; grid-template-columns:74px minmax(0,1fr); gap:12px; align-items:center; margin:0 0 14px; padding:12px; border:1px solid #b9dcff; border-radius:14px; background:#f1f8ff; }
        .selected-preview.active { display:grid; }
        .selected-preview img, .selected-preview .preview-empty { width:74px; height:74px; object-fit:cover; border-radius:12px; border:1px solid #cfe0f2; background:#fff; }
        .selected-preview .preview-empty { display:flex; align-items:center; justify-content:center; color:#58718a; font-size:11px; font-weight:900; text-transform:uppercase; border-style:dashed; }
        .selected-preview strong { display:block; color:#08233f; font-weight:950; line-height:1.15; }
        .selected-preview span { display:block; margin-top:4px; color:#58718a; font-size:12px; font-weight:800; }
        .stock-check { margin:14px 0; padding:14px; border-radius:12px; border:1px solid; }
        .stock-check strong { display:block; margin-bottom:5px; }
        .stock-check ul { margin:8px 0 0; padding-left:20px; }
        .stock-check li { margin:5px 0; font-size:13px; font-weight:750; line-height:1.4; }
        .stock-check.ok { background:#ecfdf5; border-color:#86efac; color:#166534; }
        .stock-check.bad { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
        @media (max-width: 980px) { .hero,.grid { display:block; } .form-grid { grid-template-columns:1fr; } .btn { width:100%; } table,thead,tbody,tr,td,th { display:block; } th { display:none; } td { border:1px solid #d8e8f7; border-radius:0; } td:first-child { border-radius:12px 12px 0 0; } td:last-child { border-radius:0 0 12px 12px; } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="wrap">
            <section class="hero">
                <div>
                    <h1>{{ $equipo->nombre }}</h1>
                    <p class="muted">{{ $equipo->descripcion ?: 'Receta de piezas por equipo.' }}</p>
                </div>
                <a class="btn btn-soft" href="{{ route('equipos.index') }}">Volver</a>
            </section>

            @if(session('success')) <div class="alert alert-ok">{{ session('success') }}</div> @endif
            @if($errors->any()) <div class="alert alert-bad">{{ $errors->first() }}</div> @endif

            <div class="grid">
                <section>
                    <div class="card">
                        <h2>Piezas requeridas</h2>
                        <p class="muted">La cantidad por paquete significa cuantas piezas ocupa un equipo. Para descontar stock, vincula cada renglon con una pieza real del inventario.</p>
                        <table>
                            <thead>
                                <tr>
                                    <th>Pieza</th>
                                    <th>Cantidad</th>
                                    <th>Inventario vinculado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($equipo->items as $item)
                                    <tr>
                                        <td>
                                            @php($fotoPieza = $item->material?->fotografia ?: $item->fotografia)
                                            @if($fotoPieza)
                                                <img src="{{ asset('storage/' . $fotoPieza) }}" class="piece-photo" alt="Foto de {{ $item->descripcion }}">
                                            @else
                                                <div class="piece-photo piece-photo-empty">Sin foto</div>
                                            @endif
                                            <div class="name">{{ $item->descripcion }}</div>
                                            <div class="muted">{{ $item->numero_parte ?: 'Sin no. parte' }} · {{ $item->marca ?: 'Sin marca' }}</div>
                                            @if($item->apodo)<div class="muted">Apodo: {{ $item->apodo }}</div>@endif
                                        </td>
                                        <td><strong>{{ rtrim(rtrim(number_format((float)$item->cantidad_por_paquete, 2), '0'), '.') }}</strong> {{ $item->unidad ?: 'pza' }}</td>
                                        <td>
                                            @if($item->material)
                                                <span class="pill ok">Vinculada</span>
                                                <div class="muted">{{ $item->material->descripcion }} · Stock {{ $item->material->stock }}</div>
                                            @else
                                                <span class="pill bad">Sin vincular</span>
                                                <div class="muted">No descuenta stock hasta vincularla.</div>
                                            @endif
                                        </td>
                                        <td>
                                            <form class="inline-form" method="POST" action="{{ route('equipos.items.update', [$equipo, $item]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <select name="material_id">
                                                    <option value="">Sin vincular</option>
                                                    @foreach($materiales as $material)
                                                        <option value="{{ $material->id }}" {{ $item->material_id === $material->id ? 'selected' : '' }}>
                                                            {{ $material->descripcion }} {{ $material->apodo ? '(' . $material->apodo . ')' : '' }} · {{ $material->numero_parte ?: 'N/A' }} · Stock {{ $material->stock }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="number" name="cantidad_por_paquete" min="0.01" step="0.01" value="{{ $item->cantidad_por_paquete }}">
                                                <textarea name="notas" placeholder="Notas">{{ $item->notas }}</textarea>
                                                <button class="btn btn-blue" type="submit">Actualizar</button>
                                            </form>
                                            <form method="POST" action="{{ route('equipos.items.destroy', [$equipo, $item]) }}" style="margin-top:8px;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-red" type="submit">Quitar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4"><p class="muted">Aun no hay piezas configuradas.</p></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <aside>
                    <section class="card">
                        <h2>Agregar pieza</h2>
                        <form method="POST" action="{{ route('equipos.items.store', $equipo) }}">
                            @csrf
                            <div class="field">
                                <label>Pieza de inventario real</label>
                                <select name="material_id" id="materialRealSelect">
                                    <option value="">Sin vincular por ahora</option>
                                    @foreach($materiales as $material)
                                        <option value="{{ $material->id }}">{{ $material->descripcion }} {{ $material->apodo ? '(' . $material->apodo . ')' : '' }} · {{ $material->numero_parte ?: 'N/A' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="form-hint">Si seleccionas una pieza real, descripcion, no. parte, apodo, marca y unidad se llenan automaticamente. Solo ajusta la cantidad que usa este equipo y las notas si hacen falta.</p>
                            <div class="selected-preview" id="piezaPreview">
                                <div class="preview-empty" id="piezaPreviewFoto">Sin foto</div>
                                <div>
                                    <strong id="piezaPreviewTitulo">Pieza seleccionada</strong>
                                    <span id="piezaPreviewMeta"></span>
                                </div>
                            </div>
                            <div class="field"><label>Descripcion</label><input name="descripcion" id="piezaDescripcion" placeholder="Ej. Cuello 20 soldable"></div>
                            <div class="form-grid">
                                <div class="field"><label>No. parte</label><input name="numero_parte" id="piezaNumeroParte"></div>
                                <div class="field"><label>Apodo</label><input name="apodo" id="piezaApodo" placeholder="Como lo conocen"></div>
                                <div class="field"><label>Marca</label><input name="marca" id="piezaMarca"></div>
                                <div class="field"><label>Unidad</label><input name="unidad" id="piezaUnidad" placeholder="pza"></div>
                                <div class="field full"><label>Cantidad por paquete</label><input type="number" name="cantidad_por_paquete" min="0.01" step="0.01" value="1" required></div>
                                <div class="field full"><label>Notas</label><textarea name="notas"></textarea></div>
                            </div>
                            <button class="btn btn-green" type="submit">Agregar pieza</button>
                        </form>
                    </section>

                    <section class="card" id="vender-equipo">
                        <h2>Vender equipo</h2>
                        <p class="muted">Registra la venta y descuenta automaticamente el stock de todas las piezas vinculadas al inventario real.</p>
                        <form method="POST" action="{{ route('equipos.withdraw', $equipo) }}" id="ventaEquipoForm">
                            @csrf
                            <div class="field">
                                <label>Tipo de movimiento</label>
                                <select name="tipo" id="tipoMovimientoEquipo" required>
                                    <option value="venta" selected>Venta de equipo</option>
                                    <option value="retiro">Retiro interno</option>
                                </select>
                            </div>
                            <div class="field"><label>Cantidad de equipos</label><input type="number" min="1" name="cantidad_paquetes" id="cantidadEquipos" value="1" required></div>
                            <div class="field"><label>Referencia</label><input name="referencia" placeholder="Pedido, factura, OT o cliente"></div>
                            <div class="field"><label>Notas</label><textarea name="notas" placeholder="Quien lo solicito, cliente o detalle util"></textarea></div>
                            <div class="stock-check" id="estadoStockEquipo" aria-live="polite"></div>
                            <button class="btn btn-red" id="venderEquipoButton" type="submit">Vender equipo y descontar stock</button>
                        </form>
                    </section>

                    <section class="card">
                        <h2>Ventas y retiros recientes</h2>
                        <div class="history">
                            @forelse($equipo->withdrawals->sortByDesc('created_at')->take(8) as $retiro)
                                <div class="history-row">
                                    <strong>{{ ($retiro->tipo ?? 'venta') === 'venta' ? 'Venta' : 'Retiro interno' }}: {{ $retiro->cantidad_paquetes }} equipo(s)</strong>
                                    <div class="muted">{{ $retiro->created_at->format('d/m/Y H:i') }} · {{ $retiro->user?->name ?? 'Usuario no disponible' }}</div>
                                    @if($retiro->referencia)<div class="muted">{{ $retiro->referencia }}</div>@endif
                                </div>
                            @empty
                                <p class="muted">Aun no hay ventas ni retiros.</p>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </main>
</div>
<script>
    const materialesEquipo = @json($materialesEquipo);
    const requisitosVenta = @json($requisitosVenta);
    const piezasSinVincular = @json($piezasSinVincular);

    const materialRealSelect = document.getElementById('materialRealSelect');
    const camposAuto = [
        document.getElementById('piezaDescripcion'),
        document.getElementById('piezaNumeroParte'),
        document.getElementById('piezaApodo'),
        document.getElementById('piezaMarca'),
        document.getElementById('piezaUnidad'),
    ];
    const cantidadEquipos = document.getElementById('cantidadEquipos');
    const tipoMovimientoEquipo = document.getElementById('tipoMovimientoEquipo');
    const estadoStockEquipo = document.getElementById('estadoStockEquipo');
    const venderEquipoButton = document.getElementById('venderEquipoButton');

    function escapeHtml(valor) {
        const nodo = document.createElement('div');
        nodo.textContent = String(valor ?? '');
        return nodo.innerHTML;
    }

    function revisarStockEquipo() {
        const cantidad = Math.max(1, Number.parseInt(cantidadEquipos?.value || '1', 10));
        const faltantes = requisitosVenta
            .map((pieza) => {
                const requerido = Math.ceil(Number(pieza.cantidad_por_equipo || 0) * cantidad);
                const disponible = Number(pieza.stock || 0);

                return { ...pieza, requerido, disponible, faltan: Math.max(requerido - disponible, 0) };
            })
            .filter((pieza) => pieza.faltan > 0);

        if (piezasSinVincular.length > 0) {
            estadoStockEquipo.className = 'stock-check bad';
            estadoStockEquipo.innerHTML = `<strong>No se puede retirar este equipo.</strong><span>Vincula primero las piezas pendientes:</span><ul>${piezasSinVincular.map((pieza) => `<li>${escapeHtml(pieza)}</li>`).join('')}</ul>`;
            venderEquipoButton.disabled = true;
            return;
        }

        if (faltantes.length > 0) {
            estadoStockEquipo.className = 'stock-check bad';
            estadoStockEquipo.innerHTML = `<strong>Stock insuficiente para ${cantidad} equipo(s).</strong><ul>${faltantes.map((pieza) => `<li>${escapeHtml(pieza.descripcion)}: hay ${pieza.disponible}, se requieren ${pieza.requerido} y faltan ${pieza.faltan}.</li>`).join('')}</ul>`;
            venderEquipoButton.disabled = true;
            return;
        }

        estadoStockEquipo.className = 'stock-check ok';
        estadoStockEquipo.innerHTML = `<strong>Stock completo.</strong><span>Hay piezas suficientes para ${cantidad} equipo(s). El descuento se realizara al confirmar.</span>`;
        venderEquipoButton.disabled = requisitosVenta.length === 0;
    }

    function actualizarTipoMovimiento() {
        const esVenta = tipoMovimientoEquipo?.value === 'venta';
        venderEquipoButton.textContent = esVenta
            ? 'Vender equipo y descontar stock'
            : 'Registrar retiro y descontar stock';
        venderEquipoButton.classList.toggle('btn-red', esVenta);
        venderEquipoButton.classList.toggle('btn-amber', !esVenta);
    }

    function aplicarEstadoAutomatico(activo) {
        camposAuto.forEach((campo) => {
            campo.readOnly = activo;
            campo.classList.toggle('auto-filled', activo);
        });
    }

    function llenarDatosDeMaterial() {
        const material = materialesEquipo[materialRealSelect.value];

        if (!material) {
            camposAuto.forEach((campo) => {
                campo.readOnly = false;
                campo.classList.remove('auto-filled');
            });
            document.getElementById('piezaPreview').classList.remove('active');
            return;
        }

        document.getElementById('piezaDescripcion').value = material.descripcion || '';
        document.getElementById('piezaNumeroParte').value = material.numero_parte || '';
        document.getElementById('piezaApodo').value = material.apodo || '';
        document.getElementById('piezaMarca').value = material.marca || '';
        document.getElementById('piezaUnidad').value = material.unidad || 'pza';
        document.getElementById('piezaPreview').classList.add('active');
        document.getElementById('piezaPreviewTitulo').textContent = material.descripcion || 'Pieza seleccionada';
        document.getElementById('piezaPreviewMeta').textContent = `${material.apodo ? 'Apodo: ' + material.apodo + ' - ' : ''}No. parte: ${material.numero_parte || 'N/A'} - Marca: ${material.marca || 'N/A'}`;

        const previewFoto = document.getElementById('piezaPreviewFoto');
        previewFoto.outerHTML = material.fotografia_url
            ? `<img id="piezaPreviewFoto" src="${material.fotografia_url}" alt="Foto de ${material.descripcion || 'pieza'}">`
            : '<div class="preview-empty" id="piezaPreviewFoto">Sin foto</div>';
        aplicarEstadoAutomatico(true);
    }

    materialRealSelect?.addEventListener('change', llenarDatosDeMaterial);
    cantidadEquipos?.addEventListener('input', revisarStockEquipo);
    tipoMovimientoEquipo?.addEventListener('change', actualizarTipoMovimiento);
    llenarDatosDeMaterial();
    revisarStockEquipo();
    actualizarTipoMovimiento();
</script>
</body>
</html>
