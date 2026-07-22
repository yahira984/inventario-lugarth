<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corregir entrada - Inventario</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <style>
        body { margin:0; font-family:"Segoe UI",Tahoma,sans-serif; background:#eef5fb; color:#08233f; }
        .app-shell { display:flex; min-height:100vh; }
        .app-content { flex:1; min-width:0; padding:32px 18px; overflow-x:hidden; }
        .container { width:100%; max-width:1380px; margin:0 auto; background:#fff; border:1px solid #dbe5f0; border-radius:18px; box-shadow:0 18px 50px rgba(15,60,105,.10); overflow:hidden; }
        .page-header { display:flex; justify-content:space-between; gap:18px; align-items:flex-start; padding:26px 28px 22px; border-bottom:1px solid #dbe5f0; background:#f8fbff; }
        h1 { margin:0 0 7px; color:#062443; font-size:clamp(26px,3vw,38px); }
        h2 { margin:0; color:#08233f; font-size:21px; }
        p { margin:0; }
        .muted { color:#58718a; font-size:13px; font-weight:650; line-height:1.5; }
        .status { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:#fff7ed; color:#9a3412; border:1px solid #fdba74; font-size:12px; font-weight:900; white-space:nowrap; }
        .status::before { content:""; width:8px; height:8px; border-radius:50%; background:#f97316; }
        .notice { margin:22px 28px 0; padding:14px 16px; border:1px solid #93c5fd; border-left:4px solid #2563eb; border-radius:10px; color:#1e3a5f; background:#eff6ff; font-size:14px; font-weight:750; line-height:1.5; }
        .alert { margin:22px 28px 0; padding:14px 16px; border-radius:10px; background:#fee2e2; color:#991b1b; border:1px solid #fecaca; font-weight:750; }
        form { padding:6px 28px 28px; }
        .section { padding:24px 0; border-bottom:1px solid #e5edf6; }
        .section:last-of-type { border-bottom:0; }
        .section-heading { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:17px; }
        .section-heading .muted { max-width:700px; margin-top:5px; }
        .grid { display:grid; grid-template-columns:repeat(12,minmax(0,1fr)); gap:16px; }
        .col-12 { grid-column:span 12; }
        .col-8 { grid-column:span 8; }
        .col-6 { grid-column:span 6; }
        .col-4 { grid-column:span 4; }
        .col-3 { grid-column:span 3; }
        .field { min-width:0; }
        label { display:block; margin-bottom:7px; color:#254665; font-size:12px; font-weight:900; text-transform:uppercase; }
        .required { color:#dc2626; }
        input,select,textarea { width:100%; min-height:46px; border:1px solid #b9cde1; border-radius:9px; padding:10px 12px; color:#08233f; background:#fff; font:inherit; transition:border-color .18s,box-shadow .18s; }
        input::placeholder,textarea::placeholder { color:#7c91a6; }
        input:focus,select:focus,textarea:focus { outline:0; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.14); }
        textarea { min-height:92px; resize:vertical; }
        .help { display:block; margin-top:6px; color:#6b8197; font-size:12px; line-height:1.4; }
        .photo-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .photo-box { display:grid; grid-template-columns:132px minmax(0,1fr); gap:16px; align-items:center; padding:15px; border:1px solid #d5e2ef; border-radius:12px; background:#f8fbff; }
        .photo-preview { width:132px; height:112px; border-radius:9px; border:1px solid #bfd2e6; background:#fff; object-fit:contain; cursor:zoom-in; }
        .photo-placeholder { width:132px; height:112px; display:flex; align-items:center; justify-content:center; border:1px dashed #9fb8d1; border-radius:9px; color:#58718a; background:#fff; font-size:12px; font-weight:850; text-align:center; }
        .material-summary { display:grid; grid-template-columns:84px minmax(0,1fr); gap:14px; align-items:center; margin-top:12px; padding:13px; border:1px solid #bfdbfe; border-radius:10px; background:#eff6ff; }
        .material-summary img { width:84px; height:76px; object-fit:contain; border:1px solid #c7d9eb; border-radius:8px; background:#fff; }
        .material-summary strong { display:block; margin-bottom:4px; color:#08233f; }
        .purchase-total { min-height:46px; display:flex; align-items:center; padding:0 13px; border-radius:9px; background:#ecfdf5; border:1px solid #86efac; color:#166534; font-weight:900; }
        .actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:10px; padding-top:24px; }
        .btn { min-height:44px; display:inline-flex; align-items:center; justify-content:center; border-radius:9px; padding:0 17px; border:1px solid #bfd2e6; background:#fff; color:#075985; text-decoration:none; font-weight:900; cursor:pointer; transition:transform .18s,filter .18s,box-shadow .18s; }
        .btn:hover { transform:translateY(-1px); filter:brightness(1.04); box-shadow:0 8px 18px rgba(15,60,105,.12); }
        .btn-save { background:#d97706; border-color:#d97706; color:#fff; }
        .viewer { position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; padding:22px; background:rgba(2,6,23,.84); backdrop-filter:blur(9px); }
        .viewer.open { display:flex; }
        .viewer img { max-width:94vw; max-height:88vh; object-fit:contain; border-radius:14px; background:#fff; box-shadow:0 28px 80px rgba(0,0,0,.45); animation:pop .22s ease; }
        @keyframes pop { from{transform:scale(.95);opacity:0} to{transform:scale(1);opacity:1} }
        @media(max-width:980px){ .col-8,.col-6,.col-4,.col-3{grid-column:span 6;} .photo-grid{grid-template-columns:1fr;} }
        @media(max-width:700px){ .app-content{padding:76px 10px 18px;} .container{border-radius:12px;} .page-header{display:block;padding:20px;} .status{margin-top:13px;} .notice,.alert{margin:16px 20px 0;} form{padding:4px 20px 22px;} .section{padding:20px 0;} .section-heading{display:block;} .col-8,.col-6,.col-4,.col-3{grid-column:span 12;} .photo-box{grid-template-columns:92px minmax(0,1fr);padding:12px;} .photo-preview,.photo-placeholder{width:92px;height:88px;} .actions{display:grid;grid-template-columns:1fr;} .btn{width:100%;} }
    </style>
</head>
<body>
@php
    $datosMaterial = $entrada->datos_material ?? [];
    $productoFoto = $entrada->es_material_nuevo ? $entrada->fotografia : $entrada->material?->fotografia;
@endphp
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="container">
            <header class="page-header">
                <div>
                    <h1>Corregir entrada #{{ $entrada->id }}</h1>
                    <p class="muted">Solicitud de {{ $entrada->user?->name ?? 'Usuario no disponible' }} registrada el {{ $entrada->created_at->format('d/m/Y H:i') }}.</p>
                </div>
                <span class="status">Pendiente de aprobacion</span>
            </header>

            <div class="notice">Guardar estas correcciones no suma stock. La existencia cambiara solamente cuando regreses a la lista y apruebes la entrada.</div>

            @if($errors->any())
                <div class="alert">
                    <strong>Revisa la informacion:</strong>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.entradas.update', $entrada) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <section class="section">
                    <div class="section-heading">
                        <div>
                            <h2>Fotos de revision</h2>
                            <p class="muted">Abre las imagenes para revisarlas en grande o carga una nueva si la anterior no corresponde.</p>
                        </div>
                    </div>
                    <div class="photo-grid">
                        <div class="photo-box">
                            @if($entrada->evidencia_foto)
                                <img id="evidencePreview" class="photo-preview" src="{{ asset('storage/'.$entrada->evidencia_foto) }}" alt="Evidencia actual" onclick="openViewer(this.src)">
                            @else
                                <div id="evidencePlaceholder" class="photo-placeholder">Sin evidencia</div>
                                <img id="evidencePreview" class="photo-preview" src="" alt="Nueva evidencia" style="display:none" onclick="openViewer(this.src)">
                            @endif
                            <div class="field">
                                <label for="evidencia_foto">Reemplazar evidencia</label>
                                <input id="evidencia_foto" name="evidencia_foto" type="file" accept="image/jpeg,image/png,image/webp" onchange="previewFile(this, 'evidencePreview', 'evidencePlaceholder')">
                                <span class="help">JPG, PNG o WEBP. La imagen se optimiza automaticamente.</span>
                            </div>
                        </div>

                        <div class="photo-box">
                            @if($productoFoto)
                                <img id="productPreview" class="photo-preview" src="{{ asset('storage/'.$productoFoto) }}" alt="Foto del producto" onclick="openViewer(this.src)">
                            @else
                                <div id="productPlaceholder" class="photo-placeholder">Sin foto del producto</div>
                                <img id="productPreview" class="photo-preview" src="" alt="Nueva foto del producto" style="display:none" onclick="openViewer(this.src)">
                            @endif
                            <div class="field">
                                <label>Foto del producto</label>
                                @if($entrada->es_material_nuevo)
                                    <input id="fotografia" name="fotografia" type="file" accept="image/jpeg,image/png,image/webp" onchange="previewFile(this, 'productPreview', 'productPlaceholder')">
                                    <span class="help">Esta sera la imagen que aparezca en el inventario.</span>
                                @else
                                    <span class="muted">La foto pertenece a la pieza seleccionada en inventario.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                @if(!$entrada->es_material_nuevo)
                    <section class="section">
                        <div class="section-heading">
                            <div>
                                <h2>Pieza que recibira el stock</h2>
                                <p class="muted">Corrige la seleccion si el almacenista eligio una pieza equivocada.</p>
                            </div>
                        </div>
                        <div class="field">
                            <label for="material_id">Material del inventario <span class="required">*</span></label>
                            <select id="material_id" name="material_id" required onchange="updateMaterialSummary()">
                                @foreach($materiales as $material)
                                    <option
                                        value="{{ $material->id }}"
                                        data-name="{{ $material->descripcion }}"
                                        data-category="{{ $material->categoria ?: 'Sin categoria' }}"
                                        data-location="{{ $material->almacen ?: 'Sin almacen asignado' }}"
                                        data-stock="{{ $material->stock }}"
                                        data-photo="{{ $material->fotografia ? asset('storage/'.$material->fotografia) : '' }}"
                                        @selected((int) old('material_id', $entrada->material_id) === $material->id)
                                    >
                                        {{ $material->descripcion }}{{ $material->apodo ? ' ('.$material->apodo.')' : '' }} | {{ $material->numero_parte ?: 'Sin no. parte' }} | {{ $material->categoria ?: 'Sin categoria' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="material-summary" id="materialSummary">
                            <img id="selectedMaterialPhoto" src="" alt="Foto de la pieza seleccionada" style="display:none">
                            <div id="selectedMaterialPhotoEmpty" class="photo-placeholder" style="width:84px;height:76px">Sin foto</div>
                            <div>
                                <strong id="selectedMaterialName">Pieza seleccionada</strong>
                                <div id="selectedMaterialMeta" class="muted"></div>
                            </div>
                        </div>
                    </section>
                @else
                    <section class="section">
                        <div class="section-heading">
                            <div>
                                <h2>Datos del material nuevo</h2>
                                <p class="muted">Estos datos se usaran para crear la pieza cuando apruebes la entrada.</p>
                            </div>
                        </div>
                        <div class="grid">
                            <div class="field col-8">
                                <label for="descripcion">Nombre o descripcion <span class="required">*</span></label>
                                <input id="descripcion" name="descripcion" type="text" required value="{{ old('descripcion', data_get($datosMaterial, 'descripcion')) }}" placeholder="Nombre completo de la pieza">
                            </div>
                            <div class="field col-4">
                                <label for="apodo">Apodo</label>
                                <input id="apodo" name="apodo" type="text" value="{{ old('apodo', data_get($datosMaterial, 'apodo')) }}" placeholder="Como la conocen en almacen">
                            </div>
                            <div class="field col-4">
                                <label for="categoria">Categoria</label>
                                @php($categoriaActual = old('categoria', data_get($datosMaterial, 'categoria', 'Sin categoria')) ?: 'Sin categoria')
                                <select id="categoria" name="categoria">
                                    <option value="{{ $categoriaActual }}">{{ $categoriaActual }}</option>
                                    @foreach($categorias as $categoria)
                                        @if(strcasecmp($categoria, $categoriaActual) !== 0)
                                            <option value="{{ $categoria }}">{{ $categoria }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <span class="help">Selecciona una de las categorias registradas en el sistema.</span>
                            </div>
                            <div class="field col-4">
                                <label for="almacen">Almacen</label>
                                <input id="almacen" name="almacen" type="text" value="{{ old('almacen', data_get($datosMaterial, 'almacen')) }}" placeholder="Almacen, rack o ubicacion">
                            </div>
                            <div class="field col-4">
                                <label for="marca">Marca</label>
                                <input id="marca" name="marca" type="text" value="{{ old('marca', data_get($datosMaterial, 'marca')) }}" placeholder="Marca o fabricante">
                            </div>
                            <div class="field col-4">
                                <label for="numero_parte">No. de parte</label>
                                <input id="numero_parte" name="numero_parte" type="text" value="{{ old('numero_parte', data_get($datosMaterial, 'numero_parte')) }}">
                            </div>
                            <div class="field col-4">
                                <label for="codigo_barras">Codigo de barras</label>
                                <input id="codigo_barras" name="codigo_barras" type="text" value="{{ old('codigo_barras', $entrada->codigo_barras ?: data_get($datosMaterial, 'codigo_barras')) }}">
                                <span class="help">Si ya pertenece a una pieza existente, la entrada se sumara a esa pieza.</span>
                            </div>
                            <div class="field col-4">
                                <label for="unidad">Unidad</label>
                                <input id="unidad" name="unidad" type="text" value="{{ old('unidad', data_get($datosMaterial, 'unidad', 'pza')) }}" placeholder="pza">
                            </div>
                            <div class="field col-3">
                                <label for="clave_sat">Clave SAT</label>
                                <input id="clave_sat" name="clave_sat" type="text" value="{{ old('clave_sat', data_get($datosMaterial, 'clave_sat')) }}">
                            </div>
                            <div class="field col-3">
                                <label for="clave_unidad">Clave de unidad</label>
                                <input id="clave_unidad" name="clave_unidad" type="text" value="{{ old('clave_unidad', data_get($datosMaterial, 'clave_unidad')) }}">
                            </div>
                            <div class="field col-3">
                                <label for="stock_minimo">Stock minimo</label>
                                <input id="stock_minimo" name="stock_minimo" type="number" min="0" value="{{ old('stock_minimo', data_get($datosMaterial, 'stock_minimo', 0)) }}">
                            </div>
                            <div class="field col-3">
                                <label for="stock_maximo">Stock maximo</label>
                                <input id="stock_maximo" name="stock_maximo" type="number" min="0" value="{{ old('stock_maximo', data_get($datosMaterial, 'stock_maximo', 0)) }}">
                            </div>
                            <div class="field col-4">
                                <label for="proveedor_rfc">RFC del proveedor</label>
                                <input id="proveedor_rfc" name="proveedor_rfc" type="text" value="{{ old('proveedor_rfc', data_get($datosMaterial, 'proveedor_rfc')) }}">
                            </div>
                            <div class="field col-3">
                                <label for="moneda">Moneda</label>
                                <select id="moneda" name="moneda">
                                    @foreach(['MXN', 'USD', 'EUR'] as $moneda)
                                        <option value="{{ $moneda }}" @selected(old('moneda', data_get($datosMaterial, 'moneda', 'MXN')) === $moneda)>{{ $moneda }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </section>
                @endif

                <section class="section">
                    <div class="section-heading">
                        <div>
                            <h2>Datos de la entrada</h2>
                            <p class="muted">La cantidad y el precio corregidos son los que se utilizaran al actualizar el stock y registrar el movimiento.</p>
                        </div>
                    </div>
                    <div class="grid">
                        <div class="field col-3">
                            <label for="cantidad">Cantidad recibida <span class="required">*</span></label>
                            <input id="cantidad" name="cantidad" type="number" min="1" required value="{{ old('cantidad', $entrada->cantidad) }}" oninput="updateTotal()">
                        </div>
                        <div class="field col-3">
                            <label for="costo_unitario">Precio por unidad</label>
                            <input id="costo_unitario" name="costo_unitario" type="number" min="0" step="0.01" value="{{ old('costo_unitario', $entrada->costo_unitario ?? data_get($datosMaterial, 'costo_unitario', 0)) }}" oninput="updateTotal()">
                        </div>
                        <div class="field col-6">
                            <label>Total de la entrada</label>
                            <div id="purchaseTotal" class="purchase-total">$0.00 MXN</div>
                        </div>
                        <div class="field col-6">
                            <label for="proveedor">Proveedor</label>
                            <input id="proveedor" name="proveedor" type="text" value="{{ old('proveedor', $entrada->proveedor ?: data_get($datosMaterial, 'proveedor', $entrada->material?->proveedor)) }}" placeholder="Empresa o persona que suministro la pieza">
                        </div>
                        <div class="field col-6">
                            <label for="referencia">Referencia u orden</label>
                            <input id="referencia" name="referencia" type="text" value="{{ old('referencia', $entrada->referencia) }}" placeholder="Factura, orden de compra o folio">
                        </div>
                        <div class="field col-6">
                            <label for="motivo">Motivo</label>
                            <input id="motivo" name="motivo" type="text" value="{{ old('motivo', $entrada->motivo) }}" placeholder="Recepcion de compra, devolucion u otro motivo">
                        </div>
                        <div class="field col-6">
                            <label for="comentario_admin">Nota del administrador</label>
                            <textarea id="comentario_admin" name="comentario_admin" placeholder="Explica brevemente que se corrigio">{{ old('comentario_admin', $entrada->comentario_admin) }}</textarea>
                        </div>
                    </div>
                </section>

                <div class="actions">
                    <a class="btn" href="{{ route('admin.entradas.index', ['estado' => 'pendiente']) }}">Cancelar y volver</a>
                    <button class="btn btn-save" type="submit">Guardar correcciones</button>
                </div>
            </form>
        </div>
    </main>
</div>

<div class="viewer" id="imageViewer" onclick="closeViewer(event)">
    <img id="viewerImage" src="" alt="Imagen ampliada">
</div>

<script>
    function updateTotal() {
        const quantity = Number(document.getElementById('cantidad').value || 0);
        const unitCost = Number(document.getElementById('costo_unitario').value || 0);
        document.getElementById('purchaseTotal').textContent = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(quantity * unitCost) + ' MXN';
    }

    function previewFile(input, imageId, placeholderId) {
        if (!input.files || !input.files[0]) return;
        const image = document.getElementById(imageId);
        const placeholder = document.getElementById(placeholderId);
        image.src = URL.createObjectURL(input.files[0]);
        image.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
    }

    function updateMaterialSummary() {
        const select = document.getElementById('material_id');
        if (!select) return;
        const option = select.options[select.selectedIndex];
        const photo = document.getElementById('selectedMaterialPhoto');
        const empty = document.getElementById('selectedMaterialPhotoEmpty');
        document.getElementById('selectedMaterialName').textContent = option.dataset.name || 'Pieza seleccionada';
        document.getElementById('selectedMaterialMeta').textContent =
            (option.dataset.category || 'Sin categoria') + ' | ' +
            (option.dataset.location || 'Sin almacen') + ' | Stock actual: ' +
            (option.dataset.stock || '0') + ' pzas';

        if (option.dataset.photo) {
            photo.src = option.dataset.photo;
            photo.style.display = 'block';
            empty.style.display = 'none';
        } else {
            photo.style.display = 'none';
            empty.style.display = 'flex';
        }
    }

    function openViewer(src) {
        if (!src) return;
        document.getElementById('viewerImage').src = src;
        document.getElementById('imageViewer').classList.add('open');
    }

    function closeViewer(event) {
        if (event.target.id === 'imageViewer') {
            document.getElementById('imageViewer').classList.remove('open');
        }
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') document.getElementById('imageViewer').classList.remove('open');
    });

    updateTotal();
    updateMaterialSummary();
</script>
</body>
</html>
