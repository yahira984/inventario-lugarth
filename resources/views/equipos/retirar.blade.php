<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retirar equipo - Inventario Lugarth</title>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="workspace-page">
            <header class="workspace-page-header">
                <div>
                    <h1>Retirar o vender equipo</h1>
                    <p>Selecciona un equipo. Antes de descontar, el sistema comprueba todas sus piezas y te indica exactamente qué falta.</p>
                </div>
                <div class="workspace-page-actions">
                    <a class="btn workspace-action-soft" href="{{ route('equipos.index') }}">Administrar equipos</a>
                    <a class="btn workspace-action-teal" href="{{ route('equipos.withdrawals.history') }}">Ver historial</a>
                </div>
            </header>

            <section class="workspace-panel">
                <form class="workspace-filter-bar" method="GET" action="{{ route('equipos.withdrawals.create') }}">
                    <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Nombre o código del equipo">
                    <button class="btn workspace-action-teal" type="submit">Buscar equipo</button>
                    @if($buscar !== '')<a class="btn workspace-action-soft" href="{{ route('equipos.withdrawals.create') }}">Limpiar</a>@endif
                </form>

                <div class="workspace-card-list">
                    @forelse($equipos as $equipo)
                        @php($disponibilidad = $equipo->evaluarDisponibilidad())
                        <article class="workspace-item-card">
                            <div>
                                <span class="workspace-chip tone-purple">{{ $equipo->codigo ?: 'Sin código' }}</span>
                                <h3 style="margin-top:9px">{{ $equipo->nombre }}</h3>
                                <p>{{ $equipo->items_count }} renglones configurados.</p>
                            </div>
                            <div>
                                @if($disponibilidad['sin_piezas'])
                                    <span class="workspace-chip tone-red">Sin piezas configuradas</span>
                                @elseif($disponibilidad['sin_vincular']->isNotEmpty())
                                    <span class="workspace-chip tone-amber">{{ $disponibilidad['sin_vincular']->count() }} sin vincular</span>
                                @elseif($disponibilidad['faltantes']->isNotEmpty())
                                    <span class="workspace-chip tone-red">Faltan {{ $disponibilidad['faltantes']->count() }} materiales</span>
                                    <p style="margin-top:8px">{{ $disponibilidad['faltantes']->pluck('descripcion')->join(', ') }}</p>
                                @else
                                    <span class="workspace-chip tone-green">Stock completo</span>
                                @endif
                            </div>
                            <footer>
                                <a class="btn {{ $disponibilidad['listo'] ? 'workspace-action-red' : 'workspace-action-amber' }}" href="{{ route('equipos.show', $equipo) }}#vender-equipo">
                                    {{ $disponibilidad['listo'] ? 'Retirar o vender' : 'Revisar faltantes' }}
                                </a>
                            </footer>
                        </article>
                    @empty
                        <div class="workspace-empty-panel" style="grid-column:1/-1"><strong>No hay equipos disponibles</strong><span>Crea un equipo y agrega las piezas que utiliza.</span></div>
                    @endforelse
                </div>
                <div style="margin-top:16px">{{ $equipos->links() }}</div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
