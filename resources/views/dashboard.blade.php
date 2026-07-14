<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gerencial - Inventario</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.72);
            --panel: rgba(30, 41, 59, 0.68);
            --ink: #fff;
            --muted: #94a3b8;
            --cyan: #06b6d4;
            --green: #10b981;
            --red: #ef4444;
            --amber: #f59e0b;
            --line: rgba(56, 189, 248, 0.22);
        }

        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: radial-gradient(circle at top left, #0a192f, var(--bg)); color: var(--ink); font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        .app-shell { display: flex; min-height: 100vh; }
        .app-content { flex: 1; padding: 34px 20px; overflow-x: hidden; }
        .container { width: min(1220px, 100%); margin: 0 auto; background: var(--surface); border: 1px solid var(--line); border-radius: 20px; padding: 30px; box-shadow: 0 18px 55px rgba(0,0,0,.55); backdrop-filter: blur(16px); }
        .header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-end; border-bottom: 1px solid var(--line); padding-bottom: 20px; margin-bottom: 22px; }
        h1 { margin: 0; font-size: 32px; font-weight: 900; background: linear-gradient(to right, #00f2fe, #4facfe); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .meta { margin: 7px 0 0; color: var(--muted); font-weight: 700; }
        .cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
        .card, .panel { background: var(--panel); border: 1px solid var(--line); border-radius: 16px; padding: 18px; }
        .card span { display: block; color: var(--muted); font-size: 12px; font-weight: 900; text-transform: uppercase; margin-bottom: 8px; }
        .card strong { font-size: 28px; }
        .charts { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr); gap: 18px; }
        .panel h2 { margin: 0 0 14px; font-size: 18px; color: #bae6fd; }
        .critical { margin-top: 18px; }
        .critical-list { display: grid; gap: 10px; }
        .critical-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: center; background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.28); border-radius: 12px; padding: 12px; }
        .muted { color: var(--muted); font-size: 13px; font-weight: 700; }
        .badge-red { color: #fca5a5; font-weight: 900; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { border-radius: 10px; min-height: 42px; padding: 0 14px; display: inline-flex; align-items: center; text-decoration: none; color: #fff; font-weight: 900; border: 1px solid rgba(255,255,255,.14); transition: transform .2s, filter .2s; }
        .btn:hover { transform: translateY(-1px); filter: brightness(1.08); }
        .btn-inventory { background: linear-gradient(135deg, #10b981, #047857); }
        .btn-exits { background: linear-gradient(135deg, #ef4444, #b91c1c); }
        .btn-pdf { background: linear-gradient(135deg, #f59e0b, #b45309); }
        .chart-shell { position: relative; min-height: 390px; }
        .chart-shell.small { min-height: 310px; }
        @media (max-width: 980px) { .cards, .charts { grid-template-columns: 1fr; } .header { display: block; } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')

    <main class="app-content">
        <div class="container">
            <div class="header">
                <div>
                    <h1>Dashboard Gerencial</h1>
                    <p class="meta">Consumo mensual, valor de inventario y alertas operativas.</p>
                </div>
                <div class="actions">
                    <a href="{{ route('reportes.inventario.csv') }}" class="btn btn-inventory">Excel Inventario</a>
                    <a href="{{ route('reportes.salidas.csv') }}" class="btn btn-exits">Excel Salidas</a>
                    <a href="{{ route('reportes.inventario.pdf') }}" class="btn btn-pdf">PDF Inventario</a>
                </div>
            </div>

            <section class="cards">
                <div class="card"><span>Materiales</span><strong>{{ number_format($totalMateriales) }}</strong></div>
                <div class="card"><span>Piezas en stock</span><strong>{{ number_format($stockTotal) }}</strong></div>
                <div class="card"><span>Valor inventario</span><strong>${{ number_format($valorInventario, 2) }}</strong></div>
                <div class="card"><span>Salidas del mes</span><strong>{{ number_format($salidasMes) }}</strong></div>
            </section>

            <section class="charts">
                <div class="panel">
                    <h2>Materiales más consumidos del mes</h2>
                    <div class="chart-shell">
                        <canvas id="consumoChart"></canvas>
                    </div>
                </div>
                <div class="panel">
                    <h2>Valor por categoría</h2>
                    <div class="chart-shell small">
                        <canvas id="valorChart"></canvas>
                    </div>
                </div>
            </section>

            <section class="panel critical">
                <h2>Alertas de stock mínimo: {{ $stockCriticoTotal }}</h2>
                <div class="critical-list">
                    @forelse($stockCritico as $material)
                        <div class="critical-item">
                            <div>
                                <strong>{{ $material->descripcion }}</strong>
                                <div class="muted">{{ $material->numero_parte ?? 'N/A' }} · {{ $material->categoria ?? 'Sin categoría' }}</div>
                            </div>
                            <div class="badge-red">{{ $material->stock }} / mín. {{ $material->stock_minimo }}</div>
                        </div>
                    @empty
                        <p class="muted">No hay materiales debajo del stock mínimo.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
</div>

<script>
    const consumoLabels = @json($consumoLabels);
    const consumoData = @json($consumoData);
    const valorLabels = @json($valorLabels);
    const valorData = @json($valorData);
    const acortar = (texto, max = 34) => texto.length > max ? `${texto.slice(0, max - 3)}...` : texto;

    new Chart(document.getElementById('consumoChart'), {
        type: 'bar',
        data: {
            labels: (consumoLabels.length ? consumoLabels : ['Sin salidas este mes']).map((label) => acortar(label)),
            datasets: [{
                label: 'Piezas consumidas',
                data: consumoData.length ? consumoData : [0],
                backgroundColor: '#10b981',
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title(items) {
                            return consumoLabels[items[0].dataIndex] || 'Sin salidas este mes';
                        }
                    }
                }
            },
            scales: {
                x: { beginAtZero: true, ticks: { color: '#94a3b8', precision: 0 }, grid: { color: 'rgba(148,163,184,.14)' } },
                y: { ticks: { color: '#e2e8f0', font: { weight: '700' } }, grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('valorChart'), {
        type: 'doughnut',
        data: {
            labels: valorLabels.length ? valorLabels : ['Sin valor capturado'],
            datasets: [{
                data: valorData.length ? valorData : [1],
                backgroundColor: ['#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#3b82f6', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#fff', boxWidth: 14 } },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return `${context.label}: $${Number(context.raw || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                        }
                    }
                }
            }
        }
    });
</script>
</body>
</html>
