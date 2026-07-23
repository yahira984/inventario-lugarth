<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Inventario Lugarth</title>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="workspace-page">
            <header class="workspace-page-header">
                <div><h1>Centro de reportes</h1><p>Consulta el estado actual y descarga documentos para administración, contabilidad y auditoría.</p></div>
            </header>
            <section class="workspace-metrics">
                <div class="workspace-metric blue"><span>Materiales reales</span><strong>{{ number_format($materiales) }}</strong></div>
                <div class="workspace-metric green"><span>Piezas en stock</span><strong>{{ number_format($stockTotal) }}</strong></div>
                <div class="workspace-metric amber"><span>Valor del inventario</span><strong>${{ number_format($valorInventario, 2) }}</strong></div>
            </section>
            <div class="workspace-grid equal">
                <section class="workspace-panel">
                    <div class="workspace-panel-header"><div><h2>Inventario</h2><p>Existencias, ubicación, costos y códigos.</p></div></div>
                    <div class="workspace-page-actions" style="justify-content:flex-start">
                        <a class="btn workspace-action-green" href="{{ route('reportes.inventario.csv') }}">Descargar Excel</a>
                        <a class="btn workspace-action-red" href="{{ route('reportes.inventario.pdf') }}">Abrir PDF</a>
                    </div>
                </section>
                <section class="workspace-panel">
                    <div class="workspace-panel-header"><div><h2>Salidas</h2><p>Retiros, responsables, referencias y existencias resultantes.</p></div></div>
                    <div class="workspace-page-actions" style="justify-content:flex-start">
                        <a class="btn workspace-action-green" href="{{ route('reportes.salidas.csv') }}">Descargar Excel</a>
                        <a class="btn workspace-action-red" href="{{ route('reportes.salidas.pdf') }}">Abrir PDF</a>
                    </div>
                </section>
            </div>
            <section class="workspace-panel" style="margin-top:16px">
                <div class="workspace-panel-header"><div><h2>Movimientos recientes</h2><p>Últimos cambios registrados en el inventario real.</p></div></div>
                <table>
                    <thead><tr><th>Fecha</th><th>Tipo</th><th>Material</th><th>Cantidad</th><th>Stock anterior</th><th>Stock nuevo</th><th>Usuario</th><th>Referencia</th></tr></thead>
                    <tbody>
                    @forelse($movimientos as $movimiento)
                        <tr>
                            <td>{{ $movimiento->created_at?->format('d/m/Y H:i') }}</td>
                            <td><span class="workspace-chip {{ $movimiento->tipo === 'salida' ? 'tone-red' : 'tone-green' }}">{{ ucfirst($movimiento->tipo) }}</span></td>
                            <td><strong>{{ $movimiento->material?->descripcion ?? 'Material eliminado' }}</strong><br><small>{{ $movimiento->material?->numero_parte }}</small></td>
                            <td>{{ number_format($movimiento->cantidad) }}</td><td>{{ number_format($movimiento->stock_anterior) }}</td><td>{{ number_format($movimiento->stock_nuevo) }}</td>
                            <td>{{ $movimiento->user?->name ?? 'Sistema' }}</td><td>{{ $movimiento->referencia ?: 'Sin referencia' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="workspace-empty-panel"><strong>Sin movimientos</strong><span>Las entradas y salidas aparecerán aquí.</span></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</div>
</body>
</html>
