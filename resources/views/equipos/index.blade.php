<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos y paquetes - Inventario Lugarth</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: "Segoe UI", Tahoma, sans-serif; background: #eef5fb; color: #08233f; }
        .app-shell { display: flex; min-height: 100vh; }
        .app-content { flex: 1; padding: 32px 20px; overflow-x: hidden; }
        .wrap { width: min(1180px, 100%); margin: 0 auto; }
        .hero, .card { background: #fff; border: 1px solid #cfe0f2; border-radius: 18px; box-shadow: 0 18px 50px rgba(15, 60, 105, .10); }
        .hero { padding: 26px; display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; margin-bottom: 18px; }
        h1, h2 { margin: 0; }
        h1 { font-size: clamp(26px, 4vw, 40px); letter-spacing: 0; color: #062443; }
        .muted { color: #58718a; font-weight: 700; font-size: 13px; line-height: 1.45; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .btn { min-height: 44px; border: 0; border-radius: 10px; padding: 0 16px; color: #fff; font-weight: 900; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
        .btn-blue { background: linear-gradient(135deg, #0ea5e9, #2563eb); }
        .btn-green { background: linear-gradient(135deg, #16a34a, #15803d); }
        .btn-red { background: linear-gradient(135deg, #ef4444, #b91c1c); }
        .btn-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .btn-soft { background: #e8f2ff; color: #075985; border: 1px solid #a8d3ff; }
        .grid { display: grid; grid-template-columns: minmax(300px, .75fr) minmax(0, 1.25fr); gap: 18px; align-items: start; }
        .card { padding: 22px; }
        label { display:block; color:#075985; font-size:12px; font-weight:900; text-transform:uppercase; margin-bottom:7px; }
        input, textarea { width:100%; min-height:44px; border:1px solid #bfd2e6; border-radius:10px; padding:11px 13px; font:inherit; color:#08233f; background:#fff; }
        textarea { min-height: 92px; resize: vertical; }
        .field { margin-bottom: 14px; }
        .search { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:10px; margin-bottom:14px; }
        .list { display:grid; gap:12px; }
        .row { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:12px; align-items:center; border:1px solid #d8e8f7; border-radius:14px; padding:14px; background:#f8fbff; }
        .row-actions { display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
        .title { font-weight: 950; font-size: 17px; }
        .pill { display:inline-flex; padding:5px 9px; border-radius:999px; background:#dcfce7; color:#166534; font-size:12px; font-weight:900; margin-top:7px; }
        .pill.warning { background:#fff7ed; color:#9a3412; border:1px solid #fed7aa; }
        .pill.danger { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        .alert { padding: 14px 16px; border-radius: 12px; margin-bottom: 16px; font-weight: 800; }
        .alert-ok { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .alert-bad { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .pagination { margin-top: 16px; }
        @media (max-width: 860px) { .hero, .grid { display:block; } .actions { margin-top:14px; } .card { margin-bottom:16px; } .search, .row { grid-template-columns:1fr; } .btn { width:100%; } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="wrap">
            <section class="hero">
                <div>
                    <h1>Equipos y paquetes</h1>
                    <p class="muted">Aqui van las recetas: cuantas piezas ocupa cada tipo de equipo. Al retirar o vender un paquete, el sistema descuenta las piezas vinculadas del inventario real.</p>
                </div>
                @if(auth()->user()?->puedeAdministrarCatalogo())
                    <form method="POST" action="{{ route('equipos.importar-materiales') }}">
                        @csrf
                        <button class="btn btn-amber" type="submit">Importar categorias actuales</button>
                    </form>
                @endif
            </section>

            @if(session('success')) <div class="alert alert-ok">{{ session('success') }}</div> @endif
            @if($errors->any()) <div class="alert alert-bad">{{ $errors->first() }}</div> @endif

            <div class="grid">
                <section class="card">
                    <h2>Registrar equipo</h2>
                    <p class="muted">Ejemplo: Equipo acero inoxidable, tipo asa, sistema despacho mecanico.</p>
                    <form method="POST" action="{{ route('equipos.store') }}">
                        @csrf
                        <div class="field">
                            <label for="nombre">Nombre del equipo</label>
                            <input id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. EQUIPO ACERO INOXIDABLE" required>
                        </div>
                        <div class="field">
                            <label for="codigo">Codigo interno</label>
                            <input id="codigo" name="codigo" value="{{ old('codigo') }}" placeholder="Opcional">
                        </div>
                        <div class="field">
                            <label for="descripcion">Notas</label>
                            <textarea id="descripcion" name="descripcion" placeholder="Detalles utiles para almacen o ventas">{{ old('descripcion') }}</textarea>
                        </div>
                        <button class="btn btn-green" type="submit">Guardar equipo</button>
                    </form>
                </section>

                <section class="card">
                    <form class="search" method="GET" action="{{ route('equipos.index') }}">
                        <input name="buscar" value="{{ $buscar }}" placeholder="Buscar equipo, codigo o descripcion">
                        <button class="btn btn-blue" type="submit">Buscar</button>
                    </form>

                    <div class="list">
                        @forelse($equipos as $equipo)
                            @php($disponibilidad = $equipo->evaluarDisponibilidad())
                            <article class="row">
                                <div>
                                    <div class="title">{{ $equipo->nombre }}</div>
                                    <div class="muted">{{ $equipo->codigo ?: 'Sin codigo' }}</div>
                                    @if($disponibilidad['sin_piezas'])
                                        <span class="pill danger">Sin piezas configuradas</span>
                                    @elseif($disponibilidad['sin_vincular']->isNotEmpty())
                                        <span class="pill warning">{{ $disponibilidad['sin_vincular']->count() }} piezas sin vincular</span>
                                    @elseif($disponibilidad['faltantes']->isNotEmpty())
                                        <span class="pill danger">Stock incompleto: faltan {{ $disponibilidad['faltantes']->count() }} materiales</span>
                                    @else
                                        <span class="pill">Listo para vender · {{ $equipo->items_count }} renglones</span>
                                    @endif
                                </div>
                                <div class="row-actions">
                                    <a class="btn btn-soft" href="{{ route('equipos.show', $equipo) }}">Abrir</a>
                                    @if($disponibilidad['listo'])
                                        <a class="btn btn-red" href="{{ route('equipos.show', $equipo) }}#vender-equipo">Vender</a>
                                    @elseif($disponibilidad['faltantes']->isNotEmpty())
                                        <a class="btn btn-amber" href="{{ route('equipos.show', $equipo) }}#vender-equipo">Revisar stock</a>
                                    @else
                                        <a class="btn btn-amber" href="{{ route('equipos.show', $equipo) }}">Completar piezas</a>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="muted">Aun no hay equipos registrados.</p>
                        @endforelse
                    </div>

                    <div class="pagination">{{ $equipos->links() }}</div>
                </section>
            </div>
        </div>
    </main>
</div>
</body>
</html>
