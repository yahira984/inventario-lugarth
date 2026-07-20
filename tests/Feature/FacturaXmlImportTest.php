<?php

namespace Tests\Feature;

use App\Models\FacturaXmlImportacion;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialMovimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FacturaXmlImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_cfdi_import_saves_fiscal_data_and_only_adds_stock_once(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);
        MaterialCategory::firstOrCreate([
            'nombre' => 'IMPORTADO XML',
        ], [
            'activa' => true,
        ]);
        $plantilla = Material::create([
            'numero_parte' => '34006319',
            'descripcion' => 'Renglon antiguo de equipo',
            'stock' => 99,
            'es_plantilla_equipo' => true,
        ]);

        $preview = $this->actingAs($admin)->post(route('materiales.xml.preview'), [
            'xml_file' => UploadedFile::fake()->createWithContent('factura.xml', $this->cfdi()),
        ]);

        $preview->assertOk()
            ->assertViewIs('materiales.preview_xml')
            ->assertViewHas('facturaYaImportada', false)
            ->assertSee('COMERCIALIZADORA DE MOTOCICLETAS DE CALIDAD')
            ->assertSee('B9AA6783-CAF4-4B17-94E3-1BAC718160F8')
            ->assertSee('$22,412.93');

        $factura = $preview->viewData('factura');
        $this->assertNull($factura['conceptos'][0]['material_existente']);
        $this->assertSame(3586.07, $factura['impuestos_trasladados']);
        $this->assertSame('25101801', $factura['conceptos'][0]['clave_prod_serv']);

        $payload = $preview->viewData('payload');
        $signature = $preview->viewData('payloadSignature');

        $this->actingAs($admin)->post(route('materiales.xml.store'), [
            'payload' => $payload,
            'payload_signature' => $signature,
            'items' => [
                ['importar' => 1, 'categoria' => 'IMPORTADO XML'],
            ],
        ])->assertRedirect(route('materiales.index'))
            ->assertSessionHasNoErrors();

        $material = Material::query()
            ->where('numero_parte', '34006319')
            ->where('es_plantilla_equipo', false)
            ->firstOrFail();

        $this->assertSame(1, $material->stock);
        $this->assertSame('FT200 GTS GRIS', $material->descripcion);
        $this->assertSame('25101801', $material->clave_sat);
        $this->assertSame('H87', $material->clave_unidad);
        $this->assertSame('PIEZA', $material->unidad);
        $this->assertSame('22412.93', $material->costo_unitario);
        $this->assertSame('MXN', $material->moneda);
        $this->assertSame('COMERCIALIZADORA DE MOTOCICLETAS DE CALIDAD', $material->proveedor);
        $this->assertSame('CMC0712144R4', $material->proveedor_rfc);
        $this->assertSame('B9AA6783-CAF4-4B17-94E3-1BAC718160F8', $material->factura_uuid);
        $this->assertSame(99, $plantilla->fresh()->stock);

        $movimiento = MaterialMovimiento::where('material_id', $material->id)->firstOrFail();
        $this->assertSame('entrada', $movimiento->tipo);
        $this->assertSame(1, $movimiento->cantidad);
        $this->assertSame(0, $movimiento->stock_anterior);
        $this->assertSame(1, $movimiento->stock_nuevo);
        $this->assertSame('22412.93', $movimiento->costo_unitario);
        $this->assertSame('COMERCIALIZADORA DE MOTOCICLETAS DE CALIDAD', $movimiento->proveedor);

        $importacion = FacturaXmlImportacion::firstOrFail();
        $this->assertSame('25999.00', $importacion->total);
        $this->assertSame('3586.07', $importacion->impuestos_trasladados);
        $this->assertSame('PUE', $importacion->metodo_pago);
        $this->assertSame('03', $importacion->forma_pago);
        $this->assertSame('02', $importacion->datos['conceptos'][0]['objeto_impuesto']);

        $this->actingAs($admin)->post(route('materiales.xml.store'), [
            'payload' => $payload,
            'payload_signature' => $signature,
            'items' => [
                ['importar' => 1, 'categoria' => 'IMPORTADO XML'],
            ],
        ])->assertSessionHasErrors('payload');

        $this->assertSame(1, $material->fresh()->stock);
        $this->assertDatabaseCount('factura_xml_importaciones', 1);
        $this->assertSame(1, MaterialMovimiento::where('material_id', $material->id)->count());
    }

    public function test_legacy_imported_uuid_is_not_added_to_stock_again(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);
        $material = Material::create([
            'numero_parte' => '34006319',
            'descripcion' => 'FT200 GTS GRIS',
            'stock' => 1,
            'es_plantilla_equipo' => false,
            'factura_uuid' => 'B9AA6783-CAF4-4B17-94E3-1BAC718160F8',
        ]);

        $preview = $this->actingAs($admin)->post(route('materiales.xml.preview'), [
            'xml_file' => UploadedFile::fake()->createWithContent('factura.xml', $this->cfdi()),
        ]);

        $preview->assertOk()
            ->assertViewIs('materiales.preview_xml')
            ->assertViewHas('facturaYaImportada', true)
            ->assertSee('Esta factura ya fue importada anteriormente')
            ->assertDontSee('Confirmar importación');

        $this->assertSame(1, $material->fresh()->stock);
        $this->assertDatabaseCount('factura_xml_importaciones', 0);
        $this->assertDatabaseCount('material_movimientos', 0);
    }

    private function cfdi(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" Version="4.0" Serie="CIN" Folio="ITK0745532305" Fecha="2024-11-16T20:54:47" Moneda="MXN" TipoCambio="1" SubTotal="22412.93" Descuento="0.00" Total="25999.00" TipoDeComprobante="I" MetodoPago="PUE" FormaPago="03" LugarExpedicion="45070" Exportacion="01">
  <cfdi:Emisor Rfc="CMC0712144R4" Nombre="COMERCIALIZADORA DE MOTOCICLETAS DE CALIDAD" RegimenFiscal="601" />
  <cfdi:Receptor Rfc="AIAK021017N51" Nombre="LUGARTH" UsoCFDI="G03" RegimenFiscalReceptor="601" />
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="25101801" NoIdentificacion="34006319" Cantidad="1" ClaveUnidad="H87" Unidad="PIEZA" Descripcion="FT200 GTS GRIS" ValorUnitario="22412.93" Importe="22412.93" Descuento="0.00" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="22412.93" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="3586.07" />
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos TotalImpuestosTrasladados="3586.07">
    <cfdi:Traslados>
      <cfdi:Traslado Base="22412.93" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="3586.07" />
    </cfdi:Traslados>
  </cfdi:Impuestos>
  <cfdi:Complemento>
    <tfd:TimbreFiscalDigital Version="1.1" UUID="B9AA6783-CAF4-4B17-94E3-1BAC718160F8" FechaTimbrado="2024-11-16T20:55:00" />
  </cfdi:Complemento>
</cfdi:Comprobante>
XML;
    }
}
