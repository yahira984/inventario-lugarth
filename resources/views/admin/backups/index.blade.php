<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldos - Inventario</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        body{margin:0;font-family:"Segoe UI",Tahoma,sans-serif;background:#f6f8fb;color:#102033}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;padding:32px 18px}.container{max-width:980px;margin:0 auto;background:#fff;border:1px solid #dbe5f0;border-radius:16px;padding:26px;box-shadow:0 16px 40px rgba(15,23,42,.08)}h1{margin:0 0 6px}.muted{color:#64748b;font-size:13px;font-weight:600}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:20px}.box{border:1px solid #dbe5f0;border-radius:14px;padding:18px;background:#f8fafc}.btn{min-height:42px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;background:#2563eb;color:#fff;border:1px solid #1d4ed8;padding:0 14px;text-decoration:none;font-weight:800;cursor:pointer}.btn:hover{background:#1d4ed8;transform:translateY(-1px)}input{width:100%;margin:12px 0;min-height:42px}.list{margin-top:20px}.item{padding:10px 0;border-bottom:1px solid #e2e8f0;font-size:13px}.success{margin-top:14px;color:#047857;font-weight:800}@media(max-width:860px){.app-content{padding-top:76px}.grid{grid-template-columns:1fr}.btn{width:100%}}
    </style>
</head>
<body><div class="app-shell">@include('materiales.partials.sidebar')<main class="app-content"><div class="container">
    <h1>Respaldos de base de datos</h1>
    <div class="muted">Genera una copia completa en SQL y restaura una copia cuando sea necesario.</div>
    @if(session('success'))<div class="success">{{ session('success') }}</div>@endif
    <section class="grid">
        <div class="box">
            <h2>Crear respaldo</h2>
            <p class="muted">Descarga un archivo .sql con todas las tablas y datos actuales.</p>
            <form method="POST" action="{{ route('admin.backups.store') }}">@csrf <button class="btn btn-green" type="submit">Crear y descargar respaldo</button></form>
        </div>
        <div class="box">
            <h2>Restaurar respaldo</h2>
            <p class="muted">Usa esto solo si sabes que el respaldo es correcto. Reemplaza datos existentes.</p>
            <form method="POST" action="{{ route('admin.backups.restore') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="backup_sql" accept=".sql,.txt" required>
                <button class="btn btn-red" type="submit" onclick="return confirm('¿Restaurar esta base de datos?')">Restaurar SQL</button>
            </form>
        </div>
    </section>
    <div class="list">
        <h2>Respaldos guardados</h2>
        @forelse($backups as $backup)
            <div class="item">{{ $backup }}</div>
        @empty
            <div class="muted">Todavia no hay respaldos guardados.</div>
        @endforelse
    </div>
</div></main></div></body></html>
