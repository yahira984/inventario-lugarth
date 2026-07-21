<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entradas pendientes - Inventario</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        body { margin:0; font-family:"Segoe UI",Tahoma,sans-serif; background:#eef5fb; color:#08233f; }
        .app-shell { display:flex; min-height:100vh; }
        .app-content { flex:1; padding:32px 18px; overflow-x:hidden; }
        .container { max-width:1260px; margin:0 auto; background:#fff; border:1px solid #dbe5f0; border-radius:18px; box-shadow:0 18px 50px rgba(15,60,105,.10); padding:26px; }
        .header { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; margin-bottom:18px; }
        h1 { margin:0; font-size:clamp(28px,4vw,42px); color:#062443; }
        .muted { color:#58718a; font-size:13px; font-weight:700; line-height:1.45; }
        .tabs { display:flex; flex-wrap:wrap; gap:8px; }
        .tab,.btn { min-height:40px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; padding:0 13px; font-weight:900; text-decoration:none; border:1px solid #bfd2e6; background:#fff; color:#075985; cursor:pointer; transition:transform .2s, filter .2s; }
        .tab:hover,.btn:hover { transform:translateY(-1px); filter:brightness(1.04); }
        .tab.active,.btn-blue { background:#0f5fb8; color:#fff; border-color:#0f5fb8; }
        .btn-green { background:#16a34a; color:#fff; border-color:#16a34a; }
        .btn-red { background:#dc2626; color:#fff; border-color:#dc2626; }
        .alert { padding:14px 16px; border-radius:12px; margin-bottom:16px; font-weight:800; }
        .alert-ok { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .alert-bad { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .table-wrap { overflow-x:auto; border:1px solid #dbe5f0; border-radius:14px; }
        table { width:100%; min-width:1180px; border-collapse:collapse; }
        th { background:#f2f7fd; color:#335171; font-size:11px; text-transform:uppercase; letter-spacing:.08em; text-align:left; padding:12px; }
        td { padding:12px; border-top:1px solid #edf2f7; vertical-align:top; }
        strong { color:#08233f; }
        .photo { width:72px; height:72px; object-fit:cover; border-radius:12px; border:1px solid #cfe0f2; cursor:zoom-in; box-shadow:0 8px 20px rgba(15,60,105,.12); transition:transform .2s; }
        .photo:hover { transform:scale(1.05); }
        .photo-stack { display:flex; gap:10px; min-width:164px; }
        .photo-item { display:grid; gap:5px; justify-items:start; }
        .photo-label { color:#58718a; font-size:10px; font-weight:900; text-transform:uppercase; }
        .pill { display:inline-flex; padding:5px 9px; border-radius:999px; font-size:12px; font-weight:900; background:#fef3c7; color:#b45309; }
        .pill.new { margin-bottom:7px; background:#dbeafe; color:#075985; }
        .pill.aprobada { background:#dcfce7; color:#166534; }
        .pill.rechazada { background:#fee2e2; color:#b91c1c; }
        textarea { width:100%; min-height:58px; border:1px solid #bfd2e6; border-radius:10px; padding:9px; font:inherit; color:#08233f; background:#fff; resize:vertical; }
        textarea:focus { outline:3px solid rgba(14,165,233,.20); border-color:#0ea5e9; }
        .actions { display:grid; gap:8px; min-width:210px; }
        .pagination { margin-top:18px; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; color:#58718a; font-size:13px; font-weight:800; }
        .pagination-links { display:flex; flex-wrap:wrap; gap:6px; }
        .pagination a,.pagination span.page { min-width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; padding:0 10px; border-radius:10px; border:1px solid #bfd2e6; color:#075985; background:#fff; text-decoration:none; font-weight:900; }
        .pagination span.current { background:#0f5fb8; border-color:#0f5fb8; color:#fff; }
        .pagination span.disabled { color:#94a3b8; background:#f1f5f9; }
        .viewer { position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(2,6,23,.82); backdrop-filter:blur(10px); z-index:9999; padding:22px; }
        .viewer.open { display:flex; }
        .viewer img { max-width:94vw; max-height:88vh; object-fit:contain; border-radius:18px; background:#fff; box-shadow:0 28px 80px rgba(0,0,0,.45); animation:pop .24s ease; }
        @keyframes pop { from{transform:scale(.94);opacity:0;} to{transform:scale(1);opacity:1;} }
        @media(max-width:860px){ .app-content{padding-top:76px;} .header{display:block;} .tabs{margin-top:12px;} }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="container">
            <div class="header">
                <div>
                    <h1>Entradas pendientes</h1>
                    <p class="muted">Revisa quien subio la entrada, fecha, material, cantidad y evidencia. Al aprobar se suma al stock.</p>
                </div>
                <div class="tabs">
                    <a class="tab {{ $estado === 'pendiente' ? 'active' : '' }}" href="{{ route('admin.entradas.index', ['estado' => 'pendiente']) }}">Pendientes</a>
                    <a class="tab {{ $estado === 'aprobada' ? 'active' : '' }}" href="{{ route('admin.entradas.index', ['estado' => 'aprobada']) }}">Aprobadas</a>
                    <a class="tab {{ $estado === 'rechazada' ? 'active' : '' }}" href="{{ route('admin.entradas.index', ['estado' => 'rechazada']) }}">Rechazadas</a>
                    <a class="tab {{ $estado === 'todas' ? 'active' : '' }}" href="{{ route('admin.entradas.index', ['estado' => 'todas']) }}">Todas</a>
                </div>
            </div>

            @if(session('success')) <div class="alert alert-ok">{{ session('success') }}</div> @endif
            @if($errors->any()) <div class="alert alert-bad">{{ $errors->first() }}</div> @endif

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Evidencia</th>
                            <th>Material</th>
                            <th>Cantidad</th>
                            <th>Solicitado por</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entradas as $entrada)
                            @php
                                $datosMaterial = $entrada->datos_material ?? [];
                                $descripcion = $entrada->material?->descripcion ?: data_get($datosMaterial, 'descripcion', 'Material no disponible');
                                $apodo = $entrada->material?->apodo ?: data_get($datosMaterial, 'apodo');
                                $numeroParte = $entrada->material?->numero_parte ?: data_get($datosMaterial, 'numero_parte', 'N/A');
                                $categoria = $entrada->material?->categoria ?: data_get($datosMaterial, 'categoria', 'Sin categoria');
                                $almacen = $entrada->material?->almacen ?: data_get($datosMaterial, 'almacen', 'Sin asignar');
                                $marca = $entrada->material?->marca ?: data_get($datosMaterial, 'marca', 'Sin marca');
                                $proveedor = $entrada->proveedor ?: ($entrada->material?->proveedor ?: data_get($datosMaterial, 'proveedor', 'Sin capturar'));
                            @endphp
                            <tr>
                                <td>
                                    <div class="photo-stack">
                                        <div class="photo-item">
                                            <span class="photo-label">Evidencia</span>
                                            @if($entrada->evidencia_foto)
                                                <img class="photo" src="{{ asset('storage/'.$entrada->evidencia_foto) }}" alt="Evidencia de recepcion" onclick="abrirImagen(this.src)">
                                            @else
                                                <span class="muted">Sin foto</span>
                                            @endif
                                        </div>
                                        @if($entrada->fotografia)
                                            <div class="photo-item">
                                                <span class="photo-label">Producto</span>
                                                <img class="photo" src="{{ asset('storage/'.$entrada->fotografia) }}" alt="Foto del producto" onclick="abrirImagen(this.src)">
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($entrada->es_material_nuevo)<span class="pill new">Alta de material nuevo</span><br>@endif
                                    <strong>{{ $descripcion }}</strong>
                                    <div class="muted">{{ $apodo ? 'Apodo: '.$apodo.' - ' : '' }}{{ $numeroParte }}</div>
                                    <div class="muted">Codigo: {{ $entrada->codigo_barras ?: 'Sin codigo' }}</div>
                                    <div class="muted">Categoria: {{ $categoria }}</div>
                                    <div class="muted">Almacen: {{ $almacen }}</div>
                                    <div class="muted">Marca: {{ $marca }}</div>
                                    <div class="muted">Stock actual: {{ $entrada->material?->stock ?? 0 }} pzas{{ $entrada->es_material_nuevo && ! $entrada->material_id ? ' (aun no creado)' : '' }}</div>
                                    <div class="muted">Proveedor: {{ $proveedor }}</div>
                                    <div class="muted">
                                        Compra: ${{ number_format((float) ($entrada->costo_unitario ?? 0), 2) }} × {{ $entrada->cantidad }}
                                        = ${{ number_format((float) ($entrada->costo_unitario ?? 0) * $entrada->cantidad, 2) }} MXN
                                    </div>
                                </td>
                                <td><strong>{{ $entrada->cantidad }} pzas</strong></td>
                                <td>{{ $entrada->user?->name ?? 'Usuario no disponible' }}<div class="muted">{{ $entrada->user?->email }}</div></td>
                                <td>{{ $entrada->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="pill {{ $entrada->estado }}">{{ ucfirst($entrada->estado) }}</span>
                                    @if($entrada->approved_at)<div class="muted">Aprobo: {{ $entrada->approver?->name ?? 'Admin' }}</div>@endif
                                    @if($entrada->rejected_at)<div class="muted">Rechazo: {{ $entrada->rejecter?->name ?? 'Admin' }}</div>@endif
                                </td>
                                <td>
                                    @if($entrada->estado === 'pendiente')
                                        <div class="actions">
                                            <form method="POST" action="{{ route('admin.entradas.approve', $entrada) }}">
                                                @csrf
                                                @method('PATCH')
                                                <textarea name="comentario_admin" placeholder="Comentario opcional"></textarea>
                                                <button class="btn btn-green" type="submit">
                                                    {{ $entrada->es_material_nuevo ? 'Aprobar, crear y sumar stock' : 'Aprobar y sumar stock' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.entradas.reject', $entrada) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-red" type="submit">Rechazar</button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="muted">{{ $entrada->comentario_admin ?: 'Sin comentario' }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="muted">No hay entradas en este estado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($entradas->hasPages())
                <div class="pagination">
                    <div>Mostrando {{ $entradas->firstItem() }} a {{ $entradas->lastItem() }} de {{ $entradas->total() }} entradas</div>
                    <div class="pagination-links">
                        @if($entradas->onFirstPage())
                            <span class="page disabled">Anterior</span>
                        @else
                            <a href="{{ $entradas->previousPageUrl() }}">Anterior</a>
                        @endif

                        @foreach($entradas->getUrlRange(1, $entradas->lastPage()) as $page => $url)
                            @if($page === $entradas->currentPage())
                                <span class="page current">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($entradas->hasMorePages())
                            <a href="{{ $entradas->nextPageUrl() }}">Siguiente</a>
                        @else
                            <span class="page disabled">Siguiente</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
<div class="viewer" id="viewer" onclick="cerrarImagen(event)">
    <img id="viewerImg" src="" alt="Evidencia ampliada">
</div>
<script>
    function abrirImagen(src) {
        document.getElementById('viewerImg').src = src;
        document.getElementById('viewer').classList.add('open');
    }
    function cerrarImagen(event) {
        if (event.target.id === 'viewer') {
            document.getElementById('viewer').classList.remove('open');
            document.getElementById('viewerImg').src = '';
        }
    }
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.getElementById('viewer').classList.remove('open');
        }
    });
</script>
</body>
</html>
