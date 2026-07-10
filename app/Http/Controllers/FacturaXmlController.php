<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;

class FacturaXmlController extends Controller
{
    public function create()
    {
        return view('materiales.importar_xml');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'xml_file' => ['required', 'file', 'mimes:xml,txt', 'max:4096'],
        ], [
            'xml_file.required' => 'Selecciona un archivo XML de factura.',
            'xml_file.mimes' => 'El archivo debe ser XML.',
            'xml_file.max' => 'El XML no debe pesar mas de 4 MB.',
        ]);

        $xmlString = file_get_contents($request->file('xml_file')->getRealPath());
        $factura = $this->leerCfdi($xmlString);

        foreach ($factura['conceptos'] as $indice => $concepto) {
            $material = Material::where(
                'numero_parte',
                $concepto['numero_parte']
            )->first();

            $factura['conceptos'][$indice]['material_existente'] = $material
                ? [
                    'id' => $material->id,
                    'stock' => $material->stock,
                ]
                : null;
        }

        return view('materiales.preview_xml', [
            'factura' => $factura,
            'payload' => base64_encode(json_encode($factura, JSON_UNESCAPED_UNICODE)),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'payload' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.categoria' => ['required', 'string', 'max:255'],
            'items.*.importar' => ['nullable', 'boolean'],
        ], [
            'items.*.categoria.required' => 'Selecciona categoria para cada producto a importar.',
        ]);

        $factura = json_decode(base64_decode($datos['payload']), true);

        if (! is_array($factura) || empty($factura['conceptos'])) {
            return redirect()
                ->route('materiales.xml.create')
                ->with('error', 'No se pudo recuperar la vista previa del XML. Sube el archivo otra vez.');
        }

        $resumen = [
            'creados' => 0,
            'actualizados' => 0,
            'omitidos' => 0,
        ];

        DB::transaction(function () use ($factura, $datos, &$resumen) {
            foreach ($factura['conceptos'] as $indice => $concepto) {
                if (! isset($datos['items'][$indice]['importar'])) {
                    $resumen['omitidos']++;
                    continue;
                }

                $numeroParte = trim((string) ($concepto['numero_parte'] ?? ''));
                $descripcion = trim((string) ($concepto['descripcion'] ?? ''));
                $cantidad = max(0, (int) round((float) ($concepto['cantidad'] ?? 0)));

                if ($numeroParte === '' || $descripcion === '' || $cantidad <= 0) {
                    $resumen['omitidos']++;
                    continue;
                }

                $material = Material::where('numero_parte', $numeroParte)->first();

                if ($material) {
                    $material->increment('stock', $cantidad);
                    $resumen['actualizados']++;
                    continue;
                }

                Material::create([
                    'categoria' => $datos['items'][$indice]['categoria'],
                    'numero_parte' => $numeroParte,
                    'codigo_barras' => null,
                    'descripcion' => $descripcion,
                    'marca' => $factura['addenda']['marca'] ?? null,
                    'proveedor' => $factura['emisor']['nombre'] ?? null,
                    'stock' => $cantidad,
                ]);

                $resumen['creados']++;
            }
        });

        return redirect()
            ->route('materiales.index')
            ->with(
                'success',
                "XML importado: {$resumen['creados']} materiales nuevos, {$resumen['actualizados']} stocks actualizados, {$resumen['omitidos']} conceptos omitidos."
            );
    }

    private function leerCfdi(string $xmlString): array
    {
        libxml_use_internal_errors(true);

        try {
            $xml = new SimpleXMLElement($xmlString);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'xml_file' => 'No se pudo leer el XML. Verifica que sea un CFDI valido.',
            ]);
        }

        $namespaces = $xml->getNamespaces(true);
        $cfdiNamespace = $namespaces['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4';
        $tfdNamespace = $namespaces['tfd'] ?? 'http://www.sat.gob.mx/TimbreFiscalDigital';

        $xml->registerXPathNamespace('cfdi', $cfdiNamespace);
        $xml->registerXPathNamespace('tfd', $tfdNamespace);

        $conceptosXml = $xml->xpath('//cfdi:Concepto') ?: [];

        if (count($conceptosXml) === 0) {
            throw ValidationException::withMessages([
                'xml_file' => 'El XML no trae conceptos CFDI para importar.',
            ]);
        }

        $emisor = $xml->xpath('//cfdi:Emisor')[0] ?? null;
        $receptor = $xml->xpath('//cfdi:Receptor')[0] ?? null;
        $timbre = $xml->xpath('//tfd:TimbreFiscalDigital')[0] ?? null;

        $conceptos = [];

        foreach ($conceptosXml as $concepto) {
            $conceptos[] = [
                'clave_prod_serv' => (string) ($concepto['ClaveProdServ'] ?? ''),
                'numero_parte' => (string) ($concepto['NoIdentificacion'] ?? ''),
                'descripcion' => (string) ($concepto['Descripcion'] ?? ''),
                'cantidad' => (float) ($concepto['Cantidad'] ?? 0),
                'unidad' => (string) ($concepto['Unidad'] ?? $concepto['ClaveUnidad'] ?? ''),
                'valor_unitario' => (float) ($concepto['ValorUnitario'] ?? 0),
                'importe' => (float) ($concepto['Importe'] ?? 0),
            ];
        }

        return [
            'version' => (string) ($xml['Version'] ?? ''),
            'serie' => (string) ($xml['Serie'] ?? ''),
            'folio' => (string) ($xml['Folio'] ?? ''),
            'fecha' => (string) ($xml['Fecha'] ?? ''),
            'moneda' => (string) ($xml['Moneda'] ?? ''),
            'subtotal' => (float) ($xml['SubTotal'] ?? 0),
            'total' => (float) ($xml['Total'] ?? 0),
            'uuid' => $timbre ? (string) ($timbre['UUID'] ?? '') : '',
            'emisor' => [
                'rfc' => $emisor ? (string) ($emisor['Rfc'] ?? '') : '',
                'nombre' => $emisor ? (string) ($emisor['Nombre'] ?? '') : '',
            ],
            'receptor' => [
                'rfc' => $receptor ? (string) ($receptor['Rfc'] ?? '') : '',
                'nombre' => $receptor ? (string) ($receptor['Nombre'] ?? '') : '',
            ],
            'addenda' => $this->leerAddenda($xml),
            'conceptos' => $conceptos,
        ];
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
