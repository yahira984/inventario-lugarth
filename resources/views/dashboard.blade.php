<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gerencial - Inventario</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <!-- Scripts necesarios para Alpine (animaciones) y Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --bg: #020617;
            --surface: rgba(15, 23, 42, 0.78);
            --panel: rgba(15, 23, 42, 0.72);
            --panel-light: rgba(30, 41, 59, 0.68);
            --ink: #ffffff;
            --muted: #94a3b8;
            --cyan: #06b6d4;
            --blue: #3b82f6;
            --green: #10b981;
            --red: #ef4444;
            --amber: #f59e0b;
            --purple: #8b5cf6;
            --pink: #ec4899;
            --line: rgba(56, 189, 248, 0.20);
            --line-soft: rgba(148, 163, 184, 0.13);
            --shadow: 0 20px 60px rgba(0, 0, 0, 0.58);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 10% 0%, rgba(14, 165, 233, 0.14), transparent 30%),
                radial-gradient(circle at 90% 10%, rgba(139, 92, 246, 0.10), transparent 28%),
                linear-gradient(145deg, #020617 0%, #061426 48%, #020617 100%);
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        button, input, select { font-family: inherit; }
        .app-shell { display: flex; min-height: 100vh; }
        .app-content { flex: 1; min-width: 0; padding: 34px 20px; overflow-x: hidden; }
        .container {
            width: min(1320px, 100%); margin: 0 auto; padding: 30px;
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.84), rgba(2, 6, 23, 0.82));
            border: 1px solid var(--line); border-radius: 24px;
            box-shadow: var(--shadow), inset 0 0 35px rgba(56, 189, 248, 0.035);
            backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
        }
        .header { display: flex; justify-content: space-between; align-items: flex-end; gap: 22px; margin-bottom: 24px; padding-bottom: 22px; border-bottom: 1px solid var(--line); }
        .header-title-area { display: flex; align-items: center; gap: 15px; }
        .header-icon {
            display: flex; align-items: center; justify-content: center; width: 58px; height: 58px; flex-shrink: 0;
            border: 1px solid rgba(56, 189, 248, 0.30); border-radius: 17px;
            background: linear-gradient(145deg, rgba(14, 165, 233, 0.24), rgba(37, 99, 235, 0.12)); color: #7dd3fc;
            box-shadow: 0 0 24px rgba(14, 165, 233, 0.13);
        }
        .header-icon svg { width: 29px; height: 29px; }
        h1 { margin: 0; background: linear-gradient(90deg, #67e8f9, #38bdf8, #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: clamp(26px, 4vw, 36px); font-weight: 950; letter-spacing: -0.7px; }
        .meta { margin: 7px 0 0; color: var(--muted); font-size: 14px; font-weight: 650; line-height: 1.5; }
        .actions { display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 43px; padding: 0 15px; border-radius: 11px; font-size: 13px; font-weight: 850; text-decoration: none;
            transition: transform 0.2s, filter 0.2s, box-shadow 0.2s;
        }
        .btn svg { width: 17px; height: 17px; }
        .btn:hover { transform: translateY(-2px); filter: brightness(1.08); }
        .money-hero { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:18px; align-items:center; margin-bottom:20px; padding:24px; border-radius:20px; background:linear-gradient(135deg,#0f5fb8,#0ea5e9); color:#fff; box-shadow:0 20px 42px rgba(14,165,233,.25); }
        .money-hero span { display:block; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.08em; opacity:.9; }
        .money-hero strong { display:block; margin-top:6px; font-size:clamp(30px,5vw,54px); font-weight:950; line-height:1; overflow-wrap:anywhere; }
        .money-hero p { margin:8px 0 0; font-size:14px; font-weight:700; opacity:.92; }
        .money-hero-badge { padding:12px 14px; border-radius:14px; background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.24); font-weight:900; text-align:center; }
        .cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
        .card {
            position: relative; min-width: 0; overflow: hidden; padding: 19px;
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.78), rgba(15, 23, 42, 0.78));
            border: 1px solid var(--line-soft); border-radius: 17px; box-shadow: 0 14px 30px rgba(0, 0, 0, 0.24);
            transition: transform 0.25s, border-color 0.25s, box-shadow 0.25s;
        }
        .card::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: linear-gradient(90deg, var(--card-color), transparent); }
        .card::after { content: ""; position: absolute; right: -30px; top: -30px; width: 100px; height: 100px; border-radius: 50%; background: var(--card-glow); filter: blur(12px); pointer-events: none; }
        .card:hover { transform: translateY(-4px); border-color: color-mix(in srgb, var(--card-color) 45%, transparent); box-shadow: 0 18px 38px rgba(0, 0, 0, 0.32); }
        .card.cyan { --card-color: #06b6d4; --card-glow: rgba(6, 182, 212, 0.13); }
        .card.green { --card-color: #10b981; --card-glow: rgba(16, 185, 129, 0.13); }
        .card.amber { --card-color: #f59e0b; --card-glow: rgba(245, 158, 11, 0.13); }
        .card.purple { --card-color: #8b5cf6; --card-glow: rgba(139, 92, 246, 0.13); }
        .card-top { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .card-title { margin: 0; color: var(--muted); font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.9px; }
        .card-icon { display: flex; align-items: center; justify-content: center; width: 39px; height: 39px; border-radius: 11px; background: color-mix(in srgb, var(--card-color) 14%, transparent); border: 1px solid color-mix(in srgb, var(--card-color) 30%, transparent); color: var(--card-color); }
        .card-icon svg { width: 21px; height: 21px; }
        .card-value { position: relative; z-index: 2; display: block; font-size: clamp(23px, 3vw, 31px); font-weight: 950; line-height: 1.1; letter-spacing: -0.6px; word-break: break-word; }
        .card-footer { position: relative; z-index: 2; display: flex; align-items: center; gap: 6px; margin-top: 10px; color: var(--muted); font-size: 12px; font-weight: 650; }
        .card-link { color: inherit; text-decoration: none; }
        .card-link.amber .card-footer { color: #b45309; }
        .status-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--card-color); box-shadow: 0 0 8px var(--card-color); }
        .charts-main { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(330px, 0.8fr); gap: 18px; margin-bottom: 18px; }
        .charts-secondary { display: grid; grid-template-columns: minmax(330px, 0.8fr) minmax(0, 1.2fr); gap: 18px; margin-bottom: 18px; }
        .panel { min-width: 0; padding: 19px; background: linear-gradient(145deg, rgba(30, 41, 59, 0.66), rgba(15, 23, 42, 0.75)); border: 1px solid var(--line-soft); border-radius: 18px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.28); }
        .panel-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; margin-bottom: 15px; }
        .panel-title-area { min-width: 0; }
        .panel h2 { margin: 0; color: #e0f2fe; font-size: 17px; font-weight: 900; }
        .panel-description { margin: 5px 0 0; color: var(--muted); font-size: 12px; font-weight: 650; line-height: 1.5; }
        .panel-tag { flex-shrink: 0; padding: 6px 9px; background: rgba(56, 189, 248, 0.09); border: 1px solid rgba(56, 189, 248, 0.20); border-radius: 8px; color: #7dd3fc; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.6px; }
        .chart-shell { position: relative; height: 390px; min-height: 390px; }
        .chart-shell.medium { height: 330px; min-height: 330px; }
        .chart-shell.health { height: 295px; min-height: 295px; }
        .provider-ranking { display:grid; gap:10px; }
        .provider-row { display:grid; grid-template-columns:42px minmax(0,1fr) auto; align-items:center; gap:12px; padding:13px 14px; background:#ffffff; border:1px solid #cfe0f2; border-radius:14px; box-shadow:0 10px 24px rgba(15,60,105,.08); transition:transform .2s, border-color .2s, box-shadow .2s; }
        .provider-row:hover { transform:translateY(-2px); border-color:#38bdf8; box-shadow:0 14px 30px rgba(15,60,105,.14); }
        .provider-rank { width:42px; height:42px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; background:#e0f2fe; color:#075985; font-weight:950; }
        .provider-name { display:block; color:#10233f; font-size:14px; font-weight:950; line-height:1.3; overflow-wrap:anywhere; }
        .provider-meta { margin-top:3px; color:#64748b; font-size:12px; font-weight:750; }
        .provider-money { color:#047857; font-size:15px; font-weight:950; text-align:right; white-space:nowrap; }
        .chart-footer-info { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 13px; }
        .chart-stat { padding: 12px; background: #ffffff; border: 1px solid #cfe0f2; border-radius: 11px; box-shadow: 0 10px 24px rgba(15, 60, 105, .08); }
        .chart-stat span { display: block; margin-bottom: 5px; color: #475569; font-size: 10px; font-weight: 850; text-transform: uppercase; letter-spacing: 0.6px; }
        .chart-stat strong { font-size: 18px; font-weight: 900; }
        .chart-stat.good strong { color: #6ee7b7; }
        .chart-stat.danger strong { color: #fca5a5; }
        .critical { margin-top: 0; }
        .critical-header { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 15px; }
        .critical-title { display: flex; align-items: center; gap: 11px; }
        .critical-icon { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.28); border-radius: 11px; color: #f87171; }
        .critical-icon svg { width: 21px; height: 21px; }
        .critical-counter { padding: 7px 11px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 999px; color: #fca5a5; font-size: 12px; font-weight: 900; }
        .critical-list { display: grid; gap: 10px; }
        .critical-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 14px; padding: 13px 15px; background: linear-gradient(90deg, rgba(239, 68, 68, 0.10), rgba(127, 29, 29, 0.04)); border: 1px solid rgba(239, 68, 68, 0.24); border-radius: 13px; transition: transform 0.2s, border-color 0.2s, background 0.2s; }
        .critical-item:hover { transform: translateX(3px); border-color: rgba(239, 68, 68, 0.44); background: linear-gradient(90deg, rgba(239, 68, 68, 0.15), rgba(127, 29, 29, 0.06)); }
        .critical-item strong { display: block; color: #ffffff; font-size: 14px; line-height: 1.4; }
        .muted { margin-top: 4px; color: var(--muted); font-size: 12px; font-weight: 650; line-height: 1.4; }
        .badge-red { min-width: 110px; padding: 8px 10px; background: rgba(239, 68, 68, 0.10); border: 1px solid rgba(239, 68, 68, 0.21); border-radius: 9px; color: #fca5a5; font-size: 12px; font-weight: 900; text-align: center; }
        .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 150px; padding: 20px; background: rgba(16, 185, 129, 0.055); border: 1px dashed rgba(16, 185, 129, 0.24); border-radius: 13px; color: #6ee7b7; text-align: center; }
        .empty-state svg { width: 37px; height: 37px; margin-bottom: 9px; }
        .empty-state strong { font-size: 14px; }
        .empty-state span { margin-top: 4px; color: var(--muted); font-size: 12px; }
        @media (max-width: 1120px) { .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } .charts-main, .charts-secondary { grid-template-columns: 1fr; } .chart-shell { height: 350px; min-height: 350px; } }
        @media (max-width: 760px) { .app-content { padding: 20px 12px; } .container { padding: 20px 15px; border-radius: 18px; } .header { display: block; } .header-title-area { align-items: flex-start; } .actions { justify-content: flex-start; margin-top: 18px; } .btn { flex: 1; min-width: 145px; } .money-hero { grid-template-columns:1fr; } .cards { grid-template-columns: 1fr; } .chart-shell, .chart-shell.medium, .chart-shell.health { height: 310px; min-height: 310px; } .panel { padding: 15px; } .panel-header { display: block; } .panel-tag { display: inline-flex; margin-top: 9px; } .provider-row { grid-template-columns:36px minmax(0,1fr); } .provider-money { grid-column:2; text-align:left; } .critical-header { align-items: flex-start; } .critical-item { grid-template-columns: 1fr; } .badge-red { width: 100%; } }
        @media (max-width: 440px) { .header-icon { width: 48px; height: 48px; } .actions { flex-direction: column; } .btn { width: 100%; } .chart-footer-info { grid-template-columns: 1fr; } }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.23); }
        ::-webkit-scrollbar-thumb { background: rgba(56, 189, 248, 0.42); border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(56, 189, 248, 0.68); }
        
        /* ESTILOS AÑADIDOS PARA EL WIDGET DE XBOX */
        .xbox-widget-container * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .xbox-custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .xbox-custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .xbox-custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
        }
        .xbox-custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>

<div class="app-shell">
    @include('materiales.partials.sidebar')

    <main class="app-content">
        <div class="container">
            <!-- ENCABEZADO -->
            <header class="header">
                <div class="header-title-area">
                    <div class="header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5h4.5V21H3v-7.5Zm6.75-6h4.5V21h-4.5V7.5Zm6.75-4.5H21v18h-4.5V3Z" />
                        </svg>
                    </div>
                    <div>
                        <h1>Dashboard Gerencial</h1>
                        <p class="meta">Monitoreo de consumo, valor de inventario, existencias y alertas operativas.</p>
                    </div>
                </div>

                <div class="actions">
                    <!-- BOTÓN EXCEL (VERDE FORZADO) -->
                    <a href="{{ route('reportes.inventario.csv') }}" class="btn" style="background: #16a34a !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 10.5 12 15m0 0 4.5-4.5M12 15V3" />
                        </svg>
                        Excel Inventario
                    </a>

                    <!-- BOTÓN SALIDAS (GRIS FORZADO) -->
                    <a href="{{ route('reportes.salidas.csv') }}" class="btn" style="background: #15803d !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H9.75m9 0-3-3m3 3-3 3" />
                        </svg>
                        Excel Salidas
                    </a>

                    <!-- BOTÓN PDF (ROJO FORZADO) -->
                    <a href="{{ route('reportes.inventario.pdf') }}" class="btn" style="background: #b91c1c !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        PDF Inventario
                    </a>
                </div>
            </header>

            <section class="money-hero">
                <div>
                    <span>Valor total del inventario</span>
                    <strong>${{ number_format($valorInventario, 2) }} MXN</strong>
                    <p>Calculado con stock actual por costo unitario de cada material real.</p>
                </div>
                <div class="money-hero-badge">{{ number_format($totalMateriales) }} materiales</div>
            </section>

            <section class="panel" style="margin-bottom:18px;">
                <div class="panel-header">
                    <div class="panel-title-area">
                        <h2>Top 5 proveedores por compras</h2>
                        <p class="panel-description">Clasificación por el dinero acumulado en entradas de almacén.</p>
                    </div>
                    <span class="panel-tag">Compras en MXN</span>
                </div>
                <div class="provider-ranking">
                    @forelse($topProveedoresCompras as $proveedor)
                        <div class="provider-row">
                            <span class="provider-rank">#{{ $loop->iteration }}</span>
                            <div>
                                <span class="provider-name">{{ $proveedor->proveedor }}</span>
                                <div class="provider-meta">{{ number_format((int) $proveedor->piezas) }} piezas compradas</div>
                            </div>
                            <div class="provider-money">${{ number_format((float) $proveedor->total, 2) }} MXN</div>
                        </div>
                    @empty
                        <div class="empty-state" style="min-height:100px;margin-bottom:10px;">
                            <strong>Aún no hay compras con proveedor e importe capturados</strong>
                            <span>El ranking en dinero se llenará automáticamente al registrar entradas con proveedor y costo unitario.</span>
                        </div>
                        @foreach($proveedoresCatalogo as $proveedor)
                            <div class="provider-row">
                                <span class="provider-rank">#{{ $loop->iteration }}</span>
                                <div>
                                    <span class="provider-name">{{ $proveedor->proveedor }}</span>
                                    <div class="provider-meta">{{ number_format((int) $proveedor->productos) }} referencias históricas</div>
                                </div>
                                <div class="provider-money" style="color:#b45309;">Sin importe capturado</div>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </section>

            <!-- TARJETAS DE INDICADORES -->
            <section class="cards">
                <article class="card cyan">
                    <div class="card-top">
                        <p class="card-title">Materiales registrados</p>
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 8.25-9-5.25-9 5.25m18 0-9 5.25m9-5.25V15l-9 5.25M3 8.25l9 5.25M3 8.25V15l9 5.25m0-6.75v6.75" /></svg>
                        </div>
                    </div>
                    <strong class="card-value">{{ number_format($totalMateriales) }}</strong>
                    <div class="card-footer"><span class="status-dot"></span> Productos diferentes en el sistema</div>
                </article>
                <article class="card green">
                    <div class="card-top">
                        <p class="card-title">Piezas en stock</p>
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5 12 12.75 3.75 7.5M12 12.75V21m8.25-13.5L12 2.25 3.75 7.5m16.5 0v9L12 21l-8.25-4.5v-9" /></svg>
                        </div>
                    </div>
                    <strong class="card-value">{{ number_format($stockTotal) }}</strong>
                    <div class="card-footer"><span class="status-dot"></span> Existencias acumuladas</div>
                </article>
                <a class="card card-link amber" href="{{ route('admin.entradas.index') }}">
                    <div class="card-top">
                        <p class="card-title">Entradas por aprobar</p>
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m4-9.5H10a2.5 2.5 0 0 0 0 5h4a2.5 2.5 0 0 1 0 5H8M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </div>
                    </div>
                    <strong class="card-value">{{ number_format($entradasPendientes) }}</strong>
                    <div class="card-footer"><span class="status-dot"></span> Revisar solicitudes pendientes</div>
                </a>
                <article class="card purple">
                    <div class="card-top">
                        <p class="card-title">Salidas del mes</p>
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H9.75m9 0-3-3m3 3-3 3" /></svg>
                        </div>
                    </div>
                    <strong class="card-value">{{ number_format($salidasMes) }}</strong>
                    <div class="card-footer"><span class="status-dot"></span> Piezas consumidas en el periodo</div>
                </article>
            </section>

            <!-- GRÁFICAS PRINCIPALES -->
            <section class="charts-main">
                <article class="panel">
                    <div class="panel-header">
                        <div class="panel-title-area">
                            <h2>Materiales más consumidos</h2>
                            <p class="panel-description">Clasificación de los materiales con mayor cantidad de salidas durante el mes actual.</p>
                        </div>
                        <span class="panel-tag">Consumo mensual</span>
                    </div>
                    <div class="chart-shell">
                        <canvas id="consumoChart"></canvas>
                    </div>
                </article>
                <article class="panel">
                    <div class="panel-header">
                        <div class="panel-title-area">
                            <h2>Valor por categoría</h2>
                            <p class="panel-description">Participación económica de cada categoría dentro del inventario.</p>
                        </div>
                        <span class="panel-tag">Distribución</span>
                    </div>
                    <div class="chart-shell medium">
                        <canvas id="valorChart"></canvas>
                    </div>
                </article>
            </section>

            <!-- GRÁFICA NUEVA Y RESUMEN -->
            <section class="charts-secondary">
                <article class="panel">
                    <div class="panel-header">
                        <div class="panel-title-area">
                            <h2>Estado general del inventario</h2>
                            <p class="panel-description">Comparación entre materiales disponibles y materiales debajo del stock mínimo.</p>
                        </div>
                        <span class="panel-tag">Salud del stock</span>
                    </div>
                    <div class="chart-shell health">
                        <canvas id="estadoInventarioChart"></canvas>
                    </div>
                    <div class="chart-footer-info">
                        <div class="chart-stat good">
                            <span>Stock saludable</span>
                            <strong>{{ number_format(max((int) $totalMateriales - (int) $stockCriticoTotal, 0)) }}</strong>
                        </div>
                        <div class="chart-stat danger">
                            <span>Stock crítico</span>
                            <strong>{{ number_format($stockCriticoTotal) }}</strong>
                        </div>
                    </div>
                </article>

                <!-- ALERTAS DE STOCK -->
                <article class="panel critical">
                    <div class="critical-header">
                        <div class="critical-title">
                            <div class="critical-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.052 3.38c.866-1.5 3.03-1.5 3.896 0l7.355 12.746ZM12 16.5h.008v.008H12V16.5Z" /></svg>
                            </div>
                            <div>
                                <h2>Alertas de stock mínimo</h2>
                                <p class="panel-description">Materiales que requieren revisión o reabastecimiento.</p>
                            </div>
                        </div>
                        <span class="critical-counter">{{ number_format($stockCriticoTotal) }} alertas</span>
                    </div>
                    <div class="critical-list">
                    @forelse($stockCritico as $material)
                        <div class="critical-item">
                            <!-- Nuevo contenedor Flex para agrupar la foto y la información -->
                            <div style="display: flex; align-items: center; gap: 12px;">
                                
                                <!-- Validación y miniatura de la imagen -->
                                @if(isset($material->fotografia) && $material->fotografia)
                                    <img src="{{ asset('storage/' . $material->fotografia) }}" alt="Foto de {{ $material->descripcion }}" style="width: 42px; height: 42px; border-radius: 8px; object-fit: contain; background-color: rgba(255, 255, 255, 0.5); padding: 2px; border: 1px solid rgba(239, 68, 68, 0.3);">                
                                @else
                                    <!-- Icono por defecto si el material no tiene foto -->
                                    <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); border: 1px dashed rgba(239, 68, 68, 0.3); display: flex; align-items: center; justify-content: center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#fca5a5" style="width: 20px; height: 20px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 1-1.5-1.5V6a1.5 1.5 0 0 1 1.5-1.5h16.5A1.5 1.5 0 0 1 22.5 6v12a1.5 1.5 0 0 1-1.5 1.5zm-10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0z" />
                                        </svg>
                                    </div>
                                @endif

                                <!-- Textos originales -->
                                <div>
                                    <strong>{{ $material->descripcion }}</strong>
                                    <div class="muted">{{ $material->numero_parte ?? 'N/A' }} · {{ $material->categoria ?? 'Sin categoría' }}</div>
                                </div>
                            </div>
                            
                            <!-- Etiqueta de stock original -->
                            <div class="badge-red">{{ number_format($material->stock) }} / mín. {{ number_format($material->stock_minimo) }}</div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <strong>Inventario sin alertas críticas</strong>
                            <span>Todos los materiales se encuentran por encima del stock mínimo.</span>
                        </div>
                    @endforelse
                    </div>
                </article>
            </section>
        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- CONTENEDOR DEL WIDGET XBOX COMPLETAMENTE FIJO -->
<!-- ========================================== -->
<div class="xbox-widget-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 99999;">
    <!-- Inicializamos AlpineJS aquí mismo -->
    <div x-data="{ openAmigos: false }" @click.outside="openAmigos = false">
        
        @php
            $usuariosXbox = \App\Models\User::orderBy('last_seen_at', 'desc')->get();
            $enLinea = $usuariosXbox->filter(fn($u) => $u->isOnline());
            $desconectados = $usuariosXbox->reject(fn($u) => $u->isOnline());
        @endphp

        <!-- LA LISTA DE AMIGOS (Se despliega hacia arriba) -->
        <div x-show="openAmigos"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="transform opacity-0 translate-y-4"
             x-transition:enter-end="transform opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 translate-y-0"
             x-transition:leave-end="transform opacity-0 translate-y-4"
             style="display: none; margin-bottom: 8px; width: 320px; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); background-color: #1c1c1c; color: white; border: 1px solid #303030; overflow: hidden;">
            
            <div class="xbox-custom-scrollbar" style="max-height: 350px; overflow-y: auto; padding-top: 12px; padding-bottom: 12px;">
                <!-- En Línea -->
                @if($enLinea->count() > 0)
                <div style="margin-top: 4px;">
                    <h3 style="padding-left: 16px; padding-right: 16px; font-size: 12px; font-weight: 600; color: #d1d5db; margin-bottom: 4px;">En línea ({{ $enLinea->count() }})</h3>
                    @foreach($enLinea as $user)
                    <div style="display: flex; align-items: center; padding: 8px 16px; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#2d2d2d'" onmouseout="this.style.backgroundColor='transparent'">
                        <div style="position: relative; flex-shrink: 0;">
                            <img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=7F9CF5&background=EBF4FF') }}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                            <span style="position: absolute; bottom: 0; right: 0; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #1c1c1c; background-color: #107c10;"></span>
                        </div>
                        <div style="margin-left: 12px; flex: 1; overflow: hidden;">
                            <p style="font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #f2f2f2; margin: 0;">{{ $user->name }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Sin Conexión -->
                @if($desconectados->count() > 0)
                <div style="margin-top: 16px;">
                    <h3 style="padding-left: 16px; padding-right: 16px; font-size: 12px; font-weight: 600; color: #d1d5db; margin-bottom: 4px;">Sin conexión ({{ $desconectados->count() }})</h3>
                    @foreach($desconectados as $user)
                    <div style="display: flex; align-items: center; padding: 8px 16px; cursor: pointer; opacity: 0.7; transition: all 0.2s;" onmouseover="this.style.opacity='1'; this.style.backgroundColor='#2d2d2d'" onmouseout="this.style.opacity='0.7'; this.style.backgroundColor='transparent'">
                        <div style="position: relative; flex-shrink: 0;">
                            <img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=7F9CF5&background=EBF4FF') }}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; filter: grayscale(100%);">
                            <span style="position: absolute; bottom: 0; right: 0; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #1c1c1c; background-color: transparent;"></span>
                        </div>
                        <div style="margin-left: 12px; flex: 1; overflow: hidden;">
                            <p style="font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #f2f2f2; margin: 0;">{{ $user->name }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- LA PESTAÑITA EXACTA DE LA CAPTURA -->
        <div @click="openAmigos = !openAmigos" 
             style="width: 320px; background-color: #242424; border: 1px solid #333333; border-radius: 12px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#2a2a2a'" onmouseout="this.style.backgroundColor='#242424'">
            <div>
                <p style="color: #f2f2f2; font-weight: 700; font-size: 15px; line-height: 1.25; margin: 0;">Amigos</p>
                <p style="color: #e0e0e0; font-size: 13px; margin: 0;">{{ $enLinea->count() }} en línea</p>
            </div>
            <div style="display: flex; align-items: center; gap: 16px; color: #f2f2f2;">
                <!-- Flechita -->
                <svg :style="openAmigos ? 'transform: rotate(180deg);' : ''" style="width: 20px; height: 20px; transition: transform 0.2s;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                <!-- Ícono de expandir -->
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </div>
        </div>

    </div>
</div>
<!-- ========================================== -->


<script>
    const consumoLabels = @json($consumoLabels);
    const consumoData = @json($consumoData);
    const valorLabels = @json($valorLabels);
    const valorData = @json($valorData);
    const totalMateriales = Number(@json((int) $totalMateriales));
    const stockCriticoTotal = Number(@json((int) $stockCriticoTotal));
    const materialesSaludables = Math.max(totalMateriales - stockCriticoTotal, 0);

    Chart.defaults.color = '#334155';
    Chart.defaults.font.family = '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif';
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.96)';
    Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
    Chart.defaults.plugins.tooltip.bodyColor = '#e2e8f0';
    Chart.defaults.plugins.tooltip.borderColor = 'rgba(56, 189, 248, 0.30)';
    Chart.defaults.plugins.tooltip.borderWidth = 1;
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 10;
    Chart.defaults.plugins.tooltip.displayColors = true;

    const acortar = (texto, max = 35) => { const valor = String(texto ?? ''); return valor.length > max ? `${valor.slice(0, max - 3)}...` : valor; };
    const formatoNumero = (numero) => { return Number(numero || 0).toLocaleString('es-MX', { maximumFractionDigits: 0 }); };
    const formatoMoneda = (numero) => { return Number(numero || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN', minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

    const textoCentralPlugin = {
        id: 'textoCentral',
        afterDraw(chart, args, opciones) {
            if (!opciones || !opciones.mostrar) return;
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const centroX = (chartArea.left + chartArea.right) / 2;
            const centroY = (chartArea.top + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.fillStyle = opciones.colorPrincipal || '#ffffff';
            ctx.font = `900 ${opciones.tamanoPrincipal || 24}px "Segoe UI"`;
            ctx.fillText(opciones.textoPrincipal || '', centroX, centroY - 8);
            ctx.fillStyle = opciones.colorSecundario || '#94a3b8';
            ctx.font = `700 ${opciones.tamanoSecundario || 11}px "Segoe UI"`;
            ctx.fillText(opciones.textoSecundario || '', centroX, centroY + 17);
            ctx.restore();
        }
    };
    Chart.register(textoCentralPlugin);

    const consumoCanvas = document.getElementById('consumoChart');
    const consumoContexto = consumoCanvas.getContext('2d');
    const gradienteConsumo = consumoContexto.createLinearGradient(0, 0, 650, 0);
    gradienteConsumo.addColorStop(0, 'rgba(16, 185, 129, 0.95)');
    gradienteConsumo.addColorStop(0.55, 'rgba(6, 182, 212, 0.92)');
    gradienteConsumo.addColorStop(1, 'rgba(59, 130, 246, 0.88)');

    new Chart(consumoContexto, {
        type: 'bar',
        data: {
            labels: (consumoLabels.length ? consumoLabels : ['Sin salidas registradas este mes']).map((label) => acortar(label)),
            datasets: [{ label: 'Piezas consumidas', data: consumoData.length ? consumoData : [0], backgroundColor: gradienteConsumo, borderColor: 'rgba(103, 232, 249, 0.70)', borderWidth: 1, borderRadius: 9, borderSkipped: false, barThickness: 22, maxBarThickness: 26, hoverBackgroundColor: 'rgba(34, 211, 238, 0.95)' }]
        },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false, animation: { duration: 900, easing: 'easeOutQuart' }, interaction: { intersect: false, mode: 'nearest' },
            plugins: { legend: { display: false }, tooltip: { callbacks: { title(items) { const indice = items[0].dataIndex; return (consumoLabels[indice] || 'Sin salidas registradas'); }, label(context) { return ` Piezas consumidas: ` + formatoNumero(context.raw); } } } },
            scales: { x: { beginAtZero: true, ticks: { color: '#94a3b8', precision: 0, font: { size: 11, weight: '700' }, callback(value) { return formatoNumero(value); } }, grid: { color: 'rgba(148, 163, 184, 0.24)', drawBorder: false }, border: { display: false } }, y: { ticks: { color: '#334155', font: { size: 11, weight: '750' }, padding: 8 }, grid: { display: false }, border: { display: false } } }
        }
    });

    const coloresCategorias = ['#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#3b82f6', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316'];
    const totalValorCategorias = valorData.reduce((acumulado, valor) => acumulado + Number(valor || 0), 0);

    new Chart(document.getElementById('valorChart'), {
        type: 'doughnut',
        data: { labels: valorLabels.length ? valorLabels : ['Sin valor capturado'], datasets: [{ data: valorData.length ? valorData : [1], backgroundColor: coloresCategorias, borderColor: '#ffffff', borderWidth: 4, hoverBorderColor: '#FFFF', hoverOffset: 9, spacing: 2 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '67%', animation: { animateRotate: true, duration: 1000, easing: 'easeOutQuart' },
            plugins: {
                legend: { position: 'bottom', labels: { color: '#334155', boxWidth: 11, boxHeight: 11, padding: 14, usePointStyle: true, pointStyle: 'circle', font: { size: 10, weight: '700' }, generateLabels(chart) { const datos = chart.data; if (!datos.labels.length || !datos.datasets.length) { return []; } return datos.labels.map((label, indice) => { const meta = chart.getDatasetMeta(0); const estilo = meta.controller.getStyle(indice); return { text: acortar(label, 25), fillStyle: estilo.backgroundColor, strokeStyle: estilo.borderColor, lineWidth: estilo.borderWidth, hidden: !chart.getDataVisibility(indice), index: indice, pointStyle: 'circle' }; }); } } },
                tooltip: { callbacks: { label(context) { const valor = Number(context.raw || 0); const porcentaje = totalValorCategorias > 0 ? (valor / totalValorCategorias * 100).toFixed(1) : '0.0'; return ` ${context.label}: ${formatoMoneda(valor)} (${porcentaje}%)`; } } },
                textoCentral: { mostrar: true, textoPrincipal: `${valorLabels.length}`, textoSecundario: valorLabels.length === 1 ? 'CATEGORIA' : 'CATEGORIAS', tamanoPrincipal: 24, tamanoSecundario: 10, colorPrincipal: '#102033', colorSecundario: '#94a3b8' }
            }
        }
    });

    const totalParaPorcentaje = totalMateriales > 0 ? totalMateriales : 1;
    const porcentajeSaludable = totalMateriales > 0 ? (materialesSaludables / totalParaPorcentaje * 100).toFixed(0) : 0;

    new Chart(document.getElementById('estadoInventarioChart'), {
        type: 'doughnut',
        data: { labels: ['Stock saludable', 'Stock crítico'], datasets: [{ data: totalMateriales > 0 ? [materialesSaludables, stockCriticoTotal] : [1, 0], backgroundColor: ['rgba(16, 185, 129, 0.90)', 'rgba(239, 68, 68, 0.90)'], borderColor: ['rgba(110, 231, 183, 0.85)', 'rgba(252, 165, 165, 0.85)'], borderWidth: 2, hoverOffset: 8, spacing: 3 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '74%', circumference: 300, rotation: -150, animation: { animateRotate: true, duration: 1100, easing: 'easeOutQuart' },
            plugins: {
                legend: { position: 'bottom', labels: { color: '#04224b', usePointStyle: true, pointStyle: 'circle', boxWidth: 10, boxHeight: 10, padding: 16, font: { size: 11, weight: '750' } } },
                tooltip: { callbacks: { label(context) { const valor = Number(context.raw || 0); const porcentaje = totalMateriales > 0 ? (valor / totalMateriales * 100).toFixed(1) : '0.0'; return ` ${context.label}: ${formatoNumero(valor)} materiales (${porcentaje}%)`; } } },
                textoCentral: { mostrar: true, textoPrincipal: `${porcentajeSaludable}%`, textoSecundario: 'SALUDABLE', tamanoPrincipal: 29, tamanoSecundario: 10, colorPrincipal: '#6ee7b7', colorSecundario: '#94a3b8' }
            }
        }
    });
</script>