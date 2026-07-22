<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de equipos - Inventario Lugarth</title>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="workspace-page">
            <header class="workspace-page-header">
                <div><h1>Historial de equipos</h1><p>Ventas y retiros internos con fecha, responsable, cantidad y referencia.</p></div>
                <div class="workspace-page-actions"><a class="btn workspace-action-red" href="{{ route('equipos.withdrawals.create') }}">Retirar equipo</a></div>
            </header>
            <section class="workspace-panel">
                <form class="workspace-filter-bar" method="GET" action="{{ route('equipos.withdrawals.history') }}">
                    <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Equipo, referencia, nota o usuario">
                    <select name="tipo">
                        <option value="">Todos los movimientos</option>
                        <option value="venta" @selected($tipo === 'venta')>Ventas</option>
                        <option value="retiro" @selected($tipo === 'retiro')>Retiros internos</option>
                    </select>
                    <button class="btn workspace-action-teal" type="submit">Filtrar</button>
                </form>
                <table>
                    <thead><tr><th>Fecha y hora</th><th>Equipo</th><th>Tipo</th><th>Cantidad</th><th>Referencia</th><th>Responsable</th><th>Notas</th></tr></thead>
                    <tbody>
                    @forelse($retiros as $retiro)
                        <tr>
                            <td>{{ $retiro->created_at?->format('d/m/Y H:i') }}</td>
                            <td><strong>{{ $retiro->package?->nombre ?? 'Equipo eliminado' }}</strong></td>
                            <td><span class="workspace-chip {{ $retiro->tipo === 'venta' ? 'tone-red' : 'tone-amber' }}">{{ $retiro->tipo === 'venta' ? 'Venta' : 'Retiro interno' }}</span></td>
                            <td>{{ number_format($retiro->cantidad_paquetes) }}</td>
                            <td>{{ $retiro->referencia ?: 'Sin referencia' }}</td>
                            <td>{{ $retiro->user?->name ?? 'Usuario eliminado' }}</td>
                            <td>{{ $retiro->notas ?: 'Sin notas' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="workspace-empty-panel"><strong>Aún no hay retiros de equipos</strong><span>Cuando vendas o retires uno aparecerá aquí.</span></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div style="margin-top:16px">{{ $retiros->links() }}</div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
