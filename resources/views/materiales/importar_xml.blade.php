<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar facturas XML - Inventario Lugarth</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f8fc;color:#0b2948;font-family:"Segoe UI",Tahoma,sans-serif}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;min-width:0;padding:34px 22px 90px}.page{width:min(1050px,100%);margin:auto}.head{padding:22px 0;border-bottom:1px solid #d5e4f1}.head h1{margin:0;font-size:clamp(28px,4vw,40px)}.head p,.muted{color:#60768c;font-size:13px;font-weight:650;line-height:1.5}.panel{margin-top:18px;background:#fff;border:1px solid #d5e4f1;border-radius:8px;padding:22px}.info{padding:14px;border:1px solid #c4b5fd;background:#f5f3ff;color:#5b21b6;border-radius:8px;font-weight:700;line-height:1.5}.field{display:grid;gap:7px;margin-top:16px}.field label{font-size:11px;font-weight:850;text-transform:uppercase;color:#315673}.field input,.field select{width:100%;min-height:48px;border:1px solid #b9cee0;border-radius:8px;background:#fff;color:#0b2948;padding:10px 12px;font:inherit}.field input[type=file]{border-style:dashed;background:#f8fbfe}.field input[type=file]::file-selector-button{margin-right:12px;border:0;border-radius:7px;padding:9px 12px;background:#7c3aed;color:#fff;font-weight:800;cursor:pointer}.selected-files{display:grid;gap:6px;margin-top:10px}.selected-file{display:flex;justify-content:space-between;gap:10px;padding:9px 11px;background:#f7fafc;border:1px solid #e0e9f1;border-radius:7px;font-size:12px}.actions{display:flex;gap:9px;flex-wrap:wrap;margin-top:18px}.btn{min-height:44px;border:0;border-radius:8px;padding:0 16px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;font-weight:850;cursor:pointer;transition:.16s}.btn:hover{filter:brightness(1.05);transform:translateY(-1px);box-shadow:0 8px 18px rgba(75,32,150,.17)}.purple{background:#7c3aed;color:#fff}.soft{background:#fff;color:#075c9d;border:1px solid #b9d5ea}.alert{margin-top:15px;padding:13px 15px;border-radius:8px;background:#fff1f2;color:#a2102d;border:1px solid #fecdd3;font-weight:750}.steps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:18px}.step{padding:14px;background:#fff;border:1px solid #d5e4f1;border-radius:8px}.step span{display:inline-flex;width:25px;height:25px;align-items:center;justify-content:center;background:#ede9fe;color:#6d28d9;border-radius:50%;font-weight:900}.step strong{display:block;margin-top:8px}.step small{display:block;color:#60768c;margin-top:4px}@media(max-width:800px){.app-content{padding:76px 14px 90px}.steps{grid-template-columns:1fr}.actions .btn{width:100%}.panel{padding:17px}}
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')
    <main class="app-content">
        <div class="page">
            <header class="head"><h1>Importar facturas XML</h1><p>Selecciona una o hasta 20 facturas CFDI. Primero veras todos los datos y nada cambiara hasta confirmar.</p></header>
            @if(session('error'))<div class="alert">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif
            <section class="panel">
                <div class="info">El XML aporta cantidad, NoIdentificacion, Clave SAT, unidad, precio, proveedor, impuestos, UUID y folio. No contiene codigos de barras ni fotografias.</div>
                <form action="{{ route('materiales.xml.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="field">
                        <label for="xml_files">Facturas XML</label>
                        <input type="file" name="xml_files[]" id="xml_files" accept=".xml,text/xml,application/xml" multiple required>
                        <small class="muted">Puedes elegir varios archivos de una sola vez. Maximo 4 MB por archivo.</small>
                        <div class="selected-files" id="selectedFiles"></div>
                    </div>
                    @if($ordenes->isNotEmpty())
                        <div class="field">
                            <label for="purchase_order_id">Vincular con orden recibida (opcional)</label>
                            <select name="purchase_order_id" id="purchase_order_id">
                                <option value="">Sin vincular a una orden</option>
                                @foreach($ordenes as $orden)
                                    <option value="{{ $orden->id }}" @selected((int)$selectedOrder===$orden->id)>{{ $orden->referencia }} · {{ $orden->proveedor }} · ${{ number_format((float)$orden->total,2) }}</option>
                                @endforeach
                            </select>
                            <small class="muted">Si eliges una orden, al confirmar el XML quedara como facturada.</small>
                        </div>
                    @endif
                    <div class="actions"><button class="btn purple" type="submit">Previsualizar facturas</button><a class="btn soft" href="{{ route('materiales.index') }}">Volver al inventario</a></div>
                </form>
            </section>
            <div class="steps">
                <div class="step"><span>1</span><strong>Selecciona</strong><small>Una o varias facturas del SAT.</small></div>
                <div class="step"><span>2</span><strong>Revisa</strong><small>Duplicados, conceptos, precios y categorias.</small></div>
                <div class="step"><span>3</span><strong>Confirma</strong><small>Solo entonces se sumara el stock.</small></div>
            </div>
        </div>
    </main>
</div>
<script>
document.getElementById('xml_files')?.addEventListener('change',event=>{
    const target=document.getElementById('selectedFiles');target.replaceChildren();
    [...event.target.files].forEach(file=>{const row=document.createElement('div');row.className='selected-file';const name=document.createElement('strong');name.textContent=file.name;const size=document.createElement('span');size.textContent=`${Math.max(.01,file.size/1024/1024).toFixed(2)} MB`;row.append(name,size);target.append(row)});
});
</script>
</body>
</html>
