<?php

namespace App\Support;

use App\Models\Material;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VisualImageDescriptor
{
    private const VERSION = 1;

    /** @var array<string, array<string, mixed>> */
    private array $runtimeCache = [];

    /**
     * @return array<string, mixed>
     */
    public function forMaterial(Material $material, bool $force = false): array
    {
        $relativePath = trim((string) $material->fotografia);

        if ($relativePath === '') {
            $this->clearStoredDescriptor($material);

            return [];
        }

        $absolutePath = Storage::disk('public')->path(ltrim($relativePath, '/\\'));

        if (! is_file($absolutePath)) {
            $this->clearStoredDescriptor($material);

            return [];
        }

        $signature = $this->signature($relativePath, $absolutePath);
        $storedDescriptor = $material->visual_descriptor;

        if (! $force
            && $material->visual_descriptor_signature === $signature
            && is_array($storedDescriptor)
            && $storedDescriptor !== []) {
            return $storedDescriptor;
        }

        $descriptor = $this->runtimeCache[$signature] ??= $this->fromPath($absolutePath);
        $this->persist($material, $descriptor, $signature);

        return $descriptor;
    }

    /**
     * @return array<string, mixed>
     */
    public function fromPath(string $path): array
    {
        $size = @getimagesize($path);
        $descriptor = [
            'version' => self::VERSION,
            'calidad' => 'basica',
            'sha1' => is_file($path) ? @sha1_file($path) : null,
            'width' => $size[0] ?? null,
            'height' => $size[1] ?? null,
            'ahash' => null,
            'dhash' => null,
            'histogram' => [],
            'aspect_ratio' => null,
            'brightness' => null,
            'color' => null,
            'forma' => null,
        ];

        if (! function_exists('imagecreatefromstring')) {
            return $descriptor;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return $descriptor;
        }

        $image = @imagecreatefromstring($contents);
        unset($contents);

        if (! $image) {
            return $descriptor;
        }

        $descriptor['ahash'] = $this->averageHash($image);
        $descriptor['dhash'] = $this->differenceHash($image);

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        $maxSize = 180;
        $scale = min($maxSize / max($originalWidth, 1), $maxSize / max($originalHeight, 1), 1);
        $width = max(1, (int) round($originalWidth * $scale));
        $height = max(1, (int) round($originalHeight * $scale));
        $sample = imagecreatetruecolor($width, $height);
        imagecopyresampled($sample, $image, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
        imagedestroy($image);

        $background = $this->averageCornerColor($sample, $width, $height);
        $minX = $width;
        $minY = $height;
        $maxX = 0;
        $maxY = 0;
        $foregroundPixels = 0;
        $redTotal = 0;
        $greenTotal = 0;
        $blueTotal = 0;
        $brightnessTotal = 0;
        $saturationTotal = 0;
        $histogram = array_fill(0, 64, 0);

        for ($y = 0; $y < $height; $y += 2) {
            for ($x = 0; $x < $width; $x += 2) {
                [$red, $green, $blue] = $this->rgbAt($sample, $x, $y);
                $distance = $this->colorDistance([$red, $green, $blue], $background);
                $brightness = ($red + $green + $blue) / 3;
                $saturation = max($red, $green, $blue) - min($red, $green, $blue);

                if ($distance > 34 || ($saturation > 42 && $distance > 18)) {
                    $foregroundPixels++;
                    $minX = min($minX, $x);
                    $minY = min($minY, $y);
                    $maxX = max($maxX, $x);
                    $maxY = max($maxY, $y);
                    $redTotal += $red;
                    $greenTotal += $green;
                    $blueTotal += $blue;
                    $brightnessTotal += $brightness;
                    $saturationTotal += $saturation;
                    $histogram[$this->histogramIndex($red, $green, $blue)]++;
                }
            }
        }

        imagedestroy($sample);

        if ($foregroundPixels === 0) {
            return $descriptor;
        }

        $boxWidth = max(1, $maxX - $minX + 1);
        $boxHeight = max(1, $maxY - $minY + 1);
        $objectRatio = max($boxWidth / $boxHeight, $boxHeight / $boxWidth);
        $averageRed = $redTotal / $foregroundPixels;
        $averageGreen = $greenTotal / $foregroundPixels;
        $averageBlue = $blueTotal / $foregroundPixels;
        $averageBrightness = $brightnessTotal / $foregroundPixels;
        $averageSaturation = $saturationTotal / $foregroundPixels;

        return array_merge($descriptor, [
            'calidad' => 'ok',
            'aspect_ratio' => round($objectRatio, 3),
            'foreground_ratio' => round($foregroundPixels / max(1, (($width / 2) * ($height / 2))), 4),
            'brightness' => round($averageBrightness, 2),
            'saturation' => round($averageSaturation, 2),
            'histogram' => array_map(fn (int $value) => $value / $foregroundPixels, $histogram),
            'color' => $this->classifyColor(
                $averageRed,
                $averageGreen,
                $averageBlue,
                $averageBrightness,
                $averageSaturation
            ),
            'forma' => $this->classifyShape($objectRatio),
        ]);
    }

    private function signature(string $relativePath, string $absolutePath): string
    {
        return hash('sha256', implode('|', [
            self::VERSION,
            str_replace('\\', '/', $relativePath),
            (int) @filesize($absolutePath),
            (int) @filemtime($absolutePath),
        ]));
    }

    /**
     * @param  array<string, mixed>  $descriptor
     */
    private function persist(Material $material, array $descriptor, string $signature): void
    {
        Model::withoutTimestamps(function () use ($material, $descriptor, $signature): void {
            $material->forceFill([
                'visual_descriptor' => $descriptor,
                'visual_descriptor_signature' => $signature,
            ])->saveQuietly();
        });
    }

    private function clearStoredDescriptor(Material $material): void
    {
        if ($material->visual_descriptor === null && $material->visual_descriptor_signature === null) {
            return;
        }

        Model::withoutTimestamps(function () use ($material): void {
            $material->forceFill([
                'visual_descriptor' => null,
                'visual_descriptor_signature' => null,
            ])->saveQuietly();
        });
    }

    private function averageHash($image): string
    {
        $sample = $this->resizeForHash($image, 8, 8);
        $grays = [];
        $total = 0;

        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                [$red, $green, $blue] = $this->rgbAt($sample, $x, $y);
                $gray = ($red * 0.299) + ($green * 0.587) + ($blue * 0.114);
                $grays[] = $gray;
                $total += $gray;
            }
        }

        imagedestroy($sample);
        $average = $total / 64;

        return implode('', array_map(fn (float $gray) => $gray >= $average ? '1' : '0', $grays));
    }

    private function differenceHash($image): string
    {
        $sample = $this->resizeForHash($image, 9, 8);
        $bits = [];

        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                [$red1, $green1, $blue1] = $this->rgbAt($sample, $x, $y);
                [$red2, $green2, $blue2] = $this->rgbAt($sample, $x + 1, $y);
                $gray1 = ($red1 * 0.299) + ($green1 * 0.587) + ($blue1 * 0.114);
                $gray2 = ($red2 * 0.299) + ($green2 * 0.587) + ($blue2 * 0.114);
                $bits[] = $gray1 > $gray2 ? '1' : '0';
            }
        }

        imagedestroy($sample);

        return implode('', $bits);
    }

    private function resizeForHash($image, int $width, int $height)
    {
        $sample = imagecreatetruecolor($width, $height);
        imagecopyresampled($sample, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $sample;
    }

    private function histogramIndex(int $red, int $green, int $blue): int
    {
        $redBin = min(3, intdiv($red, 64));
        $greenBin = min(3, intdiv($green, 64));
        $blueBin = min(3, intdiv($blue, 64));

        return ($redBin * 16) + ($greenBin * 4) + $blueBin;
    }

    private function averageCornerColor($image, int $width, int $height): array
    {
        $points = [
            [0, 0],
            [max(0, $width - 1), 0],
            [0, max(0, $height - 1)],
            [max(0, $width - 1), max(0, $height - 1)],
        ];

        $total = [0, 0, 0];
        foreach ($points as [$x, $y]) {
            [$red, $green, $blue] = $this->rgbAt($image, $x, $y);
            $total[0] += $red;
            $total[1] += $green;
            $total[2] += $blue;
        }

        return [$total[0] / 4, $total[1] / 4, $total[2] / 4];
    }

    private function rgbAt($image, int $x, int $y): array
    {
        $color = imagecolorat($image, $x, $y);

        return [
            ($color >> 16) & 0xFF,
            ($color >> 8) & 0xFF,
            $color & 0xFF,
        ];
    }

    private function colorDistance(array $first, array $second): float
    {
        return sqrt(
            (($first[0] - $second[0]) ** 2)
            + (($first[1] - $second[1]) ** 2)
            + (($first[2] - $second[2]) ** 2)
        );
    }

    private function classifyColor(
        float $red,
        float $green,
        float $blue,
        float $brightness,
        float $saturation
    ): ?string {
        if ($brightness < 82) {
            return 'oscuro';
        }

        if ($red > 118 && $green > 90 && $blue < 95 && ($red - $blue) > 38) {
            return 'dorado';
        }

        if ($saturation < 45 && $brightness > 95) {
            return 'plateado';
        }

        return null;
    }

    private function classifyShape(float $ratio): string
    {
        if ($ratio >= 2.45) {
            return 'alargada';
        }

        if ($ratio >= 1.45) {
            return 'media';
        }

        return 'redonda';
    }
}
