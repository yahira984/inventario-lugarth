<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestamos de herramientas - Inventario Lugarth</title>
    <style>
        .loan-page { width: min(1680px, 100%); margin: 0 auto; padding: 0 8px 30px; }
        .loan-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding: 22px 0; border-bottom: 1px solid var(--ws-line); }
        .loan-header h1, .loan-panel h2, .loan-card h3 { margin: 0; }
        .loan-header p, .loan-panel > p { margin: 7px 0 0; color: var(--ws-muted); line-height: 1.5; }
        .loan-actions, .loan-tabs, .loan-form-actions, .loan-return-actions { display: flex; gap: 9px; flex-wrap: wrap; align-items: center; }
        .loan-layout { display: grid; grid-template-columns: minmax(420px, 460px) minmax(0, 1fr); gap: 24px; margin-top: 22px; align-items: start; }
        .loan-panel { padding: 22px; background: #fff; border: 1px solid var(--ws-line); border-radius: 10px; box-shadow: var(--ws-shadow-sm); }
        .loan-form, .loan-field, .loan-selected-list, .loan-list { display: grid; gap: 14px; }
        .loan-grid { display: grid; grid-template-columns: 1fr; gap: 14px; }
        .loan-field { gap: 6px; }
        .loan-field.full { grid-column: 1 / -1; }
        .loan-field label { font-size: 12px; font-weight: 800; color: var(--ws-ink); }
        .loan-help { margin: 0; color: var(--ws-muted); font-size: 12px; line-height: 1.45; }
        .loan-draft { display: grid; grid-template-columns: minmax(0, 1fr) 92px; gap: 9px; }
        .loan-draft > :nth-child(1) { grid-column: 1 / -1; }
        .loan-draft > :nth-child(2) { grid-column: 1; }
        .loan-draft > :nth-child(3) { grid-column: 2; }
        .loan-draft > button { grid-column: 1 / -1; justify-self: start; }
        .loan-selection, .loan-item, .loan-return-item { padding: 10px; border: 1px solid #d9e5ef; border-radius: 8px; background: #f8fbfe; }
        .loan-selection { display: grid; grid-template-columns: minmax(0, 1fr) 86px auto; align-items: center; gap: 9px; }
        .loan-selection strong { display: block; font-size: 13px; }
        .loan-selection small, .loan-meta, .loan-item-meta { color: var(--ws-muted); font-size: 12px; line-height: 1.4; }
        .loan-selection input[type="number"] { text-align: center; }
        .loan-remove { width: 36px; min-height: 36px; padding: 0; border: 1px solid #fecaca; border-radius: 7px; color: #b42336; background: #fff0f2; font-size: 18px; cursor: pointer; }
        .loan-empty { padding: 22px; border: 1px dashed #bfd1df; border-radius: 8px; color: var(--ws-muted); text-align: center; font-size: 13px; }
        .loan-alert { margin: 14px 0; padding: 12px 14px; border: 1px solid; border-radius: 8px; font-size: 13px; font-weight: 700; }
        .loan-alert.success { color: #076b4c; background: #ecfdf5; border-color: #a7f3d0; }
        .loan-alert.error { color: #a7182a; background: #fff0f2; border-color: #fecdd3; }
        .loan-status-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin: 18px 0; }
        .loan-stat { padding: 13px; color: var(--ws-ink); background: #fff; border: 1px solid var(--ws-line); border-radius: 8px; text-decoration: none; }
        .loan-stat strong { display: block; font-size: 24px; }
        .loan-stat span { display: block; margin-top: 3px; color: var(--ws-muted); font-size: 12px; font-weight: 700; }
        .loan-stat.active { border-color: #f4c778; background: #fffaf0; }.loan-stat.repair { border-color: #c6b5ff; background: #f7f4ff; }.loan-stat.closed { border-color: #a7e7c9; background: #f0fdf7; }
        .loan-filter { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 9px; margin: 14px 0; }
        .loan-tabs a { padding: 7px 10px; border: 1px solid #bfd3e4; border-radius: 999px; color: #315775; background: #fff; text-decoration: none; font-size: 12px; font-weight: 800; }
        .loan-tabs a.active { color: #fff; border-color: var(--ws-blue); background: var(--ws-blue); }
        .loan-card { overflow: hidden; border: 1px solid var(--ws-line); border-left: 4px solid var(--ws-amber); border-radius: 8px; background: #fff; }
        .loan-card.status-reparacion { border-left-color: var(--ws-purple); }.loan-card.status-devuelto { border-left-color: var(--ws-green); }
        .loan-card-head { display: flex; justify-content: space-between; gap: 12px; padding: 14px 14px 10px; }
        .loan-chip { display: inline-flex; align-items: center; min-height: 26px; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 900; white-space: nowrap; }
        .loan-chip.active { color: #9a4e02; background: #fff1cf; }.loan-chip.repair { color: #5b2da5; background: #eee7ff; }.loan-chip.closed { color: #076b4c; background: #dff9eb; }
        .loan-item-list { display: grid; gap: 7px; padding: 0 14px 14px; }
        .loan-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 10px; }
        .loan-item-title { display: block; font-size: 14px; }
        .loan-item-count { text-align: right; color: #46617c; font-size: 12px; line-height: 1.45; }
        .loan-item-count strong { display: block; color: var(--ws-ink); font-size: 15px; }
        .loan-evidence { display: flex; align-items: center; gap: 9px; margin: 0 14px 14px; padding: 9px; border-radius: 7px; color: var(--ws-muted); background: #f7fafc; font-size: 12px; }
        .loan-evidence img { width: 48px; height: 48px; object-fit: cover; border: 1px solid #cbdceb; border-radius: 6px; cursor: zoom-in; }
        .loan-return-form { padding: 18px; border-top: 1px solid var(--ws-line); background: #fbfdff; }.loan-return-form h4 { margin: 0 0 8px; }
        .loan-return-grid { display: grid; gap: 10px; }.loan-return-item { display: grid; grid-template-columns: 88px minmax(0, 1fr); align-items: center; gap: 9px; padding: 12px; }
        .loan-return-item label { grid-column: 1 / -1; font-size: 13px; font-weight: 800; }.loan-return-item small { display: block; margin-top: 3px; color: var(--ws-muted); font-size: 11px; }
        .loan-return-bottom { display: grid; grid-template-columns: 1fr; gap: 12px; margin-top: 14px; }
        .loan-return-actions { justify-content: flex-end; margin-top: 14px; }
        @media (max-width: 1180px) { .loan-layout { grid-template-columns: 1fr; }.loan-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }.loan-field.full { grid-column: 1 / -1; } }
        @media (max-width: 700px) { .loan-page { padding-inline: 0; }.loan-header { display: block; }.loan-actions { margin-top: 12px; }.loan-grid, .loan-return-bottom, .loan-status-grid { grid-template-columns: 1fr; }.loan-draft { grid-template-columns: 1fr 76px; }.loan-selection { grid-template-columns: minmax(0, 1fr) 70px auto; }.loan-filter { grid-template-columns: 1fr; }.loan-card-head { display: block; }.loan-card-head .loan-chip { margin-top: 8px; }.loan-return-item { grid-template-columns: 72px minmax(0, 1fr); }.loan-return-actions .btn { width: 100%; } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="loan-page">
            <header class="loan-header">
                <div>
                    <h1>Prestamos de herramientas</h1>
                    <p>Control sencillo de herramientas prestadas a empleados. Este apartado es independiente: no modifica el inventario ni el stock.</p>
                </div>
                <div class="loan-actions">
                    <a class="btn workspace-action-teal" href="{{ route('prestamos.report.csv') }}">Descargar reporte CSV</a>
                </div>
            </header>

            @if(session('success'))
                <div class="loan-alert success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="loan-alert error"><strong>Revisa el registro:</strong> {{ $errors->first() }}</div>
            @endif

            <div class="loan-status-grid">
                <a class="loan-stat active" href="{{ route('prestamos.index', ['estado' => 'activos']) }}"><strong>{{ $statusCounts['activos'] }}</strong><span>Prestamos activos</span></a>
                <a class="loan-stat repair" href="{{ route('prestamos.index', ['estado' => 'reparacion']) }}"><strong>{{ $statusCounts['reparacion'] }}</strong><span>En reparacion</span></a>
                <a class="loan-stat closed" href="{{ route('prestamos.index', ['estado' => 'devueltos']) }}"><strong>{{ $statusCounts['devueltos'] }}</strong><span>Prestamos cerrados</span></a>
            </div>

            <div class="loan-layout">
                <section class="loan-panel">
                    <h2>Registrar prestamo</h2>
                    <p class="loan-help">Escribe manualmente las herramientas y sus cantidades. Puedes agregar varias en un mismo prestamo.</p>
                    <form class="loan-form" method="POST" action="{{ route('prestamos.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="loan-grid">
                            <div class="loan-field full"><label for="employee_name">Empleado que recibe *</label><input id="employee_name" name="employee_name" required value="{{ old('employee_name') }}" placeholder="Nombre completo"></div>
                            <div class="loan-field"><label for="employee_code">No. de empleado</label><input id="employee_code" name="employee_code" value="{{ old('employee_code') }}" placeholder="Opcional"></div>
                            <div class="loan-field"><label for="employee_area">Area o departamento</label><input id="employee_area" name="employee_area" value="{{ old('employee_area') }}" placeholder="Ej. Mantenimiento"></div>
                            <div class="loan-field"><label for="taken_at">Fecha y hora de entrega *</label><input id="taken_at" type="datetime-local" name="taken_at" required value="{{ old('taken_at', now()->format('Y-m-d\\TH:i')) }}"></div>
                            <div class="loan-field"><label for="expected_return_at">Regreso estimado</label><input id="expected_return_at" type="datetime-local" name="expected_return_at" value="{{ old('expected_return_at') }}"></div>
                            <div class="loan-field full"><label for="notes">Motivo o notas</label><textarea id="notes" name="notes" rows="3" placeholder="Trabajo a realizar, orden, ubicacion o detalle util">{{ old('notes') }}</textarea></div>
                        </div>

                        <div class="loan-field">
                            <label>Herramientas prestadas *</label>
                            <div class="loan-draft">
                                <input id="toolDraftName" type="text" placeholder="Ej. Taladro Bosch">
                                <input id="toolDraftDetail" type="text" placeholder="Serie, color o detalle opcional">
                                <input id="toolDraftQuantity" type="number" min="1" value="1" aria-label="Cantidad">
                                <button type="button" class="btn workspace-action-amber" id="addLoanTool">Agregar</button>
                            </div>
                            <p class="loan-help">Las herramientas se registran por nombre, sin depender del catalogo de inventario.</p>
                        </div>

                        <div id="loanToolList" class="loan-selected-list"><div class="loan-empty">Aun no agregas herramientas al prestamo.</div></div>
                        <div class="loan-field"><label for="evidence_out">Foto de entrega *</label><input id="evidence_out" type="file" name="evidence_out" accept="image/jpeg,image/png,image/webp" capture="environment" required><p class="loan-help">En celular puedes tomarla con la camara. La imagen se comprime automaticamente.</p></div>
                        <div class="loan-form-actions"><button class="btn workspace-action-amber" type="submit">Registrar prestamo</button></div>
                    </form>
                </section>

                <section class="loan-panel">
                    <div class="loan-tabs" aria-label="Estado de prestamos">
                        @foreach(['activos' => 'Activos', 'reparacion' => 'En reparacion', 'devueltos' => 'Cerrados', 'todos' => 'Todos'] as $key => $label)
                            <a href="{{ route('prestamos.index', array_filter(['estado' => $key, 'buscar' => $search])) }}" class="{{ $status === $key ? 'active' : '' }}">{{ $label }}@if($key !== 'todos') ({{ $statusCounts[$key] }})@endif</a>
                        @endforeach
                    </div>
                    <form class="loan-filter" method="GET" action="{{ route('prestamos.index') }}">
                        <input type="hidden" name="estado" value="{{ $status }}">
                        <input type="search" name="buscar" value="{{ $search }}" placeholder="Empleado, area o herramienta">
                        <button class="btn workspace-action-teal" type="submit">Buscar</button>
                    </form>

                    <div class="loan-list">
                        @forelse($loans as $loan)
                            @php
                                $chipClass = $loan->status === 'activo' ? 'active' : ($loan->status === 'reparacion' ? 'repair' : 'closed');
                                $chipText = $loan->status === 'activo' ? 'Prestamo activo' : ($loan->status === 'reparacion' ? 'En reparacion' : 'Cerrado');
                            @endphp
                            <article class="loan-card status-{{ $loan->status }}">
                                <header class="loan-card-head">
                                    <div>
                                        <h3>Prestamo #{{ $loan->id }} - {{ $loan->employee_name }}</h3>
                                        <p class="loan-meta">
                                            {{ $loan->employee_code ? 'No. '.$loan->employee_code.' - ' : '' }}{{ $loan->employee_area ?: 'Sin area indicada' }}<br>
                                            Entregado: {{ $loan->taken_at?->format('d/m/Y H:i') }}
                                            @if($loan->expected_return_at)
                                                - Regreso estimado: {{ $loan->expected_return_at->format('d/m/Y H:i') }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="loan-chip {{ $chipClass }}">{{ $chipText }}</span>
                                </header>
                                <div class="loan-item-list">
                                    @foreach($loan->items as $item)
                                        <div class="loan-item">
                                            <div>
                                                <strong class="loan-item-title">{{ $item->tool_name }}</strong>
                                                @if($item->tool_detail)
                                                    <span class="loan-item-meta">{{ $item->tool_detail }}</span>
                                                @endif
                                            </div>
                                            <div class="loan-item-count">
                                                <strong>{{ $item->quantity_loaned }} prestadas</strong>
                                                <span>Pendientes: {{ $item->pendingQuantity() }}</span>
                                                @if($item->pendingRepairQuantity() > 0)
                                                    <span>Reparacion: {{ $item->pendingRepairQuantity() }}</span>
                                                @endif
                                                @if($item->quantity_lost > 0)
                                                    <span style="color:#b42336">Perdidas: {{ $item->quantity_lost }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if($loan->evidence_out || $loan->evidence_return)
                                    <div class="loan-evidence">
                                        @if($loan->evidence_out)
                                            <img data-workspace-lightbox data-lightbox-title="Evidencia de entrega - Prestamo #{{ $loan->id }}" src="{{ asset('storage/'.$loan->evidence_out) }}" alt="Evidencia de entrega"><span>Foto de entrega</span>
                                        @endif
                                        @if($loan->evidence_return)
                                            <img data-workspace-lightbox data-lightbox-title="Evidencia de regreso - Prestamo #{{ $loan->id }}" src="{{ asset('storage/'.$loan->evidence_return) }}" alt="Evidencia de regreso"><span>Foto de regreso</span>
                                        @endif
                                    </div>
                                @endif
                                @if($loan->notes)
                                    <div class="loan-evidence"><span><strong>Notas de entrega:</strong> {{ $loan->notes }}</span></div>
                                @endif

                                @if($loan->status === 'activo')
                                    <form class="loan-return-form" method="POST" action="{{ route('prestamos.return', $loan) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PATCH')
                                        <h4>Registrar regreso</h4>
                                        <p class="loan-help" style="margin:0 0 9px">Indica solamente las herramientas que regresaron ahora y su estado. Esto no modifica el inventario.</p>
                                        <div class="loan-return-grid">
                                            @foreach($loan->items->filter(fn ($item) => $item->pendingQuantity() > 0) as $item)
                                                <div class="loan-return-item"><label>{{ $item->tool_name }}<small>Pendientes: {{ $item->pendingQuantity() }}</small></label><input type="number" min="0" max="{{ $item->pendingQuantity() }}" name="returns[{{ $item->id }}][quantity]" value="0" aria-label="Cantidad devuelta de {{ $item->tool_name }}"><select name="returns[{{ $item->id }}][condition]" aria-label="Estado de {{ $item->tool_name }}"><option value="bueno">Buen estado</option><option value="reparacion">Requiere reparacion</option><option value="perdida">No regreso</option></select></div>
                                            @endforeach
                                        </div>
                                        <div class="loan-return-bottom"><div class="loan-field"><label>Fecha y hora de regreso *</label><input type="datetime-local" name="returned_at" required value="{{ now()->format('Y-m-d\\TH:i') }}"></div><div class="loan-field"><label>Foto de regreso</label><input type="file" name="evidence_return" accept="image/jpeg,image/png,image/webp" capture="environment"></div><div class="loan-field full"><label>Notas de regreso o reparacion</label><textarea name="return_notes" rows="2" placeholder="Dano, envio a taller, pieza faltante o detalle util"></textarea></div></div>
                                        <div class="loan-return-actions"><button class="btn workspace-action-green" type="submit">Registrar regreso</button></div>
                                    </form>
                                @elseif($loan->status === 'reparacion')
                                    <form class="loan-return-form" method="POST" action="{{ route('prestamos.repair.complete', $loan) }}">
                                        @csrf
                                        @method('PATCH')
                                        <h4>Reparacion terminada</h4><p class="loan-help">Al confirmar, este registro queda cerrado. El inventario no se modifica.</p>
                                        <div class="loan-field"><label>Detalle de reparacion</label><textarea name="repair_notes" rows="2" placeholder="Taller, trabajo realizado o referencia"></textarea></div>
                                        <div class="loan-return-actions"><button class="btn workspace-action-purple" type="submit">Marcar reparacion terminada</button></div>
                                    </form>
                                @elseif($loan->return_notes || $loan->repair_notes)
                                    <div class="loan-evidence"><span><strong>Cierre:</strong> {{ $loan->repair_notes ?: $loan->return_notes }}</span></div>
                                @endif
                            </article>
                        @empty
                            <div class="loan-empty"><strong>No hay prestamos en este estado.</strong><br>Al registrar una entrega o un regreso aparecera aqui.</div>
                        @endforelse
                    </div>
                    <div style="margin-top:16px">{{ $loans->links() }}</div>
                </section>
            </div>
        </div>
    </main>
</div>
<script>
(() => {
    const nameInput = document.getElementById('toolDraftName');
    const detailInput = document.getElementById('toolDraftDetail');
    const quantityInput = document.getElementById('toolDraftQuantity');
    const addButton = document.getElementById('addLoanTool');
    const list = document.getElementById('loanToolList');
    const selected = new Map();
    const escapeHtml = (value) => String(value || '').replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[character]);
    const render = () => {
        if (!selected.size) { list.innerHTML = '<div class="loan-empty">Aun no agregas herramientas al prestamo.</div>'; return; }
        list.innerHTML = [...selected.values()].map((item) => `<div class="loan-selection"><span><input type="hidden" name="items[${item.id}][tool_name]" value="${escapeHtml(item.name)}"><input type="hidden" name="items[${item.id}][tool_detail]" value="${escapeHtml(item.detail)}"><strong>${escapeHtml(item.name)}</strong>${item.detail ? `<small>${escapeHtml(item.detail)}</small>` : ''}</span><input type="number" min="1" name="items[${item.id}][quantity]" value="${item.quantity}" data-quantity="${item.id}" aria-label="Cantidad de ${escapeHtml(item.name)}"><button type="button" class="loan-remove" data-remove="${item.id}" aria-label="Quitar ${escapeHtml(item.name)}">x</button></div>`).join('');
    };
    const addTool = () => {
        const name = nameInput?.value.trim();
        if (!name) { nameInput?.focus(); return; }
        const id = `${Date.now()}_${Math.random().toString(36).slice(2, 7)}`;
        selected.set(id, { id, name, detail: detailInput?.value.trim() || '', quantity: Math.max(1, Number(quantityInput?.value || 1)) });
        nameInput.value = ''; detailInput.value = ''; quantityInput.value = '1'; nameInput.focus(); render();
    };
    addButton?.addEventListener('click', addTool);
    nameInput?.addEventListener('keydown', (event) => { if (event.key === 'Enter') { event.preventDefault(); addTool(); } });
    list?.addEventListener('click', (event) => { const button = event.target.closest('[data-remove]'); if (button) { selected.delete(button.dataset.remove); render(); } });
    list?.addEventListener('input', (event) => { const input = event.target.closest('[data-quantity]'); const item = input && selected.get(input.dataset.quantity); if (item) { item.quantity = Math.max(1, Number(input.value || 1)); input.value = item.quantity; } });
})();
</script>
</body>
</html>
