<?php

namespace Tests\Unit;

use App\Models\EquipmentPackage;
use App\Models\EquipmentPackageItem;
use App\Models\Material;
use App\Support\EquipmentPlanningService;
use Tests\TestCase;

class EquipmentPlanningServiceTest extends TestCase
{
    public function test_calculates_capacity_with_shared_material_and_whole_piece_rounding(): void
    {
        $material = new Material([
            'descripcion' => 'Tornillo de prueba',
            'stock' => 11,
            'costo_unitario' => 4.50,
        ]);
        $material->id = 101;
        $material->setRelation('latestInvoicePrice', null);

        $firstLine = new EquipmentPackageItem([
            'material_id' => $material->id,
            'descripcion' => $material->descripcion,
            'cantidad_por_paquete' => 1,
        ]);
        $firstLine->setRelation('material', $material);

        $secondLine = new EquipmentPackageItem([
            'material_id' => $material->id,
            'descripcion' => $material->descripcion,
            'cantidad_por_paquete' => 1,
        ]);
        $secondLine->setRelation('material', $material);

        $package = new EquipmentPackage(['nombre' => 'Equipo de prueba']);
        $package->setRelation('items', collect([$firstLine, $secondLine]));

        $plan = app(EquipmentPlanningService::class)->analyze($package);

        $this->assertTrue($plan['listo']);
        $this->assertSame(5, $plan['fabricables']);
        $this->assertSame(9.0, $plan['costo_unitario']);
        $this->assertSame('Tornillo de prueba', $plan['limitantes']->first()['descripcion']);
    }
}
