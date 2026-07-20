<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialEntradaPendiente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_entry_can_only_add_stock_once(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);
        $almacenista = User::factory()->create([
            'role' => 'almacenista',
            'approved_at' => now(),
        ]);
        $material = $this->material('Valvula de prueba', 5);
        $entrada = MaterialEntradaPendiente::create([
            'material_id' => $material->id,
            'user_id' => $almacenista->id,
            'cantidad' => 3,
            'estado' => 'pendiente',
            'proveedor' => 'Proveedor de prueba',
            'costo_unitario' => 25.50,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.entradas.approve', $entrada))
            ->assertSessionHasNoErrors();

        $this->assertSame(8, $material->fresh()->stock);
        $this->assertSame('Proveedor de prueba', $material->fresh()->proveedor);
        $this->assertSame('25.50', $material->fresh()->costo_unitario);
        $this->assertDatabaseHas('material_movimientos', [
            'material_id' => $material->id,
            'proveedor' => 'Proveedor de prueba',
            'costo_unitario' => 25.50,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.entradas.approve', $entrada))
            ->assertSessionHasErrors('entrada');

        $this->assertSame(8, $material->fresh()->stock);
        $this->assertDatabaseCount('material_movimientos', 1);
    }

    public function test_manual_output_never_allows_negative_stock(): void
    {
        $user = User::factory()->create([
            'role' => 'almacenista',
            'approved_at' => now(),
        ]);
        $material = $this->material('Conector de prueba', 5);

        $this->actingAs($user)
            ->post(route('materiales.salidas.store'), [
                'material_id' => $material->id,
                'cantidad' => 6,
            ])
            ->assertSessionHasErrors('cantidad');

        $this->assertSame(5, $material->fresh()->stock);
        $this->assertDatabaseCount('material_movimientos', 0);
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
