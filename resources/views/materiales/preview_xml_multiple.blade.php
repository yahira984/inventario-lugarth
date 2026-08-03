<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Vista previa de facturas XML - Inventario Lugarth</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f8fc;color:#0b2948;font-family:"Segoe UI",Tahoma,sans-serif}.app-shell{display:flex;min-height:100vh}.app-content{flex:1;min-width:0;padding:30px 22px 90px}.page{width:min(1550px,100%);margin:auto}.head{display:flex;justify-content:space-between;gap:14px;padding:20px 0;border-bottom:1px solid #d5e4f1}.head h1{margin:0;font-size:clamp(27px,3vw,38px)}.muted{color:#60768c;font-size:13px;font-weight:650;line-height:1.45}.btn{min-height:42px;border:0;border-radius:8px;padding:0 14px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;font-weight:850;cursor:pointer}.purple{background:#7c3aed;color:#fff}.soft{background:#fff;color:#075c9d;border:1px solid #b9d5ea}.green{background:#059669;color:#fff}.invoice-list{display:grid;gap:14px;margin-top:18px}.invoice{background:#fff;border:1px solid #d5e4f1;border-radius:8px;overflow:hidden}.invoice.done{border-color:#6ee7b7}.invoice.error{border-color:#fda4af}.invoice-head{display:flex;justify-content:space-between;gap:12px;padding:15px;background:#f8fbfe;border-bottom:1px solid #dce7f0}.invoice-head h2{margin:0;font-size:18px}.chip{display:inline-flex;padding:5px 8px;border-radius:99px;background:#dceeff;color:#075c9d;font-size:11px;font-weight:850}.chip.duplicate{background:#fee2e2;color:#a31818}.meta{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;padding:14px}.meta div{padding:10px;background:#f7fafc;border:1px solid #e2ebf3;border-radius:7px}.meta small{display:block;color:#60768c;font-size:10px;font-weight:850;text-transform:uppercase}.meta strong{display:block;margin-top:4px;overflow-wrap:anywhere}.table-wrap{overflow:auto;border-top:1px solid #dce7f0}.table{width:100%;border-collapse:collapse;min-width:950px}.table th,.table td{padding:10px;border-bottom:1px solid #e2ebf3;text-align:left}.table th{background:#eaf4fc;color:#315673;font-size:10px;text-transform:uppercase}.table select{min-height:38px;border:1px solid #b9cee0;border-radius:7px;background:#fff;padding:6px}.invoice-actions{display:flex;justify-content:flex-end;gap:8px;padding:13px}.result{margin:0 14px 14px;padding:11px;border-radius:7px;font-weight:750}.result.ok{background:#ecfdf5;color:#076b4c}.result.bad{background:#fff1f2;color:#a2102d}.batch-actions{position:sticky;bottom:12px;z-index:4;display:flex;justify-content:flex-end;gap:9px;margin-top:16px;padding:12px;background:rgba(255,255,255,.96);border:1px solid #d5e4f1;border-radius:8px;box-shadow:0 12px 30px rgba(15,60,105,.16)}@media(max-width:900px){.app-content{padding:76px 12px 90px}.head{display:block}.head .btn{margin-top:10px}.meta{grid-template-columns:1fr 1fr}.invoice-head{display:block}.chip{margin-top:8px}.batch-actions{display:grid}.batch-actions .btn{width:100%}}@media(max-width:520px){.meta{grid-template-columns:1fr}}
    </style>
</head>
<body><div class="app-shell">@include('materiales.partials.sidebar')
<main class="app-content"><div class="page">
    <header class="head"><div><h1>Vista previa de {{ $previews->count() }} facturas</h1><p class="muted">Revisa cada CFDI. Los duplicados se muestran, pero no pueden volver a sumar stock.</p></div><a class="btn soft" href="{{ route('materiales.xml.create') }}">Elegir otros archivos</a></header>
    <div class="invoice-list">
        @foreach($previews as $invoiceIndex=>$preview)
            @php($factura=$preview['factura'])
            <article class="invoice" id="invoice-{{ $invoiceIndex }}">
                <div class="invoice-head"><div><h2>{{ $preview['filename'] }}</h2><div class="muted">{{ $factura['emisor']['nombre'] }} · UUID {{ $factura['uuid'] }}</div></div><span class="chip {{ $preview['duplicate']?'duplicate':'' }}">{{ $preview['duplicate']?'Ya importada':'Lista para importar' }}</span></div>
                <div class="meta">
                    <div><small>Factura</small><strong>{{ trim($factura['serie'].' '.$factura['folio']) ?: 'Sin folio' }}</strong></div>
                    <div><small>Fecha</small><strong>{{ $factura['fecha'] ?: 'N/A' }}</strong></div>
                    <div><small>Total</small><strong>${{ number_format((float)$factura['total'],2) }} {{ $factura['moneda'] }}</strong></div>
                    <div><small>Conceptos</small><strong>{{ count($factura['conceptos']) }}</strong></div>
                </div>
                <form class="invoice-form" action="{{ route('materiales.xml.store') }}" method="POST" data-duplicate="{{ $preview['duplicate']?'1':'0' }}">
                    @csrf
                    <input type="hidden" name="payload" value="{{ $preview['payload'] }}">
                    <input type="hidden" name="payload_signature" value="{{ $preview['signature'] }}">
                    @if($purchaseOrderId)<input type="hidden" name="purchase_order_id" value="{{ $purchaseOrderId }}">@endif
                    <div class="table-wrap"><table class="table"><thead><tr><th>Importar</th><th>No. parte</th><th>Descripcion</th><th>Cantidad</th><th>Precio unitario</th><th>Importe</th><th>Categoria</th><th>Destino</th></tr></thead><tbody>
                    @foreach($factura['conceptos'] as $index=>$concepto)
                        <tr>
                            <td><input type="checkbox" name="items[{{ $index }}][importar]" value="1" checked @disabled($preview['duplicate'])></td>
                            <td>{{ $concepto['numero_parte'] ?: 'Sin identificacion' }}</td>
                            <td><strong>{{ $concepto['descripcion'] }}</strong><div class="muted">SAT {{ $concepto['clave_prod_serv'] ?: 'N/A' }}</div></td>
                            <td>{{ rtrim(rtrim(number_format((float)$concepto['cantidad'],4),'0'),'.') }} {{ $concepto['unidad'] }}</td>
                            <td>${{ number_format((float)$concepto['valor_unitario'],2) }}</td>
                            <td>${{ number_format((float)$concepto['importe'],2) }}</td>
                            <td><select name="items[{{ $index }}][categoria]" required @disabled($preview['duplicate'])>@foreach($categorias as $categoria)<option value="{{ $categoria }}" @selected($categoria==='IMPORTADO XML')>{{ $categoria }}</option>@endforeach</select></td>
                            <td>{{ $concepto['material_existente'] ? 'Suma a material existente · Stock '.$concepto['material_existente']['stock'] : 'Crea material nuevo' }}</td>
                        </tr>
                    @endforeach
                    </tbody></table></div>
                    <div class="invoice-actions">@unless($preview['duplicate'])<button class="btn purple single-import" type="submit">Importar esta factura</button>@endunless</div>
                    <div class="result" hidden></div>
                </form>
            </article>
        @endforeach
    </div>
    <div class="batch-actions"><a class="btn soft" href="{{ route('materiales.index') }}">Ir al inventario</a><button class="btn green" id="importAll" type="button">Importar todas las pendientes</button></div>
</div></main></div>
<script>
(() => {
    const forms=[...document.querySelectorAll('.invoice-form')].filter(form=>form.dataset.duplicate!=='1');
    const csrf=document.querySelector('meta[name="csrf-token"]')?.content;
    async function submitForm(form){
        const card=form.closest('.invoice'),result=form.querySelector('.result'),button=form.querySelector('.single-import');
        button&&(button.disabled=true);result.hidden=false;result.className='result';result.textContent='Importando y validando...';
        try{
            const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json',...(csrf?{'X-CSRF-TOKEN':csrf}:{})}});
            const data=await response.json();
            if(!response.ok)throw new Error(data.message||Object.values(data.errors||{}).flat()[0]||'No se pudo importar.');
            result.classList.add('ok');result.textContent=data.message;card.classList.add('done');form.dataset.duplicate='1';return true;
        }catch(error){result.classList.add('bad');result.textContent=error.message;card.classList.add('error');button&&(button.disabled=false);return false}
    }
    forms.forEach(form=>form.addEventListener('submit',event=>{event.preventDefault();submitForm(form)}));
    document.getElementById('importAll').addEventListener('click',async event=>{event.currentTarget.disabled=true;for(const form of forms){if(form.dataset.duplicate!=='1')await submitForm(form)}event.currentTarget.textContent='Proceso terminado';event.currentTarget.disabled=false});
})();
</script>
</body></html>
