<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialEntradaPendiente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPendingEntryEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_correct_an_existing_material_entry_before_approval(): void
    {
        Storage::fake('public');

        $admin = $this->user('administrador');
        $warehousekeeper = $this->user('almacenista');
        $wrongMaterial = $this->material('Pieza seleccionada por error', 10);
        $correctMaterial = $this->material('Pieza correcta', 4);
        Storage::disk('public')->put('entradas-pendientes/evidencia-anterior.jpg', 'old-image');

        $entry = MaterialEntradaPendiente::create([
            'material_id' => $wrongMaterial->id,
            'user_id' => $warehousekeeper->id,
            'cantidad' => 2,
            'estado' => 'pendiente',
            'referencia' => 'OC equivocada',
            'motivo' => 'Pendiente de aprobacion',
            'evidencia_foto' => 'entradas-pendientes/evidencia-anterior.jpg',
            'proveedor' => 'Proveedor anterior',
            'costo_unitario' => 10,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.entradas.edit', $entry))
            ->assertOk()
            ->assertSee('Corregir entrada')
            ->assertSee('Pieza correcta');

        $this->actingAs($admin)
            ->patch(route('admin.entradas.update', $entry), [
                'material_id' => $correctMaterial->id,
                'cantidad' => 7,
                'proveedor' => 'Proveedor corregido',
                'costo_unitario' => 42.75,
                'referencia' => 'OC-2026-145',
                'motivo' => 'Recepcion de compra',
                'comentario_admin' => 'Se corrigio la pieza y la cantidad.',
                'evidencia_foto' => UploadedFile::fake()->image('evidencia-correcta.jpg', 900, 700),
            ])
            ->assertRedirect(route('admin.entradas.index', ['estado' => 'pendiente']))
            ->assertSessionHasNoErrors();

        $entry->refresh();

        $this->assertSame('pendiente', $entry->estado);
        $this->assertSame($correctMaterial->id, $entry->material_id);
        $this->assertSame(7, $entry->cantidad);
        $this->assertSame('Proveedor corregido', $entry->proveedor);
        $this->assertSame('42.75', $entry->costo_unitario);
        $this->assertSame('OC-2026-145', $entry->referencia);
        $this->assertSame(10, $wrongMaterial->fresh()->stock);
        $this->assertSame(4, $correctMaterial->fresh()->stock);
        $this->assertDatabaseCount('material_movimientos', 0);
        Storage::disk('public')->assertMissing('entradas-pendientes/evidencia-anterior.jpg');
        Storage::disk('public')->assertExists($entry->evidencia_foto);

        $this->actingAs($admin)
            ->patch(route('admin.entradas.approve', $entry))
            ->assertSessionHasNoErrors();

        $this->assertSame(10, $wrongMaterial->fresh()->stock);
        $this->assertSame(11, $correctMaterial->fresh()->stock);
        $this->assertDatabaseHas('material_movimientos', [
            'material_id' => $correctMaterial->id,
            'cantidad' => 7,
            'proveedor' => 'Proveedor corregido',
            'costo_unitario' => 42.75,
        ]);
    }

    public function test_admin_can_correct_all_new_material_data_and_photos_before_approval(): void
    {
        Storage::fake('public');

        $admin = $this->user('administrador');
        $warehousekeeper = $this->user('almacenista');
        Storage::disk('public')->put('entradas-pendientes/evidencia-vieja.jpg', 'old-evidence');
        Storage::disk('public')->put('entradas-pendientes/materiales/producto-viejo.jpg', 'old-product');

        $entry = MaterialEntradaPendiente::create([
            'es_material_nuevo' => true,
            'datos_material' => [
                'descripcion' => 'Nombre equivocado',
                'categoria' => 'Sin categoria',
                'stock_minimo' => 0,
                'stock_maximo' => 0,
            ],
            'user_id' => $warehousekeeper->id,
            'cantidad' => 1,
            'estado' => 'pendiente',
            'evidencia_foto' => 'entradas-pendientes/evidencia-vieja.jpg',
            'fotografia' => 'entradas-pendientes/materiales/producto-viejo.jpg',
            'costo_unitario' => 0,
        ]);

        MaterialCategory::create([
            'nombre' => 'CONEXIONES',
            'descripcion' => 'Piezas de conexion',
            'activa' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.entradas.edit', $entry))
            ->assertOk()
            ->assertSee('<select id="categoria" name="categoria">', false)
            ->assertSee('CONEXIONES');

        $this->actingAs($admin)
            ->patch(route('admin.entradas.update', $entry), [
                'descripcion' => 'Valvula corregida de acero',
                'apodo' => 'Valvula chica',
                'categoria' => 'CONEXIONES',
                'almacen' => 'Almacen general, rack C',
                'codigo_barras' => '7509990001112',
                'numero_parte' => 'VAL-C-100',
                'clave_sat' => '40141600',
                'clave_unidad' => 'H87',
                'unidad' => 'pza',
                'marca' => 'Marca corregida',
                'proveedor' => 'Proveedor final',
                'proveedor_rfc' => 'PRO010101AA1',
                'stock_minimo' => 2,
                'stock_maximo' => 20,
                'moneda' => 'MXN',
                'cantidad' => 6,
                'costo_unitario' => 125.50,
                'referencia' => 'FACT-900',
                'motivo' => 'Compra de material nuevo',
                'evidencia_foto' => UploadedFile::fake()->image('evidencia-nueva.jpg', 1200, 900),
                'fotografia' => UploadedFile::fake()->image('producto-nuevo.jpg', 700, 700),
            ])
            ->assertSessionHasNoErrors();

        $entry->refresh();

        $this->assertSame('pendiente', $entry->estado);
        $this->assertSame(6, $entry->cantidad);
        $this->assertSame('Valvula corregida de acero', $entry->datos_material['descripcion']);
        $this->assertSame('CONEXIONES', $entry->datos_material['categoria']);
        $this->assertSame('7509990001112', $entry->codigo_barras);
        $this->assertSame(2, $entry->datos_material['stock_minimo']);
        $this->assertDatabaseCount('materials', 0);
        Storage::disk('public')->assertMissing('entradas-pendientes/evidencia-vieja.jpg');
        Storage::disk('public')->assertMissing('entradas-pendientes/materiales/producto-viejo.jpg');
        Storage::disk('public')->assertExists($entry->evidencia_foto);
        Storage::disk('public')->assertExists($entry->fotografia);

        $this->actingAs($admin)
            ->patch(route('admin.entradas.approve', $entry))
            ->assertSessionHasNoErrors();

        $material = Material::where('codigo_barras', '7509990001112')->firstOrFail();
        $this->assertSame('Valvula corregida de acero', $material->descripcion);
        $this->assertSame('Valvula chica', $material->apodo);
        $this->assertSame('Almacen general, rack C', $material->almacen);
        $this->assertSame(6, $material->stock);
        $this->assertSame(2, $material->stock_minimo);
        $this->assertSame(20, $material->stock_maximo);
        $this->assertSame('125.50', $material->costo_unitario);
        $this->assertSame($entry->fotografia, $material->fotografia);
    }

    public function test_warehousekeeper_cannot_edit_a_pending_entry(): void
    {
        $warehousekeeper = $this->user('almacenista');
        $material = $this->material('Pieza protegida', 3);
        $entry = MaterialEntradaPendiente::create([
            'material_id' => $material->id,
            'user_id' => $warehousekeeper->id,
            'cantidad' => 2,
            'estado' => 'pendiente',
        ]);

        $this->actingAs($warehousekeeper)
            ->get(route('admin.entradas.edit', $entry))
            ->assertForbidden();

        $this->actingAs($warehousekeeper)
            ->patch(route('admin.entradas.update', $entry), [
                'material_id' => $material->id,
                'cantidad' => 99,
            ])
            ->assertForbidden();

        $this->assertSame(2, $entry->fresh()->cantidad);
        $this->assertSame(3, $material->fresh()->stock);
    }

    private function user(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'approved_at' => now(),
        ]);
    }

    private function material(string $description, int $stock): Material
    {
        return Material::create([
            'descripcion' => $description,
            'stock' => $stock,
            'es_plantilla_equipo' => false,
        ]);
    }
}
