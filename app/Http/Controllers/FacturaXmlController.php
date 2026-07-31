<?php

namespace App\Http\Controllers;

use App\Models\FacturaXmlImportacion;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialMovimiento;
use App\Models\PurchaseOrder;
use App\Support\AuditLogger;
use App\Support\SupplierPriceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;

class FacturaXmlController extends Controller
{
    public function __construct(private readonly SupplierPriceService $prices) {}

    public function create(Request $request)
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403, 'No tienes permiso para importar XML.');

        return view('materiales.importar_xml', [
            'ordenes' => PurchaseOrder::query()
                ->where('estado', 'recibida')
                ->latest('received_at')
                ->get(['id', 'referencia', 'proveedor', 'total']),
            'selectedOrder' => $request->integer('orden'),
        ]);
    }

    public function preview(Request $request)
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403, 'No tienes permiso para importar XML.');

        $request->validate([
            'xml_file' => ['nullable', 'file', 'mimes:xml,txt', 'max:4096', 'required_without:xml_files'],
            'xml_files' => ['nullable', 'array', 'min:1', 'max:20', 'required_without:xml_file'],
            'xml_files.*' => ['required', 'file', 'mimes:xml,txt', 'max:4096'],
            'purchase_order_id' => ['nullable', 'integer', 'exists:purchase_orders,id'],
        ], [
            'xml_file.required_without' => 'Selecciona al menos un archivo XML de factura.',
            'xml_files.required_without' => 'Selecciona al menos un archivo XML de factura.',
            'xml_files.max' => 'Puedes previsualizar hasta 20 facturas a la vez.',
            'xml_file.file' => 'El XML no se recibió como archivo válido.',
            'xml_file.mimes' => 'El archivo debe ser XML.',
            'xml_file.max' => 'El XML no debe pesar más de 4 MB.',
        ]);

        $files = collect($request->file('xml_files', []));
        if ($files->isEmpty() && $request->hasFile('xml_file')) {
            $files = collect([$request->file('xml_file')]);
        }

        $purchaseOrderId = $request->integer('purchase_order_id') ?: null;
        if ($purchaseOrderId && $files->count() > 1) {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'Una orden de compra solo puede vincularse con una factura a la vez. Selecciona un XML o quita la orden.',
            ]);
        }

        $seenUuids = [];
        $previews = $files->map(function ($file) use (&$seenUuids): array {
            $factura = $this->leerCfdi(
                (string) file_get_contents($file->getRealPath())
            );

            if ($factura['uuid'] === '') {
                throw ValidationException::withMessages([
                    'xml_files' => "La factura {$file->getClientOriginalName()} no contiene UUID del SAT.",
                ]);
            }

            foreach ($factura['conceptos'] as $indice => $concepto) {
                $material = Material::query()
                    ->where('numero_parte', $concepto['numero_parte'])
                    ->where('es_plantilla_equipo', false)
                    ->first();
                $factura['conceptos'][$indice]['material_existente'] = $material
                    ? ['id' => $material->id, 'stock' => $material->stock]
                    : null;
            }

            $payload = base64_encode(json_encode($factura, JSON_UNESCAPED_UNICODE));
            $duplicate = $this->facturaYaImportada($factura['uuid'])
                || in_array($factura['uuid'], $seenUuids, true);
            $seenUuids[] = $factura['uuid'];

            return [
                'filename' => $file->getClientOriginalName(),
                'factura' => $factura,
                'payload' => $payload,
                'signature' => hash_hmac('sha256', $payload, (string) config('app.key')),
                'duplicate' => $duplicate,
            ];
        })->values();

        if ($previews->count() > 1) {
            return view('materiales.preview_xml_multiple', [
                'previews' => $previews,
                'categorias' => $this->categoriasDisponibles(),
                'purchaseOrderId' => $purchaseOrderId,
            ]);
        }

        $preview = $previews->first();
        $factura = $preview['factura'];
        $payload = $preview['payload'];

        return view('materiales.preview_xml', [
            'factura' => $factura,
            'payload' => $payload,
            'payloadSignature' => $preview['signature'],
            'categorias' => $this->categoriasDisponibles(),
            'facturaYaImportada' => $preview['duplicate'],
            'purchaseOrderId' => $purchaseOrderId,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403, 'No tienes permiso para importar XML.');

        $datos = $request->validate([
            'payload' => ['required', 'string'],
            'payload_signature' => ['required', 'string', 'size:64'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.categoria' => ['required', 'string', 'max:255'],
            'items.*.importar' => ['nullable', 'boolean'],
            'purchase_order_id' => ['nullable', 'integer', 'exists:purchase_orders,id'],
        ], [
            'payload.required' => 'La vista previa se perdió. Sube el XML otra vez.',
            'items.required' => 'No hay productos seleccionados para importar.',
            'items.min' => 'Selecciona al menos un producto para importar.',
            'items.*.categoria.required' => 'Selecciona categoría para cada producto que vas a importar.',
        ]);

        $firmaEsperada = hash_hmac('sha256', $datos['payload'], (string) config('app.key'));

        if (! hash_equals($firmaEsperada, $datos['payload_signature'])) {
            throw ValidationException::withMessages([
                'payload' => 'Los datos de la vista previa fueron alterados o expiraron. Sube el XML nuevamente.',
            ]);
        }

        $decodedPayload = base64_decode($datos['payload'], true);
        $factura = $decodedPayload !== false
            ? json_decode($decodedPayload, true)
            : null;

        if (! is_array($factura) || empty($factura['conceptos'])) {
            return redirect()
                ->route('materiales.xml.create')
                ->with('error', 'No se pudo recuperar la vista previa del XML. Sube el archivo otra vez.');
        }

        if (empty($factura['uuid'])) {
            throw ValidationException::withMessages([
                'payload' => 'El XML no contiene UUID fiscal y no puede importarse.',
            ]);
        }

        if ($this->facturaYaImportada($factura['uuid'])) {
            throw ValidationException::withMessages([
                'payload' => 'Esta factura ya fue importada anteriormente. El stock no se modificó.',
            ]);
        }

        $seleccionados = collect($datos['items'])
            ->filter(fn (array $item) => ! empty($item['importar']));

        if ($seleccionados->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Selecciona al menos un concepto para importar.',
            ]);
        }

        $resumen = [
            'creados' => 0,
            'actualizados' => 0,
            'facturados' => 0,
            'omitidos' => 0,
        ];

        $usuarioId = $request->user()->getAuthIdentifier();

        DB::transaction(function () use ($factura, $datos, $usuarioId, &$resumen) {
            $order = null;
            $orderMaterialIds = collect();
            if (! empty($datos['purchase_order_id'])) {
                $order = PurchaseOrder::query()
                    ->whereKey($datos['purchase_order_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($order->estado !== 'recibida') {
                    throw ValidationException::withMessages([
                        'purchase_order_id' => 'La orden debe estar recibida antes de vincular su factura.',
                    ]);
                }

                $orderMaterialIds = $order->items()
                    ->whereNotNull('material_id')
                    ->pluck('material_id')
                    ->map(fn ($id): int => (int) $id);
            }

            FacturaXmlImportacion::create([
                'purchase_order_id' => $datos['purchase_order_id'] ?? null,
                'uuid' => $factura['uuid'],
                'version' => $factura['version'] ?: null,
                'serie' => $factura['serie'] ?: null,
                'folio' => $factura['folio'] ?: null,
                'fecha' => $factura['fecha'] ?: null,
                'moneda' => $factura['moneda'] ?: null,
                'tipo_cambio' => $factura['tipo_cambio'] ?: null,
                'subtotal' => $factura['subtotal'] ?? 0,
                'descuento' => $factura['descuento'] ?? 0,
                'impuestos_trasladados' => $factura['impuestos_trasladados'] ?? 0,
                'impuestos_retenidos' => $factura['impuestos_retenidos'] ?? 0,
                'total' => $factura['total'] ?? 0,
                'tipo_comprobante' => $factura['tipo_comprobante'] ?: null,
                'metodo_pago' => $factura['metodo_pago'] ?: null,
                'forma_pago' => $factura['forma_pago'] ?: null,
                'emisor_rfc' => $factura['emisor']['rfc'] ?: null,
                'emisor_nombre' => $factura['emisor']['nombre'] ?: null,
                'receptor_rfc' => $factura['receptor']['rfc'] ?: null,
                'receptor_nombre' => $factura['receptor']['nombre'] ?: null,
                'conceptos_count' => count($factura['conceptos']),
                'datos' => $factura,
                'user_id' => $usuarioId,
            ]);

            foreach ($factura['conceptos'] as $indice => $concepto) {
                if (! isset($datos['items'][$indice]['importar'])) {
                    $resumen['omitidos']++;

                    continue;
                }

                $numeroParte = trim((string) ($concepto['numero_parte'] ?? ''));
                $descripcion = trim((string) ($concepto['descripcion'] ?? ''));
                $cantidadOriginal = max(0, (float) ($concepto['cantidad'] ?? 0));
                $cantidad = (int) round($cantidadOriginal);

                if (abs($cantidadOriginal - $cantidad) > 0.0001) {
                    throw ValidationException::withMessages([
                        "items.{$indice}.importar" => "El concepto {$descripcion} tiene cantidad decimal ({$cantidadOriginal}). El inventario maneja piezas enteras; corrige la unidad antes de importarlo.",
                    ]);
                }

                if ($numeroParte === '' || $descripcion === '' || $cantidad <= 0) {
                    $resumen['omitidos']++;

                    continue;
                }

                $material = Material::query()
                    ->where('numero_parte', $numeroParte)
                    ->where('es_plantilla_equipo', false)
                    ->lockForUpdate()
                    ->first();

                if ($material) {
                    if ($order && ! $orderMaterialIds->contains((int) $material->id)) {
                        throw ValidationException::withMessages([
                            "items.{$indice}.importar" => "El material {$descripcion} no pertenece a la orden seleccionada.",
                        ]);
                    }

                    if ($order) {
                        $material->update([
                            'clave_sat' => $concepto['clave_prod_serv'] ?? $material->clave_sat,
                            'clave_unidad' => $concepto['clave_unidad'] ?? $material->clave_unidad,
                            'unidad' => $concepto['unidad'] ?? $material->unidad,
                            'costo_unitario' => (float) ($concepto['valor_unitario'] ?? $material->costo_unitario),
                            'moneda' => $factura['moneda'] ?: ($material->moneda ?: 'MXN'),
                            'proveedor' => $factura['emisor']['nombre'] ?? $material->proveedor,
                            'proveedor_rfc' => $factura['emisor']['rfc'] ?? $material->proveedor_rfc,
                            'factura_uuid' => $factura['uuid'] ?? $material->factura_uuid,
                            'factura_folio' => trim(($factura['serie'] ?? '').' '.($factura['folio'] ?? '')) ?: $material->factura_folio,
                            'factura_fecha' => $factura['fecha'] ?? $material->factura_fecha,
                            'xml_importado_at' => now(),
                        ]);

                        $this->prices->record(
                            $material,
                            $factura['emisor']['nombre'] ?? null,
                            (float) ($concepto['valor_unitario'] ?? 0),
                            $factura['moneda'] ?: 'MXN',
                            'xml',
                            $factura['uuid'],
                            $factura['emisor']['rfc'] ?? null,
                            $factura['fecha'] ?: now()
                        );
                        $resumen['facturados']++;

                        continue;
                    }

                    $stockAnterior = $material->stock;
                    $material->update([
                        'stock' => $stockAnterior + $cantidad,
                        'clave_sat' => $concepto['clave_prod_serv'] ?? $material->clave_sat,
                        'clave_unidad' => $concepto['clave_unidad'] ?? $material->clave_unidad,
                        'unidad' => $concepto['unidad'] ?? $material->unidad,
                        'costo_unitario' => (float) ($concepto['valor_unitario'] ?? $material->costo_unitario),
                        'moneda' => $factura['moneda'] ?: ($material->moneda ?: 'MXN'),
                        'proveedor' => $factura['emisor']['nombre'] ?? $material->proveedor,
                        'proveedor_rfc' => $factura['emisor']['rfc'] ?? $material->proveedor_rfc,
                        'factura_uuid' => $factura['uuid'] ?? $material->factura_uuid,
                        'factura_folio' => trim(($factura['serie'] ?? '').' '.($factura['folio'] ?? '')) ?: $material->factura_folio,
                        'factura_fecha' => $factura['fecha'] ?? $material->factura_fecha,
                        'xml_importado_at' => now(),
                    ]);

                    MaterialMovimiento::create([
                        'material_id' => $material->id,
                        'user_id' => $usuarioId,
                        'tipo' => 'entrada',
                        'cantidad' => $cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $material->stock,
                        'codigo_barras' => $material->codigo_barras,
                        'referencia' => 'XML '.(($factura['uuid'] ?? '') ?: ($factura['folio'] ?? '')),
                        'motivo' => 'Entrada importada desde XML',
                        'proveedor' => $factura['emisor']['nombre'] ?? null,
                        'costo_unitario' => (float) ($concepto['valor_unitario'] ?? 0),
                    ]);

                    $this->prices->record(
                        $material,
                        $factura['emisor']['nombre'] ?? null,
                        (float) ($concepto['valor_unitario'] ?? 0),
                        $factura['moneda'] ?: 'MXN',
                        'xml',
                        $factura['uuid'],
                        $factura['emisor']['rfc'] ?? null,
                        $factura['fecha'] ?: now()
                    );

                    $resumen['actualizados']++;

                    continue;
                }

                if ($order) {
                    throw ValidationException::withMessages([
                        "items.{$indice}.importar" => "No se encontro {$descripcion} dentro del inventario de la orden. Revisa el numero de parte antes de facturar.",
                    ]);
                }

                $nuevo = Material::create([
                    'categoria' => $datos['items'][$indice]['categoria'],
                    'numero_parte' => $numeroParte,
                    'codigo_barras' => null,
                    'clave_sat' => $concepto['clave_prod_serv'] ?? null,
                    'clave_unidad' => $concepto['clave_unidad'] ?? null,
                    'unidad' => $concepto['unidad'] ?? null,
                    'descripcion' => $descripcion,
                    'es_plantilla_equipo' => false,
                    'marca' => $factura['addenda']['marca'] ?? null,
                    'proveedor' => $factura['emisor']['nombre'] ?? null,
                    'proveedor_rfc' => $factura['emisor']['rfc'] ?? null,
                    'stock' => $cantidad,
                    'stock_minimo' => 0,
                    'stock_maximo' => 0,
                    'costo_unitario' => (float) ($concepto['valor_unitario'] ?? 0),
                    'moneda' => $factura['moneda'] ?: 'MXN',
                    'factura_uuid' => $factura['uuid'] ?? null,
                    'factura_folio' => trim(($factura['serie'] ?? '').' '.($factura['folio'] ?? '')) ?: null,
                    'factura_fecha' => $factura['fecha'] ?? null,
                    'xml_importado_at' => now(),
                ]);

                MaterialMovimiento::create([
                    'material_id' => $nuevo->id,
                    'user_id' => $usuarioId,
                    'tipo' => 'entrada',
                    'cantidad' => $cantidad,
                    'stock_anterior' => 0,
                    'stock_nuevo' => $cantidad,
                    'codigo_barras' => null,
                    'referencia' => 'XML '.(($factura['uuid'] ?? '') ?: ($factura['folio'] ?? '')),
                    'motivo' => 'Alta importada desde XML',
                    'proveedor' => $factura['emisor']['nombre'] ?? null,
                    'costo_unitario' => (float) ($concepto['valor_unitario'] ?? 0),
                ]);

                $this->prices->record(
                    $nuevo,
                    $factura['emisor']['nombre'] ?? null,
                    (float) ($concepto['valor_unitario'] ?? 0),
                    $factura['moneda'] ?: 'MXN',
                    'xml',
                    $factura['uuid'],
                    $factura['emisor']['rfc'] ?? null,
                    $factura['fecha'] ?: now()
                );

                $resumen['creados']++;
            }

            if (($resumen['creados'] + $resumen['actualizados'] + $resumen['facturados']) === 0) {
                throw ValidationException::withMessages([
                    'items' => 'Ningún concepto pudo importarse. Verifica número de parte, descripción, cantidad y selección.',
                ]);
            }

            if ($order) {
                $order->update([
                    'estado' => 'facturada',
                    'invoice_uuid' => $factura['uuid'],
                    'invoice_folio' => trim(($factura['serie'] ?? '').' '.($factura['folio'] ?? '')) ?: null,
                    'invoiced_by' => $usuarioId,
                    'invoiced_at' => now(),
                ]);
                $order->request?->update(['estado' => 'facturada']);
            }
        });

        AuditLogger::registrar('XML', 'Importacion de factura', "Proceso XML con {$resumen['creados']} materiales nuevos, {$resumen['actualizados']} entradas y {$resumen['facturados']} precios facturados.", [
            'uuid' => $factura['uuid'] ?? null,
            'folio' => $factura['folio'] ?? null,
            'proveedor' => $factura['emisor']['nombre'] ?? null,
            'resumen' => $resumen,
        ], $request);

        $message = $resumen['facturados'] > 0
            ? "Factura vinculada: {$resumen['facturados']} precios actualizados. El stock ya recibido no se duplico."
            : "XML importado: {$resumen['creados']} materiales nuevos, {$resumen['actualizados']} stocks actualizados, {$resumen['omitidos']} conceptos omitidos.";

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'summary' => $resumen,
                'uuid' => $factura['uuid'],
            ]);
        }

        return redirect()
            ->route('materiales.index')
            ->with('success', $message);
    }

    private function leerCfdi(string $xmlString): array
    {
        libxml_use_internal_errors(true);

        try {
            $xml = new SimpleXMLElement($xmlString, LIBXML_NONET | LIBXML_NOCDATA);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'xml_file' => 'No se pudo leer el XML. Verifica que sea un CFDI válido descargado del SAT.',
            ]);
        }

        $namespaces = $xml->getNamespaces(true);
        $cfdiNamespace = $namespaces['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4';
        $tfdNamespace = $namespaces['tfd'] ?? 'http://www.sat.gob.mx/TimbreFiscalDigital';

        $xml->registerXPathNamespace('cfdi', $cfdiNamespace);
        $xml->registerXPathNamespace('tfd', $tfdNamespace);

        if ($xml->getName() !== 'Comprobante') {
            throw ValidationException::withMessages([
                'xml_file' => 'El archivo XML no tiene como raíz un Comprobante CFDI.',
            ]);
        }

        $conceptosXml = $xml->xpath('//cfdi:Concepto') ?: [];

        if (count($conceptosXml) === 0) {
            throw ValidationException::withMessages([
                'xml_file' => 'El XML no trae conceptos CFDI para importar. Verifica que sea la factura correcta.',
            ]);
        }

        $emisor = $xml->xpath('//cfdi:Emisor')[0] ?? null;
        $receptor = $xml->xpath('//cfdi:Receptor')[0] ?? null;
        $timbre = $xml->xpath('//tfd:TimbreFiscalDigital')[0] ?? null;
        $impuestosFactura = $xml->xpath('/cfdi:Comprobante/cfdi:Impuestos')[0] ?? null;

        $conceptos = [];

        foreach ($conceptosXml as $concepto) {
            $traslados = $concepto->xpath('./*[local-name()="Impuestos"]/*[local-name()="Traslados"]/*[local-name()="Traslado"]') ?: [];
            $retenciones = $concepto->xpath('./*[local-name()="Impuestos"]/*[local-name()="Retenciones"]/*[local-name()="Retencion"]') ?: [];

            $conceptos[] = [
                'clave_prod_serv' => (string) ($concepto['ClaveProdServ'] ?? ''),
                'clave_unidad' => (string) ($concepto['ClaveUnidad'] ?? ''),
                'numero_parte' => (string) ($concepto['NoIdentificacion'] ?? ''),
                'descripcion' => (string) ($concepto['Descripcion'] ?? ''),
                'cantidad' => (float) ($concepto['Cantidad'] ?? 0),
                'unidad' => (string) ($concepto['Unidad'] ?? $concepto['ClaveUnidad'] ?? ''),
                'valor_unitario' => (float) ($concepto['ValorUnitario'] ?? 0),
                'importe' => (float) ($concepto['Importe'] ?? 0),
                'descuento' => (float) ($concepto['Descuento'] ?? 0),
                'objeto_impuesto' => (string) ($concepto['ObjetoImp'] ?? ''),
                'impuestos_trasladados' => collect($traslados)->sum(fn (SimpleXMLElement $impuesto) => (float) ($impuesto['Importe'] ?? 0)),
                'impuestos_retenidos' => collect($retenciones)->sum(fn (SimpleXMLElement $impuesto) => (float) ($impuesto['Importe'] ?? 0)),
                'traslados' => collect($traslados)->map(fn (SimpleXMLElement $impuesto): array => [
                    'impuesto' => (string) ($impuesto['Impuesto'] ?? ''),
                    'tipo_factor' => (string) ($impuesto['TipoFactor'] ?? ''),
                    'tasa_cuota' => (string) ($impuesto['TasaOCuota'] ?? ''),
                    'base' => (float) ($impuesto['Base'] ?? 0),
                    'importe' => (float) ($impuesto['Importe'] ?? 0),
                ])->values()->all(),
            ];
        }

        return [
            'version' => (string) ($xml['Version'] ?? ''),
            'serie' => (string) ($xml['Serie'] ?? ''),
            'folio' => (string) ($xml['Folio'] ?? ''),
            'fecha' => (string) ($xml['Fecha'] ?? ''),
            'moneda' => (string) ($xml['Moneda'] ?? ''),
            'tipo_cambio' => (float) ($xml['TipoCambio'] ?? 0),
            'subtotal' => (float) ($xml['SubTotal'] ?? 0),
            'descuento' => (float) ($xml['Descuento'] ?? 0),
            'impuestos_trasladados' => $impuestosFactura ? (float) ($impuestosFactura['TotalImpuestosTrasladados'] ?? 0) : 0,
            'impuestos_retenidos' => $impuestosFactura ? (float) ($impuestosFactura['TotalImpuestosRetenidos'] ?? 0) : 0,
            'total' => (float) ($xml['Total'] ?? 0),
            'tipo_comprobante' => (string) ($xml['TipoDeComprobante'] ?? ''),
            'metodo_pago' => (string) ($xml['MetodoPago'] ?? ''),
            'forma_pago' => (string) ($xml['FormaPago'] ?? ''),
            'lugar_expedicion' => (string) ($xml['LugarExpedicion'] ?? ''),
            'exportacion' => (string) ($xml['Exportacion'] ?? ''),
            'uuid' => $timbre ? strtoupper(trim((string) ($timbre['UUID'] ?? ''))) : '',
            'emisor' => [
                'rfc' => $emisor ? (string) ($emisor['Rfc'] ?? '') : '',
                'nombre' => $emisor ? (string) ($emisor['Nombre'] ?? '') : '',
                'regimen_fiscal' => $emisor ? (string) ($emisor['RegimenFiscal'] ?? '') : '',
            ],
            'receptor' => [
                'rfc' => $receptor ? (string) ($receptor['Rfc'] ?? '') : '',
                'nombre' => $receptor ? (string) ($receptor['Nombre'] ?? '') : '',
                'uso_cfdi' => $receptor ? (string) ($receptor['UsoCFDI'] ?? '') : '',
                'regimen_fiscal' => $receptor ? (string) ($receptor['RegimenFiscalReceptor'] ?? '') : '',
            ],
            'addenda' => $this->leerAddenda($xml),
            'conceptos' => $conceptos,
        ];
    }

    private function categoriasDisponibles()
    {
        return MaterialCategory::query()
            ->where('activa', true)
            ->where('nombre', 'not like', 'EQUIPO%')
            ->orderBy('nombre')
            ->pluck('nombre')
            ->merge(Material::query()
                ->where('es_plantilla_equipo', false)
                ->whereNotNull('categoria')
                ->where('categoria', 'not like', 'EQUIPO%')
                ->distinct()
                ->orderBy('categoria')
                ->pluck('categoria'))
            ->map(fn ($categoria) => trim((string) $categoria))
            ->filter()
            ->unique(fn ($categoria) => strtoupper($categoria))
            ->values();
    }

    private function facturaYaImportada(string $uuid): bool
    {
        return FacturaXmlImportacion::where('uuid', $uuid)->exists()
            || Material::where('factura_uuid', $uuid)->exists()
            || MaterialMovimiento::where('referencia', 'XML '.$uuid)->exists();
    }

    private function leerAddenda(SimpleXMLElement $xml): array
    {
        $datos = [];
        $addenda = $xml->xpath('//*[local-name()="Addenda"]/*[local-name()="datos"]')[0] ?? null;

        if (! $addenda) {
            return $datos;
        }

        foreach ($addenda->attributes() as $llave => $valor) {
            $datos[strtolower((string) $llave)] = (string) $valor;
        }

        return $datos;
    }
}
