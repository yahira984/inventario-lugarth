<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisualIdentifierIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_material_photo_is_indexed_and_exact_search_returns_one_hundred_points(): void
    {
        Storage::fake('public');

        $path = $this->createPiecePhoto('materiales/pieza.jpg');

        $material = Material::create([
            'categoria' => 'PRUEBAS',
            'numero_parte' => 'VIS-001',
            'descripcion' => 'Pieza visual exacta',
            'stock' => 3,
            'es_plantilla_equipo' => false,
            'fotografia' => $path,
        ])->fresh();

        $this->assertNotEmpty($material->visual_descriptor);
        $this->assertSame('ok', $material->visual_descriptor['calidad']);
        $this->assertNotEmpty($material->visual_descriptor_signature);

        $user = User::factory()->create([
            'role' => 'consultor',
            'approved_at' => now(),
        ]);
        $searchPhoto = UploadedFile::fake()
            ->createWithContent('consulta.jpg', Storage::disk('public')->get($path))
            ->mimeType('image/jpeg');

        $response = $this->actingAs($user)->post(route('materiales.visual.search'), [
            'fotografia' => $searchPhoto,
        ]);

        $response->assertOk()
            ->assertSee('Pieza visual exacta')
            ->assertSee('100 pts');
    }

    public function test_reindex_command_prepares_missing_descriptors(): void
    {
        Storage::fake('public');

        $path = $this->createPiecePhoto('materiales/reindexar.jpg');
        $material = Material::create([
            'descripcion' => 'Pieza para reindexar',
            'stock' => 1,
            'es_plantilla_equipo' => false,
            'fotografia' => $path,
        ]);

        $material->forceFill([
            'visual_descriptor' => null,
            'visual_descriptor_signature' => null,
        ])->saveQuietly();

        $this->artisan('visual:reindex')
            ->assertSuccessful();

        $material->refresh();

        $this->assertSame('ok', $material->visual_descriptor['calidad']);
        $this->assertNotEmpty($material->visual_descriptor_signature);

        $material->update(['fotografia' => null]);
        $material->refresh();

        $this->assertNull($material->visual_descriptor);
        $this->assertNull($material->visual_descriptor_signature);
    }

    private function createPiecePhoto(string $path): string
    {
        $image = imagecreatetruecolor(320, 240);
        $white = imagecolorallocate($image, 255, 255, 255);
        $blue = imagecolorallocate($image, 25, 90, 170);
        $gray = imagecolorallocate($image, 110, 120, 130);

        imagefilledrectangle($image, 0, 0, 319, 239, $white);
        imagefilledellipse($image, 160, 120, 190, 110, $blue);
        imagefilledrectangle($image, 145, 48, 175, 192, $gray);

        ob_start();
        imagejpeg($image, null, 85);
        $contents = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
