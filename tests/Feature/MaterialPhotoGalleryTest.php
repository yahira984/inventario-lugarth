<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialEntradaPendiente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialPhotoGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_only_three_optimized_photos_per_material(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);
        $material = Material::create([
            'descripcion' => 'Pieza con tres angulos',
            'stock' => 1,
            'es_plantilla_equipo' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('materiales.photos.store', $material), [
            'photos' => [
                UploadedFile::fake()->image('frente.jpg', 1800, 900),
                UploadedFile::fake()->image('lado.jpg', 900, 1800),
                UploadedFile::fake()->image('detalle.jpg', 1400, 1400),
            ],
            'angle' => 'Referencias',
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors();
        $material->refresh();

        $this->assertCount(3, $material->photos);
        $this->assertNotNull($material->fotografia);
        $this->assertSame(1, $material->photos->where('es_principal', true)->count());

        foreach ($material->photos as $photo) {
            Storage::disk('public')->assertExists($photo->path);
            [$width, $height] = getimagesize(Storage::disk('public')->path($photo->path));
            $this->assertLessThanOrEqual(1100, max($width, $height));
        }

        $this->actingAs($admin)
            ->from(route('materiales.edit', $material))
            ->post(route('materiales.photos.store', $material), [
                'photos' => [UploadedFile::fake()->image('cuarta.jpg', 800, 800)],
            ])
            ->assertRedirect(route('materiales.edit', $material))
            ->assertSessionHasErrors('photos');

        $this->assertSame(3, $material->photos()->count());
    }

    public function test_registering_a_new_material_accepts_three_reference_photos(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('materiales.store'), [
                'descripcion' => 'Valvula fotografiada desde tres angulos',
                'stock' => 4,
                'fotografias' => [
                    UploadedFile::fake()->image('frente.jpg', 1600, 900),
                    UploadedFile::fake()->image('costado.jpg', 900, 1600),
                    UploadedFile::fake()->image('detalle.jpg', 1400, 1400),
                ],
            ])
            ->assertRedirect(route('materiales.index'))
            ->assertSessionHasNoErrors();

        $material = Material::where('descripcion', 'Valvula fotografiada desde tres angulos')->firstOrFail();

        $this->assertSame(3, $material->photos()->count());
        $this->assertSame($material->fotografia, $material->photos()->where('es_principal', true)->value('path'));

        foreach ($material->photos as $photo) {
            Storage::disk('public')->assertExists($photo->path);
        }
    }

    public function test_continuous_entry_returns_to_a_clean_registration_screen(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('materiales.store'), [
                'descripcion' => 'Pieza de captura continua',
                'stock' => 3,
                'modo_continuo' => 1,
            ])
            ->assertRedirect(route('materiales.create', ['continuo' => 1]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('materials', [
            'descripcion' => 'Pieza de captura continua',
            'stock' => 3,
        ]);
    }

    public function test_warehousekeeper_can_submit_three_photos_for_a_new_material_pending_approval(): void
    {
        Storage::fake('public');

        $warehousekeeper = User::factory()->create([
            'role' => 'almacenista',
            'approved_at' => now(),
        ]);
        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);

        $this->actingAs($warehousekeeper)
            ->post(route('materiales.store'), [
                'descripcion' => 'Conector nuevo pendiente',
                'stock' => 8,
                'evidencia_foto' => UploadedFile::fake()->image('evidencia.jpg', 1200, 900),
                'fotografias' => [
                    UploadedFile::fake()->image('frente.jpg', 1200, 900),
                    UploadedFile::fake()->image('lado.jpg', 900, 1200),
                    UploadedFile::fake()->image('detalle.jpg', 1000, 1000),
                ],
            ])
            ->assertRedirect(route('materiales.index'))
            ->assertSessionHasNoErrors();

        $entry = MaterialEntradaPendiente::where('es_material_nuevo', true)->firstOrFail();

        $this->assertCount(3, $entry->datos_material['fotografias_referencia']);
        $this->assertSame($entry->datos_material['fotografias_referencia'][0], $entry->fotografia);
        $this->assertDatabaseCount('materials', 0);

        $this->actingAs($admin)
            ->patch(route('admin.entradas.approve', $entry))
            ->assertSessionHasNoErrors();

        $material = Material::where('descripcion', 'Conector nuevo pendiente')->firstOrFail();
        $this->assertSame(8, $material->stock);
        $this->assertSame(3, $material->photos()->count());
        $this->assertSame(1, $material->photos()->where('es_principal', true)->count());
    }
}
