<?php

namespace Tests\Feature;

use App\Models\EquipmentPackage;
use App\Models\EquipmentPackageWithdrawal;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_is_grouped_and_only_shows_options_allowed_for_each_role(): void
    {
        $admin = $this->user('administrador');
        $almacenista = $this->user('almacenista');

        $this->actingAs($admin)
            ->get(route('materiales.index'))
            ->assertOk()
            ->assertSee('Operación')
            ->assertSee('Órdenes de compra')
            ->assertSee('Auditoría')
            ->assertSee('Dashboard');

        $this->actingAs($almacenista)
            ->get(route('materiales.index'))
            ->assertOk()
            ->assertSee('Registrar entrada')
            ->assertSee('Retirar equipo')
            ->assertDontSee('Órdenes de compra')
            ->assertDontSee('Auditoría')
            ->assertDontSee('href="'.route('dashboard').'"', false);
    }

    public function test_global_search_respects_roles_and_finds_materials_equipment_and_admin_data(): void
    {
        $admin = $this->user('administrador');
        $almacenista = $this->user('almacenista');
        $material = Material::create([
            'descripcion' => 'Válvula inoxidable especial',
            'apodo' => 'Válvula azul',
            'numero_parte' => 'VAL-900',
            'stock' => 4,
            'es_plantilla_equipo' => false,
            'proveedor' => 'Proveedor Industrial Norte',
        ]);
        EquipmentPackage::create(['nombre' => 'Equipo válvulas', 'codigo' => 'EQ-VAL']);

        $searchResponse = $this->actingAs($almacenista)
            ->getJson(route('buscar.global', ['q' => 'válvula']))
            ->assertOk()
            ->assertJsonFragment(['type' => 'Material', 'title' => 'Válvula inoxidable especial (Válvula azul)'])
            ->assertJsonFragment(['type' => 'Equipo', 'title' => 'Equipo válvulas']);

        $materialResult = collect($searchResponse->json('results'))->firstWhere('type', 'Material');
        $this->assertNotNull($materialResult);
        $this->assertStringContainsString('/materiales?', $materialResult['url']);
        $this->assertStringContainsString('material_id=' . $material->id, $materialResult['url']);
        $this->assertStringContainsString('#material-' . $material->id, $materialResult['url']);
        $this->assertStringNotContainsString('/edit', $materialResult['url']);

        $this->actingAs($almacenista)
            ->get(route('materiales.index', [
                'material_id' => $material->id,
                'buscar' => $material->numero_parte,
                'destacar' => $material->id,
            ]))
            ->assertOk()
            ->assertSee('id="material-' . $material->id . '"', false)
            ->assertSee('workspace-highlight-row');

        $this->actingAs($admin)
            ->getJson(route('buscar.global', ['q' => 'Proveedor Industrial']))
            ->assertOk()
            ->assertJsonFragment(['type' => 'Proveedor', 'title' => 'Proveedor Industrial Norte']);
    }

    public function test_admin_can_create_purchase_order_and_total_is_calculated_on_server(): void
    {
        $admin = $this->user('administrador');
        $material = Material::create([
            'descripcion' => 'Conector de compra',
            'stock' => 2,
            'es_plantilla_equipo' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.ordenes.store'), [
                'proveedor' => 'Proveedor de prueba',
                'referencia' => 'OC-TEST-001',
                'fecha_orden' => now()->toDateString(),
                'material_id' => [$material->id],
                'descripcion' => ['Conector de compra'],
                'cantidad' => [3],
                'costo_unitario' => [125.50],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('purchase_orders', [
            'referencia' => 'OC-TEST-001',
            'estado' => 'borrador',
            'total' => 376.50,
        ]);
        $this->assertDatabaseHas('purchase_order_items', [
            'material_id' => $material->id,
            'subtotal' => 376.50,
        ]);

        $this->actingAs($this->user('almacenista'))
            ->get(route('admin.ordenes.index'))
            ->assertForbidden();
    }

    public function test_equipment_withdrawal_pages_show_readiness_and_history(): void
    {
        $almacenista = $this->user('almacenista');
        $equipo = EquipmentPackage::create(['nombre' => 'Equipo listo', 'codigo' => 'EQ-001']);
        $material = Material::create(['descripcion' => 'Pieza suficiente', 'stock' => 10, 'es_plantilla_equipo' => false]);
        $equipo->items()->create(['material_id' => $material->id, 'descripcion' => $material->descripcion, 'cantidad_por_paquete' => 2]);
        EquipmentPackageWithdrawal::create([
            'equipment_package_id' => $equipo->id,
            'user_id' => $almacenista->id,
            'cantidad_paquetes' => 1,
            'tipo' => 'venta',
            'referencia' => 'VENTA-TEST',
        ]);

        $this->actingAs($almacenista)
            ->get(route('equipos.withdrawals.create'))
            ->assertOk()
            ->assertSee('Equipo listo')
            ->assertSee('Stock completo');

        $this->actingAs($almacenista)
            ->get(route('equipos.withdrawals.history'))
            ->assertOk()
            ->assertSee('VENTA-TEST')
            ->assertSee('Venta');
    }

    public function test_reports_are_available_to_admin_and_consultant_but_not_warehousekeeper(): void
    {
        $this->actingAs($this->user('administrador'))->get(route('reportes.index'))->assertOk();
        $this->actingAs($this->user('consultor'))->get(route('reportes.index'))->assertOk();
        $this->actingAs($this->user('almacenista'))->get(route('reportes.index'))->assertForbidden();
    }

    private function user(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'approved_at' => now(),
        ]);
    }
}
