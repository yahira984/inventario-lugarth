<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoria - Inventario</title>
    <style>
        body{margin:0;font-family:"Segoe UI",Tahoma,sans-serif;background:#f6f8fb;color:#102033}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;padding:32px 18px}.container{max-width:1280px;margin:0 auto;background:#fff;border:1px solid #dbe5f0;border-radius:16px;padding:26px;box-shadow:0 16px 40px rgba(15,23,42,.08)}.header{display:flex;justify-content:space-between;gap:12px;align-items:flex-end}h1{margin:0 0 6px}.muted{color:#64748b;font-size:13px;font-weight:600}.table-wrap{overflow-x:auto;margin-top:18px}table{width:100%;min-width:1050px;border-collapse:collapse}th{background:#f8fafc;color:#475569;font-size:11px;text-transform:uppercase;text-align:left;padding:12px;border-bottom:1px solid #dbe5f0}td{padding:12px;border-bottom:1px solid #edf2f7;vertical-align:top}.btn{min-height:38px;display:inline-flex;align-items:center;border-radius:10px;background:#2563eb;color:#fff;border:1px solid #1d4ed8;padding:0 12px;text-decoration:none;font-weight:800;cursor:pointer}.btn-danger{background:#1f64d1}.btn:hover{background:#1d4ed8;transform:translateY(-1px)}form{margin:0}.links{margin-top:18px}@media(max-width:860px){.app-content{padding-top:76px}.header{display:block}.header form{margin-top:12px}.btn{width:100%;justify-content:center}}
    </style>
</head>
<body><div class="app-shell">@include('materiales.partials.sidebar')<main class="app-content"><div class="container">
    <div class="header">
        <div><h1>Auditoria</h1><div class="muted">Registro entendible de acciones realizadas en el sistema.</div></div>
        <form method="POST" action="{{ route('admin.auditoria.clear') }}">@csrf @method('DELETE')<button class="btn btn-danger" type="submit" onclick="return confirm('¿Eliminar todos los logs de auditoria?')">Borrar todos</button></form>
    </div>
    @if(session('success'))<div class="muted" style="margin-top:12px;color:#047857">{{ session('success') }}</div>@endif
    <div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Usuario</th><th>Modulo</th><th>Accion</th><th>Descripcion clara</th><th>Ruta</th><th>IP</th><th></th></tr></thead><tbody>
        @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $log->user?->name ?? 'Sistema' }}</td>
                <td>{{ $log->modulo }}</td>
                <td>{{ $log->accion }}</td>
                <td><strong>{{ $log->descripcion }}</strong></td>
                <td>{{ $log->ruta ?? 'N/A' }}</td>
                <td>{{ $log->ip ?? 'N/A' }}</td>
                <td><form method="POST" action="{{ route('admin.auditoria.destroy', $log) }}">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Borrar</button></form></td>
            </tr>
        @endforeach
    </tbody></table></div>
    <div class="links">{{ $logs->links() }}</div>
</div></main></div></body></html>
