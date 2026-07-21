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

    public function test_cluttered_phone_photos_prefer_the_matching_piece_shape(): void
    {
        Storage::fake('public');

        $flagPath = $this->createShapePhoto('materiales/banderola.jpg', 'triangle');
        $rectanglePath = $this->createShapePhoto('materiales/etiqueta-roja.jpg', 'rectangle');

        Material::create([
            'categoria' => 'SEGURIDAD',
            'descripcion' => 'Banderola triangular',
            'stock' => 2,
            'es_plantilla_equipo' => false,
            'fotografia' => $flagPath,
        ]);
        Material::create([
            'categoria' => 'PINTURA',
            'descripcion' => 'Cubeta con etiqueta roja',
            'stock' => 2,
            'es_plantilla_equipo' => false,
            'fotografia' => $rectanglePath,
        ]);

        $user = User::factory()->create([
            'role' => 'consultor',
            'approved_at' => now(),
        ]);
        $query = UploadedFile::fake()
            ->createWithContent('foto-celular.jpg', $this->createClutteredShapePhoto('triangle'))
            ->mimeType('image/jpeg');

        $response = $this->actingAs($user)->post(route('materiales.visual.search'), [
            'fotografia' => $query,
        ]);

        $response->assertOk()
            ->assertSee('Banderola triangular')
            ->assertDontSee('Cubeta con etiqueta roja');

        $rectangleQuery = UploadedFile::fake()
            ->createWithContent('foto-rectangulo.jpg', $this->createClutteredShapePhoto('rectangle'))
            ->mimeType('image/jpeg');

        $rectangleResponse = $this->actingAs($user)->post(route('materiales.visual.search'), [
            'fotografia' => $rectangleQuery,
        ]);

        $rectangleResponse->assertOk()
            ->assertSee('Cubeta con etiqueta roja')
            ->assertDontSee('Banderola triangular');
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

    private function createShapePhoto(string $path, string $shape): string
    {
        $image = imagecreatetruecolor(320, 240);
        $white = imagecolorallocate($image, 255, 255, 255);
        $red = imagecolorallocate($image, 225, 55, 32);
        $gray = imagecolorallocate($image, 105, 115, 125);

        imagefilledrectangle($image, 0, 0, 319, 239, $white);

        if ($shape === 'triangle') {
            imagefilledpolygon($image, [72, 45, 268, 120, 72, 195], 3, $red);
            imagefilledrectangle($image, 62, 35, 72, 220, $gray);
        } else {
            imagefilledrectangle($image, 68, 58, 252, 182, $red);
            imageellipse($image, 160, 120, 205, 165, $gray);
        }

        ob_start();
        imagejpeg($image, null, 88);
        $contents = ob_get_clean();
        imagedestroy($image);
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    private function createClutteredShapePhoto(string $shape): string
    {
        $image = imagecreatetruecolor(360, 420);
        $dark = imagecolorallocate($image, 58, 63, 65);
        $light = imagecolorallocate($image, 194, 187, 169);
        $metal = imagecolorallocate($image, 118, 126, 130);
        $red = imagecolorallocate($image, 215, 50, 28);

        imagefilledrectangle($image, 0, 0, 359, 419, $light);
        imagefilledrectangle($image, 0, 0, 359, 105, $dark);
        imagefilledrectangle($image, 18, 120, 70, 410, $metal);
        imagefilledrectangle($image, 300, 95, 345, 410, $dark);

        if ($shape === 'triangle') {
            imagefilledpolygon($image, [78, 142, 298, 218, 78, 338], 3, $red);
        } else {
            imagefilledrectangle($image, 82, 145, 292, 330, $red);
        }

        imagefilledrectangle($image, 69, 125, 79, 405, $metal);

        ob_start();
        imagejpeg($image, null, 82);
        $contents = ob_get_clean();
        imagedestroy($image);

        return $contents;
    }
}
