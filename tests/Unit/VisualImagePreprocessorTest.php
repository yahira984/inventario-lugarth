<?php

namespace Tests\Unit;

use App\Support\VisualImagePreprocessor;
use Tests\TestCase;

class VisualImagePreprocessorTest extends TestCase
{
    public function test_it_preserves_aspect_ratio_and_centers_the_image_on_a_square_canvas(): void
    {
        $directory = storage_path('app/visual-ai/tmp');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $sourcePath = $directory.'/preprocessor-source-'.bin2hex(random_bytes(4)).'.jpg';
        $source = imagecreatetruecolor(100, 300);
        $red = imagecolorallocate($source, 220, 35, 35);
        imagefill($source, 0, 0, $red);
        imagejpeg($source, $sourcePath, 95);
        imagedestroy($source);

        [$normalizedPath, $temporary] = app(VisualImagePreprocessor::class)->normalize($sourcePath);

        try {
            $size = getimagesize($normalizedPath);
            $normalized = imagecreatefromjpeg($normalizedPath);
            $corner = imagecolorat($normalized, 4, 4);
            $center = imagecolorat($normalized, 256, 256);
            imagedestroy($normalized);

            $this->assertTrue($temporary);
            $this->assertSame(VisualImagePreprocessor::CANVAS_SIZE, $size[0]);
            $this->assertSame(VisualImagePreprocessor::CANVAS_SIZE, $size[1]);
            $this->assertGreaterThan(245, ($corner >> 16) & 0xFF);
            $this->assertGreaterThan(180, ($center >> 16) & 0xFF);
            $this->assertLessThan(90, ($center >> 8) & 0xFF);
        } finally {
            @unlink($sourcePath);
            if ($temporary) {
                @unlink($normalizedPath);
            }
        }
    }
}
