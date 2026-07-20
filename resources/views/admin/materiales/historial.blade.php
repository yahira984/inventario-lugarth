<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de material - Inventario</title>
    <style>
        body { margin:0; font-family:"Segoe UI",Tahoma,sans-serif; background:#f6f8fb; color:#102033; }
        .app-shell { display:flex; min-height:100vh; }
        .app-content { flex:1; padding:32px 18px; overflow-x:hidden; }
        .container { max-width:1180px; margin:0 auto; }
        .hero,.card { background:#fff; border:1px solid #dbe5f0; border-radius:16px; box-shadow:0 16px 40px rgba(15,23,42,.08); }
        .hero { padding:24px; margin-bottom:18px; display:flex; gap:16px; justify-content:space-between; align-items:flex-start; }
        h1,h2 { margin:0; color:#062443; }
        .muted { color:#64748b; font-size:13px; font-weight:700; line-height:1.45; }
        .btn { min-height:42px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; border:1px solid #1d4ed8; background:#2563eb; color:#fff; padding:0 14px; text-decoration:none; font-weight:800; }
        .grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:18px; }
        .card { padding:20px; }
        .row { border:1px solid #e2e8f0; border-radius:12px; padding:12px; margin-top:10px; background:#f8fafc; }
        .pill { display:inline-flex; padding:5px 9px; border-radius:999px; font-size:12px; font-weight:900; background:#dbeafe; color:#1d4ed8; }
        .pill.salida,.pill.merma { background:#fee2e2; color:#b91c1c; }
        .pill.entrada,.pill.devolucion { background:#dcfce7; color:#166534; }
        .photo { width:72px; height:72px; object-fit:cover; border-radius:12px; border:1px solid #dbe5f0; margin-top:8px; }
        @media(max-width:900px){ .app-content{padding-top:76px;} .hero,.grid{display:block;} .card{margin-bottom:16px;} }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="container">
            <section class="hero">
                <div>
                    <h1>{{ $material->descripcion }}</h1>
                    <div class="muted">
                        {{ $material->apodo ? 'Apodo: '.$material->apodo.' - ' : '' }}
                        No. parte: {{ $material->numero_parte ?: 'N/A' }} - Stock actual: {{ $material->stock }} pzas
                    </div>
                </div>
            <a class="btn btn-soft" href="{{ route('admin.materiales.index') }}">Volver</a>
            </section>

            <div class="grid">
                <section class="card">
                    <h2>Movimientos de stock</h2>
                    @forelse($movimientos as $movimiento)
                        <div class="row">
                            <span class="pill {{ $movimiento->tipo }}">{{ ucfirst($movimiento->tipo) }}</span>
                            <strong> {{ $movimiento->cantidad }} pzas</strong>
                            <div class="muted">{{ $movimiento->created_at->format('d/m/Y H:i') }} - {{ $movimiento->user?->name ?? 'Usuario no disponible' }}</div>
                            <div class="muted">Stock: {{ $movimiento->stock_anterior }} -> {{ $movimiento->stock_nuevo }}</div>
                            @if($movimiento->referencia)<div class="muted">Referencia: {{ $movimiento->referencia }}</div>@endif
                            @if($movimiento->motivo)<div class="muted">Nota: {{ $movimiento->motivo }}</div>@endif
                            @if($movimiento->evidencia_foto)<img class="photo" src="{{ asset('storage/'.$movimiento->evidencia_foto) }}" alt="Evidencia">@endif
                        </div>
                    @empty
                        <p class="muted">Este material aun no tiene movimientos.</p>
                    @endforelse
                    {{ $movimientos->links() }}
                </section>

                <section class="card">
                    <h2>Bitacora administrativa</h2>
                    @forelse($logs as $log)
                        <div class="row">
                            <span class="pill">{{ $log->accion }}</span>
                            <div class="muted">{{ $log->created_at->format('d/m/Y H:i') }} - {{ $log->user?->name ?? 'Sistema' }}</div>
                            <strong>{{ $log->modulo }}</strong>
                            <div class="muted">{{ $log->descripcion }}</div>
                        </div>
                    @empty
                        <p class="muted">No hay logs administrativos vinculados a este material.</p>
                    @endforelse
                    {{ $logs->links() }}
                </section>
            </div>
        </div>
    </main>
</div>
</body>
</html>
