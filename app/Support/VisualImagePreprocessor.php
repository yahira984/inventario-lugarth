<?php

namespace App\Support;

use RuntimeException;

class VisualImagePreprocessor
{
    public const CANVAS_SIZE = 512;

    private const CONTENT_SIZE = 464;

    /**
     * Creates one aspect-preserving view for CLIP and one square view for DINO.
     *
     * @return array{
     *     semantic_path: string,
     *     semantic_temporary: bool,
     *     detail_path: string,
     *     detail_temporary: bool
     * }
     */
    public function prepare(string $path): array
    {
        if (! function_exists('imagecreatefromstring')) {
            return $this->originalPaths($path);
        }

        $contents = @file_get_contents($path);
        $source = $contents === false ? false : @imagecreatefromstring($contents);
        unset($contents);

        if (! $source) {
            return $this->originalPaths($path);
        }

        $source = $this->applyExifOrientation($source, $path);
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth < 1 || $sourceHeight < 1) {
            imagedestroy($source);

            return $this->originalPaths($path);
        }

        $directory = storage_path('app/visual-ai/tmp');
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            imagedestroy($source);
            throw new RuntimeException('No se pudo crear el directorio temporal de la IA visual.');
        }

        $token = bin2hex(random_bytes(8));
        $semanticPath = $directory.'/semantic-'.$token.'.jpg';
        $detailPath = $directory.'/detail-'.$token.'.jpg';
        $semanticWritten = false;
        $detailWritten = false;

        try {
            $semantic = $this->aspectPreservingCopy($source, 1600);
            $detail = $this->squareCopy($source, $sourceWidth, $sourceHeight);

            $semanticWritten = imagejpeg($semantic, $semanticPath, 88);
            $detailWritten = imagejpeg($detail, $detailPath, 90);

            imagedestroy($semantic);
            imagedestroy($detail);
        } finally {
            imagedestroy($source);
        }

        if (! $semanticWritten || ! $detailWritten) {
            @unlink($semanticPath);
            @unlink($detailPath);

            throw new RuntimeException('No se pudo normalizar la fotografia para el analisis visual.');
        }

        return [
            'semantic_path' => $semanticPath,
            'semantic_temporary' => true,
            'detail_path' => $detailPath,
            'detail_temporary' => true,
        ];
    }

    /**
     * Backwards-compatible helper for square-only consumers.
     *
     * @return array{0: string, 1: bool}
     */
    public function normalize(string $path): array
    {
        $prepared = $this->prepare($path);

        if ($prepared['semantic_temporary']
            && $prepared['semantic_path'] !== $prepared['detail_path']) {
            @unlink($prepared['semantic_path']);
        }

        return [$prepared['detail_path'], $prepared['detail_temporary']];
    }

    /**
     * @return array{
     *     semantic_path: string,
     *     semantic_temporary: bool,
     *     detail_path: string,
     *     detail_temporary: bool
     * }
     */
    private function originalPaths(string $path): array
    {
        return [
            'semantic_path' => $path,
            'semantic_temporary' => false,
            'detail_path' => $path,
            'detail_temporary' => false,
        ];
    }

    private function aspectPreservingCopy($source, int $maximumSide)
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, $maximumSide / max($sourceWidth, $sourceHeight));
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));

        return $this->resample($source, $width, $height);
    }

    private function squareCopy($source, int $sourceWidth, int $sourceHeight)
    {
        $scale = self::CONTENT_SIZE / max($sourceWidth, $sourceHeight);
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $resized = $this->resample($source, $width, $height);

        $canvas = imagecreatetruecolor(self::CANVAS_SIZE, self::CANVAS_SIZE);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy(
            $canvas,
            $resized,
            (int) floor((self::CANVAS_SIZE - $width) / 2),
            (int) floor((self::CANVAS_SIZE - $height) / 2),
            0,
            0,
            $width,
            $height
        );
        imagedestroy($resized);

        return $canvas;
    }

    private function resample($source, int $width, int $height)
    {
        $resized = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled(
            $resized,
            $source,
            0,
            0,
            0,
            0,
            $width,
            $height,
            imagesx($source),
            imagesy($source)
        );

        return $resized;
    }

    private function applyExifOrientation($image, string $path)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $imageType = @exif_imagetype($path);
        if ($imageType !== IMAGETYPE_JPEG) {
            return $image;
        }

        $exif = @exif_read_data($path, 'IFD0', true);
        $orientation = (int) ($exif['IFD0']['Orientation'] ?? $exif['Orientation'] ?? 1);

        return match ($orientation) {
            2 => $this->flip($image, IMG_FLIP_HORIZONTAL),
            3 => $this->rotate($image, 180),
            4 => $this->flip($image, IMG_FLIP_VERTICAL),
            5 => $this->flip($this->rotate($image, -90), IMG_FLIP_HORIZONTAL),
            6 => $this->rotate($image, -90),
            7 => $this->flip($this->rotate($image, 90), IMG_FLIP_HORIZONTAL),
            8 => $this->rotate($image, 90),
            default => $image,
        };
    }

    private function rotate($image, int $degrees)
    {
        $white = imagecolorallocate($image, 255, 255, 255);
        $rotated = imagerotate($image, $degrees, $white);

        if (! $rotated) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private function flip($image, int $mode)
    {
        imageflip($image, $mode);

        return $image;
    }
}
