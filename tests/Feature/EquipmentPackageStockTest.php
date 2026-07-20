<?php

namespace Tests\Feature;

use App\Models\EquipmentPackage;
use App\Models\Material;
use App\Models\MaterialMovimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentPackageStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_is_rejected_and_reports_every_missing_piece_without_changing_stock(): void
    {
        $user = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);
        $equipo = EquipmentPackage::create(['nombre' => 'Equipo de prueba']);
        $cuello = $this->material('Cuello soldable', 1);
        $bandera = $this->material('Bandera flexible', 0);

        $equipo->items()->create(['material_id' => $cuello->id, 'descripcion' => $cuello->descripcion, 'cantidad_por_paquete' => 2]);
        $equipo->items()->create(['material_id' => $bandera->id, 'descripcion' => $bandera->descripcion, 'cantidad_por_paquete' => 1]);

        $disponibilidad = $equipo->fresh()->evaluarDisponibilidad();
        $this->assertFalse($disponibilidad['listo']);
        $this->assertCount(2, $disponibilidad['faltantes']);

        $response = $this->actingAs($user)->post(route('equipos.withdraw', $equipo), [
            'cantidad_paquetes' => 1,
            'tipo' => 'venta',
        ]);

        $response->assertSessionHasErrors('cantidad_paquetes');
        $mensaje = session('errors')->first('cantidad_paquetes');
        $this->assertStringContainsString('Cuello soldable', $mensaje);
        $this->assertStringContainsString('Bandera flexible', $mensaje);
        $this->assertSame(1, $cuello->fresh()->stock);
        $this->assertSame(0, $bandera->fresh()->stock);
        $this->assertDatabaseCount('equipment_package_withdrawals', 0);
        $this->assertDatabaseCount('material_movimientos', 0);
    }

    public function test_sale_aggregates_repeated_materials_and_deducts_stock_once(): void
    {
        $user = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);
        $equipo = EquipmentPackage::create(['nombre' => 'Equipo repetido']);
        $material = $this->material('Tornillo inoxidable', 10);

        $equipo->items()->create(['material_id' => $material->id, 'descripcion' => 'Tornillo A', 'cantidad_por_paquete' => 1]);
        $equipo->items()->create(['material_id' => $material->id, 'descripcion' => 'Tornillo B', 'cantidad_por_paquete' => 2]);

        $response = $this->actingAs($user)->post(route('equipos.withdraw', $equipo), [
            'cantidad_paquetes' => 2,
            'tipo' => 'venta',
            'referencia' => 'VENTA-001',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(4, $material->fresh()->stock);
        $this->assertDatabaseHas('equipment_package_withdrawals', [
            'equipment_package_id' => $equipo->id,
            'cantidad_paquetes' => 2,
            'tipo' => 'venta',
        ]);
        $this->assertDatabaseHas('material_movimientos', [
            'material_id' => $material->id,
            'tipo' => 'salida',
            'cantidad' => 6,
            'stock_anterior' => 10,
            'stock_nuevo' => 4,
        ]);
        $this->assertSame(1, MaterialMovimiento::where('material_id', $material->id)->count());
    }

    private function material(string $descripcion, int $stock): Material
    {
        return Material::create([
            'descripcion' => $descripcion,
            'stock' => $stock,
            'es_plantilla_equipo' => false,
        ]);
    }
}
