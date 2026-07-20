<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialEntradaPendiente;
use App\Models\MaterialMovimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

    public function test_warehousekeeper_can_request_a_new_material_and_stock_is_added_only_after_approval(): void
    {
        Storage::fake('public');
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);
        $almacenista = User::factory()->create([
            'role' => 'almacenista',
            'approved_at' => now(),
        ]);

        $this->actingAs($almacenista)
            ->post(route('materiales.store'), [
                'codigo_barras' => '7501234567890',
                'descripcion' => 'Valvula nueva de prueba',
                'apodo' => 'Valvula chica',
                'categoria' => 'Conexiones',
                'almacen' => 'Almacen principal, rack B',
                'marca' => 'Lugarth Test',
                'proveedor' => 'Proveedor nuevo',
                'stock' => 4,
                'stock_minimo' => 1,
                'stock_maximo' => 10,
                'costo_unitario' => 32.50,
                'fotografia' => UploadedFile::fake()->image('producto.jpg', 600, 600),
                'evidencia_foto' => UploadedFile::fake()->image('recepcion.jpg', 800, 600),
            ])
            ->assertRedirect(route('materiales.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('materials', ['codigo_barras' => '7501234567890']);
        $this->assertDatabaseCount('material_movimientos', 0);

        $entrada = MaterialEntradaPendiente::firstOrFail();
        $this->assertTrue($entrada->es_material_nuevo);
        $this->assertNull($entrada->material_id);
        $this->assertSame('Valvula nueva de prueba', $entrada->datos_material['descripcion']);
        $this->assertSame(4, $entrada->cantidad);
        Storage::disk('public')->assertExists($entrada->evidencia_foto);
        Storage::disk('public')->assertExists($entrada->fotografia);

        $this->actingAs($admin)
            ->patch(route('admin.entradas.approve', $entrada))
            ->assertSessionHasNoErrors();

        $material = Material::where('codigo_barras', '7501234567890')->firstOrFail();
        $this->assertSame(4, $material->stock);
        $this->assertSame('Almacen principal, rack B', $material->almacen);
        $this->assertSame('Lugarth Test', $material->marca);
        $this->assertSame('Proveedor nuevo', $material->proveedor);
        $this->assertSame($entrada->fotografia, $material->fotografia);

        $movimiento = MaterialMovimiento::where('material_id', $material->id)->firstOrFail();
        $this->assertSame(0, $movimiento->stock_anterior);
        $this->assertSame(4, $movimiento->stock_nuevo);
        $this->assertSame($almacenista->id, $movimiento->user_id);

        $entrada->refresh();
        $this->assertSame('aprobada', $entrada->estado);
        $this->assertSame($material->id, $entrada->material_id);

        $this->actingAs($admin)
            ->patch(route('admin.entradas.approve', $entrada))
            ->assertSessionHasErrors('entrada');

        $this->assertSame(4, $material->fresh()->stock);
        $this->assertDatabaseCount('material_movimientos', 1);
    }

    public function test_warehousekeeper_can_see_storage_and_brand_but_not_supplier_in_inventory(): void
    {
        $almacenista = User::factory()->create([
            'role' => 'almacenista',
            'approved_at' => now(),
        ]);
        Material::create([
            'descripcion' => 'Empaque visible de prueba',
            'categoria' => 'Empaques',
            'almacen' => 'Almacen secundario, estante 4',
            'marca' => 'Marca Delta',
            'proveedor' => 'Proveedor Delta',
            'stock' => 7,
            'es_plantilla_equipo' => false,
        ]);

        $this->actingAs($almacenista)
            ->get(route('materiales.index'))
            ->assertOk()
            ->assertSee('Almacen')
            ->assertSee('Almacen secundario, estante 4')
            ->assertSee('Marca Delta')
            ->assertDontSee('Proveedor Delta');
    }

    public function test_application_uses_mexico_city_timezone(): void
    {
        $this->assertSame('America/Mexico_City', config('app.timezone'));
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
