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
}
