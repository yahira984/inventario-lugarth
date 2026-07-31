<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImageStorage
{
    public static function storeOptimized(UploadedFile $file, string $folder, int $maxWidth = 1600, int $quality = 72): string
    {
        $folder = trim($folder, '/');
        $useWebp = function_exists('imagewebp');
        $extension = $useWebp ? 'webp' : 'jpg';
        $name = now()->format('Ymd_His') . '_' . bin2hex(random_bytes(5)) . ".{$extension}";
        $relativePath = "{$folder}/{$name}";

        $sourcePath = $file->getRealPath();
        $info = @getimagesize($sourcePath);

        if (! $info || ! function_exists('imagecreatetruecolor')) {
            return self::storeOriginal($file, $folder);
        }

        $source = match ($info['mime'] ?? '') {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : null,
            default => null,
        };

        if (! $source) {
            return self::storeOriginal($file, $folder);
        }

        $source = self::orientJpeg($source, $sourcePath, $info['mime'] ?? '');
        $width = imagesx($source);
        $height = imagesy($source);
        $ratio = min(1, $maxWidth / max($width, $height));
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        $encoded = $useWebp
            ? imagewebp($canvas, null, $quality)
            : imagejpeg($canvas, null, $quality);
        $contents = ob_get_clean();
        imagedestroy($canvas);
        imagedestroy($source);

        if (! $encoded || ! is_string($contents) || $contents === '') {
            return self::storeOriginal($file, $folder);
        }

        if (! Storage::disk('public')->put($relativePath, $contents)) {
            throw new RuntimeException('No se pudo escribir la imagen optimizada en el almacenamiento público.');
        }

        return $relativePath;
    }

    private static function orientJpeg($source, string $sourcePath, string $mime)
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $source;
        }

        $orientation = (int) (@exif_read_data($sourcePath)['Orientation'] ?? 1);
        $degrees = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($degrees === 0) {
            return $source;
        }

        $rotated = imagerotate($source, $degrees, 0);
        if ($rotated === false) {
            return $source;
        }

        imagedestroy($source);

        return $rotated;
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private static function storeOriginal(UploadedFile $file, string $folder): string
    {
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $name = now()->format('Ymd_His') . '_' . bin2hex(random_bytes(5)) . ".{$extension}";
        $storedPath = $file->storeAs($folder, $name, 'public');

        if (! is_string($storedPath) || $storedPath === '') {
            throw new RuntimeException('No se pudo escribir la imagen en el almacenamiento público.');
        }

        return $storedPath;
    }
}
