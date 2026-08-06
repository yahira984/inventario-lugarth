<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador de equipos - Inventario Lugarth</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: #092641; background: #eef5fb; font-family: "Segoe UI", Tahoma, sans-serif; }
        .app-shell { display: flex; min-height: 100vh; }.app-content { flex: 1; min-width: 0; padding: 30px 20px; }
        .simulator-page { width: min(1450px, 100%); margin: 0 auto; }.simulator-header, .simulator-panel { border: 1px solid #d4e3f0; background: #fff; box-shadow: 0 14px 38px rgba(21, 61, 97, .09); }
        .simulator-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; padding: 25px; border-radius: 12px; }.simulator-header h1, .simulator-panel h2, .simulator-panel h3 { margin: 0; }
        .simulator-header h1 { font-size: clamp(26px, 3.2vw, 40px); }.simulator-header p, .simulator-panel > p { margin: 8px 0 0; color: #55718a; line-height: 1.5; }.simulator-header p { max-width: 780px; }
        .simulator-actions { display: flex; gap: 9px; flex-wrap: wrap; }.simulator-actions .btn, .simulator-form .btn { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; padding: 0 14px; border-radius: 8px; text-decoration: none; font-weight: 850; cursor: pointer; }
        .btn-blue { color: #fff; border: 0; background: #0969d8; }.btn-soft { color: #075985; border: 1px solid #abd3f9; background: #eff8ff; }
        .simulator-layout { display: grid; grid-template-columns: minmax(260px, .32fr) minmax(0, 1fr); gap: 20px; margin-top: 20px; align-items: start; }
        .simulator-panel { border-radius: 12px; padding: 20px; }.simulator-list { display: grid; gap: 8px; margin-top: 15px; max-height: calc(100vh - 220px); overflow: auto; padding-right: 5px; }
        .simulator-equipment { display: block; padding: 12px; color: #25465e; text-decoration: none; border: 1px solid #d9e6f1; border-radius: 9px; background: #fbfdff; }.simulator-equipment:hover { border-color: #8fc4f2; background: #f0f8ff; }.simulator-equipment.active { color: #064e8c; border-color: #2589dd; background: #eaf5ff; box-shadow: inset 3px 0 #147bc8; }
        .simulator-equipment strong, .simulator-equipment span { display: block; }.simulator-equipment strong { font-size: 13px; line-height: 1.35; }.simulator-equipment span { margin-top: 4px; color: #668199; font-size: 11px; font-weight: 700; }.simulator-equipment .bad { color: #b42336; }
        .simulator-form { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; margin: 16px 0 20px; }.simulator-form select, .simulator-form input { width: 100%; min-height: 44px; padding: 10px 12px; color: #092641; border: 1px solid #bdd4e8; border-radius: 8px; font: inherit; background: #fff; }
        .formula-note { margin: 0 0 18px; padding: 12px 14px; color: #315875; border: 1px solid #b9dff7; border-radius: 8px; background: #f1f9ff; font-size: 13px; line-height: 1.45; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }.summary-card { padding: 16px; border: 1px solid #d4e5f3; border-radius: 10px; background: #fbfdff; }.summary-card small { display: block; color: #668199; font-size: 11px; font-weight: 850; text-transform: uppercase; }.summary-card strong { display: block; margin-top: 6px; color: #063a69; font-size: 28px; }.summary-card.green { border-color: #a7e7c9; background: #effcf5; }.summary-card.green strong { color: #08754d; }.summary-card.amber { border-color: #f4d087; background: #fff9eb; }.summary-card.amber strong { color: #a15b00; }
        .limit-box { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin: 18px 0; padding: 15px; border: 1px solid #f4c778; border-radius: 10px; background: #fff8e8; }.limit-box strong { display: block; color: #874600; }.limit-box span { display: block; margin-top: 4px; color: #835d26; font-size: 13px; line-height: 1.4; }
        .quantity-control { display: grid; grid-template-columns: minmax(0, 1fr) 140px; gap: 12px; align-items: end; margin: 20px 0 16px; padding: 16px; border: 1px solid #d5e5f2; border-radius: 10px; background: #f8fbfe; }.quantity-control label { display: block; margin-bottom: 6px; color: #0b5f98; font-size: 12px; font-weight: 850; text-transform: uppercase; }.quantity-control input { width: 100%; min-height: 46px; padding: 10px 12px; border: 1px solid #a9c8e1; border-radius: 8px; font: inherit; }.quantity-status { margin: 0; padding: 11px 13px; border: 1px solid #a7e7c9; border-radius: 8px; color: #08754d; background: #effcf5; font-size: 13px; font-weight: 750; }.quantity-status.bad { color: #b42336; border-color: #fecaca; background: #fff1f2; }
        .requirements { width: 100%; border-collapse: separate; border-spacing: 0 8px; }.requirements th { padding: 0 10px 5px; color: #32617f; font-size: 11px; text-align: left; text-transform: uppercase; }.requirements td { padding: 11px 10px; vertical-align: middle; background: #fbfdff; border-top: 1px solid #dbe8f2; border-bottom: 1px solid #dbe8f2; }.requirements td:first-child { border-left: 1px solid #dbe8f2; border-radius: 8px 0 0 8px; }.requirements td:last-child { border-right: 1px solid #dbe8f2; border-radius: 0 8px 8px 0; }.requirements tr.is-missing td { border-color: #fecaca; background: #fff7f7; }.piece { display: flex; align-items: center; gap: 10px; min-width: 190px; }.piece img, .piece-placeholder { width: 46px; height: 46px; flex: 0 0 46px; border: 1px solid #c7dced; border-radius: 7px; object-fit: contain; background: #fff; }.piece-placeholder { display: grid; place-items: center; color: #71859a; font-size: 10px; font-weight: 800; }.piece strong, .piece span { display: block; }.piece strong { font-size: 13px; line-height: 1.3; }.piece span { margin-top: 3px; color: #668199; font-size: 11px; }.status-chip { display: inline-flex; padding: 5px 8px; border-radius: 999px; color: #08754d; background: #ddf9e9; font-size: 11px; font-weight: 850; }.status-chip.bad { color: #b42336; background: #ffe5e8; }
        .incomplete { padding: 22px; color: #9a4a00; border: 1px solid #f8cf85; border-radius: 10px; background: #fff8e8; }.incomplete h2 { color: #8a4a00; }.incomplete ul { margin: 12px 0 0; padding-left: 20px; }.empty { padding: 30px; text-align: center; color: #668199; border: 1px dashed #bdd1e1; border-radius: 10px; }
        @media (max-width: 980px) { .simulator-layout { grid-template-columns: 1fr; }.simulator-list { max-height: 290px; }.summary-grid { grid-template-columns: 1fr; } }.@media (max-width: 650px) { .app-content { padding: 16px 12px 88px; }.simulator-header { display: block; padding: 20px; }.simulator-actions { margin-top: 14px; }.simulator-actions .btn { width: 100%; }.simulator-form, .quantity-control { grid-template-columns: 1fr; }.requirements, .requirements tbody, .requirements tr, .requirements td { display: block; }.requirements thead { display: none; }.requirements tr { margin-bottom: 10px; border: 1px solid #dbe8f2; border-radius: 9px; overflow: hidden; }.requirements td, .requirements td:first-child, .requirements td:last-child { display: flex; justify-content: space-between; gap: 12px; border: 0; border-radius: 0; }.requirements td::before { content: attr(data-label); color: #668199; font-size: 11px; font-weight: 850; }.requirements td:first-child { display: block; }.requirements td:first-child::before { display: none; }.limit-box { display: block; } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="simulator-page">
            <header class="simulator-header">
                <div><h1>Simulador de fabricacion</h1><p>Calcula cuantos equipos completos se pueden armar con el stock real disponible. No reserva ni descuenta piezas: es una consulta para planear produccion o ventas.</p></div>
                <div class="simulator-actions"><a class="btn btn-soft" href="{{ route('equipos.index') }}">Administrar equipos</a><a class="btn btn-blue" href="{{ route('equipos.withdrawals.create') }}">Retirar o vender</a></div>
            </header>

            <div class="simulator-layout">
                <aside class="simulator-panel">
                    <h2>Equipos disponibles</h2><p>Selecciona un equipo para revisar su capacidad.</p>
                    <form class="simulator-form" method="GET" action="{{ route('equipos.simulator') }}"><select name="equipo" aria-label="Equipo a simular" onchange="this.form.submit()">@foreach($equipos as $option)<option value="{{ $option->id }}" {{ $equipo?->id === $option->id ? 'selected' : '' }}>{{ $option->nombre }}</option>@endforeach</select><button class="btn btn-blue" type="submit">Ver</button></form>
                    <div class="simulator-list">
                        @forelse($equipos as $option)
                            @php($optionPlan = $planes->get($option->id))
                            <a class="simulator-equipment {{ $equipo?->id === $option->id ? 'active' : '' }}" href="{{ route('equipos.simulator', ['equipo' => $option->id]) }}"><strong>{{ $option->nombre }}</strong>@if($optionPlan['listo'])<span>{{ number_format($optionPlan['fabricables']) }} equipos fabricables</span>@else<span class="bad">Receta incompleta</span>@endif</a>
                        @empty
                            <div class="empty">Aun no hay equipos activos.</div>
                        @endforelse
                    </div>
                </aside>

                <section class="simulator-panel">
                    @if(!$equipo || !$planeacion)
                        <div class="empty"><strong>No hay equipo para simular.</strong><br>Registra un equipo y vincula sus piezas reales.</div>
                    @elseif(!$planeacion['listo'])
                        <div class="incomplete"><h2>Este equipo aun no se puede calcular</h2><p>La receta necesita al menos una pieza real vinculada y no puede tener renglones pendientes.</p>@if($planeacion['sin_vincular']->isNotEmpty())<strong>Piezas pendientes de vincular:</strong><ul>@foreach($planeacion['sin_vincular'] as $pieza)<li>{{ $pieza }}</li>@endforeach</ul>@endif</div>
                    @else
                        @php
                            $requirementsForJs = $planeacion['requisitos']->map(fn (array $requisito): array => [
                                'descripcion' => $requisito['descripcion'], 'unidad' => $requisito['material']?->unidad ?: 'pza', 'stock' => (int) $requisito['stock'], 'cantidad' => (float) $requisito['cantidad_por_equipo'],
                            ])->values();
                        @endphp
                        <h2>{{ $equipo->nombre }}</h2><p>Capacidad calculada con las piezas vinculadas y el stock que existe en este momento.</p>
                        <p class="formula-note">La capacidad corresponde solamente a este equipo. Si planeas fabricar equipos distintos al mismo tiempo, las piezas compartidas deben revisarse por separado porque el simulador no reserva stock.</p>
                        <div class="summary-grid"><div class="summary-card green"><small>Equipos fabricables hoy</small><strong>{{ number_format($planeacion['fabricables']) }}</strong></div><div class="summary-card"><small>Costo aproximado por equipo</small><strong>${{ number_format($planeacion['costo_unitario'], 2) }}</strong></div><div class="summary-card amber"><small>Valor fabricable estimado</small><strong>${{ number_format($planeacion['valor_stock_fabricable'], 2) }}</strong></div></div>
                        @if($planeacion['limitantes']->isNotEmpty())
                            <div class="limit-box"><div><strong>Pieza{{ $planeacion['limitantes']->count() > 1 ? 's' : '' }} limitante{{ $planeacion['limitantes']->count() > 1 ? 's' : '' }}</strong><span>{{ $planeacion['limitantes']->map(fn (array $limitante) => $limitante['descripcion'].' (permite '.number_format($limitante['fabricables']).')')->implode(', ') }}</span></div><a class="btn btn-soft" href="{{ route('equipos.show', $equipo) }}">Ver receta</a></div>
                        @endif
                        <div class="quantity-control"><div><label for="desiredEquipmentCount">Quiero fabricar o vender</label><input id="desiredEquipmentCount" type="number" min="1" value="1" inputmode="numeric"></div><p class="quantity-status" id="quantityStatus" aria-live="polite"></p></div>
                        <table class="requirements"><thead><tr><th>Pieza</th><th>Stock actual</th><th>Usa por equipo</th><th>Necesario ahora</th><th>Quedarian</th><th>Estado</th></tr></thead><tbody id="requirementsBody">@foreach($planeacion['requisitos'] as $requisito)<tr><td><div class="piece">@if($requisito['material']?->fotografia)<img data-workspace-lightbox data-lightbox-title="{{ $requisito['descripcion'] }}" src="{{ asset('storage/'.$requisito['material']->fotografia) }}" alt="Foto de {{ $requisito['descripcion'] }}">@else<div class="piece-placeholder">Sin foto</div>@endif<div><strong>{{ $requisito['descripcion'] }}</strong><span>{{ $requisito['material']?->numero_parte ?: 'Sin no. parte' }}</span></div></div></td><td data-label="Stock actual">{{ number_format($requisito['stock']) }} {{ $requisito['material']?->unidad ?: 'pza' }}</td><td data-label="Usa por equipo">{{ rtrim(rtrim(number_format($requisito['cantidad_por_equipo'], 2), '0'), '.') }}</td><td data-label="Necesario ahora" data-needed></td><td data-label="Quedarian" data-remaining></td><td data-label="Estado" data-state></td></tr>@endforeach</tbody></table>
                    @endif
                </section>
            </div>
        </div>
    </main>
</div>
@if($equipo && $planeacion && $planeacion['listo'])
<script>
(() => {
    const requirements = @json($requirementsForJs);
    const amountInput = document.getElementById('desiredEquipmentCount');
    const tableRows = [...document.querySelectorAll('#requirementsBody tr')];
    const status = document.getElementById('quantityStatus');
    const update = () => {
        const amount = Math.max(1, Number.parseInt(amountInput.value || '1', 10));
        amountInput.value = amount;
        const missing = [];
        requirements.forEach((piece, index) => {
            const needed = Math.ceil(piece.cantidad * amount);
            const remaining = piece.stock - needed;
            const row = tableRows[index];
            row.querySelector('[data-needed]').textContent = `${needed.toLocaleString('es-MX')} ${piece.unidad}`;
            row.querySelector('[data-remaining]').textContent = `${Math.max(remaining, 0).toLocaleString('es-MX')} ${piece.unidad}`;
            const state = row.querySelector('[data-state]');
            const hasMissing = remaining < 0;
            row.classList.toggle('is-missing', hasMissing);
            state.innerHTML = hasMissing ? `<span class="status-chip bad">Faltan ${Math.abs(remaining).toLocaleString('es-MX')}</span>` : '<span class="status-chip">Completo</span>';
            if (hasMissing) missing.push(`${piece.descripcion}: faltan ${Math.abs(remaining).toLocaleString('es-MX')} ${piece.unidad}`);
        });
        status.classList.toggle('bad', missing.length > 0);
        status.textContent = missing.length ? `No alcanza para ${amount.toLocaleString('es-MX')} equipo(s). ${missing.join('; ')}.` : `Stock completo para ${amount.toLocaleString('es-MX')} equipo(s).`;
    };
    amountInput.addEventListener('input', update);
    update();
})();
</script>
@endif
</body>
</html>
