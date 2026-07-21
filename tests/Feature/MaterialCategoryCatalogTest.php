<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialCategoryCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_synchronizes_categories_used_by_real_materials(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);

        MaterialCategory::create([
            'nombre' => 'CATEGORIA DESACTIVADA',
            'activa' => false,
        ]);

        Material::create([
            'categoria' => 'CONEXIONES DE AIRE',
            'descripcion' => 'Conector de prueba',
            'stock' => 2,
            'es_plantilla_equipo' => false,
        ]);

        Material::create([
            'categoria' => 'EQUIPO PRUEBA',
            'descripcion' => 'Plantilla de equipo',
            'stock' => 0,
            'es_plantilla_equipo' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.categorias.index'));

        $response->assertOk()->assertSee('CONEXIONES DE AIRE');
        $this->assertDatabaseHas('material_categories', [
            'nombre' => 'CONEXIONES DE AIRE',
            'activa' => true,
        ]);
        $this->assertDatabaseMissing('material_categories', [
            'nombre' => 'EQUIPO PRUEBA',
        ]);
        $this->assertDatabaseHas('material_categories', [
            'nombre' => 'CATEGORIA DESACTIVADA',
            'activa' => false,
        ]);
    }

    public function test_equipment_names_are_not_offered_as_material_categories(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);

        MaterialCategory::create([
            'nombre' => 'EQUIPO ACERO AL CARBON',
            'activa' => true,
        ]);
        MaterialCategory::create([
            'nombre' => 'SOLDADURA',
            'activa' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('materiales.index'));

        $response->assertOk()
            ->assertDontSee('EQUIPO ACERO AL CARBON')
            ->assertSee('SOLDADURA');
    }

    public function test_equipment_name_cannot_be_created_as_a_category(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.categorias.store'), [
            'nombre' => 'EQUIPO DE PRUEBA',
        ]);

        $response->assertSessionHasErrors('nombre');
        $this->assertDatabaseMissing('material_categories', [
            'nombre' => 'EQUIPO DE PRUEBA',
        ]);
    }
}
