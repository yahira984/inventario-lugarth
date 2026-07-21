<?php

namespace App\Support;

use App\Models\Material;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VisualImageDescriptor
{
    private const VERSION = 3;

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
            'regions' => [],
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

        $regions = $this->extractRegions($sample, $width, $height);
        imagedestroy($sample);

        if ($foregroundPixels === 0) {
            if ($regions !== []) {
                $descriptor['calidad'] = 'ok';
                $descriptor['regions'] = $regions;
            }

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
            'regions' => $regions,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractRegions($image, int $width, int $height): array
    {
        $totalPixels = $width * $height;
        $buckets = [];
        $rgb = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $index = ($y * $width) + $x;
                [$red, $green, $blue] = $this->rgbAt($image, $x, $y);
                $rgb[$index] = [$red, $green, $blue];
                $max = max($red, $green, $blue);
                $min = min($red, $green, $blue);
                $saturation = $max - $min;
                $brightness = ($red + $green + $blue) / 3;

                if ($saturation >= 35 && $brightness >= 24 && $brightness <= 245) {
                    $hue = $this->rgbHue($red, $green, $blue);
                    $buckets[$index] = 'c'.((int) floor(($hue + 15) / 30) % 12);
                } else {
                    $buckets[$index] = 'g'.min(5, (int) floor($brightness / 43));
                }
            }
        }

        $visited = [];
        $regions = [];
        $minimumPixels = max(18, (int) round($totalPixels * 0.0075));

        for ($start = 0; $start < $totalPixels; $start++) {
            if (isset($visited[$start])) {
                continue;
            }

            $bucket = $buckets[$start];
            $queue = [$start];
            $visited[$start] = true;
            $cursor = 0;
            $moments = array_fill_keys(['m00', 'm10', 'm01', 'm20', 'm02', 'm11', 'm30', 'm03', 'm12', 'm21'], 0.0);
            $redTotal = 0;
            $greenTotal = 0;
            $blueTotal = 0;
            $minX = $width;
            $minY = $height;
            $maxX = 0;
            $maxY = 0;
            $touches = [false, false, false, false];

            while (isset($queue[$cursor])) {
                $index = $queue[$cursor++];
                $x = $index % $width;
                $y = intdiv($index, $width);
                [$red, $green, $blue] = $rgb[$index];

                $moments['m00']++;
                $moments['m10'] += $x;
                $moments['m01'] += $y;
                $moments['m20'] += $x ** 2;
                $moments['m02'] += $y ** 2;
                $moments['m11'] += $x * $y;
                $moments['m30'] += $x ** 3;
                $moments['m03'] += $y ** 3;
                $moments['m12'] += $x * ($y ** 2);
                $moments['m21'] += ($x ** 2) * $y;
                $redTotal += $red;
                $greenTotal += $green;
                $blueTotal += $blue;
                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
                $touches[0] = $touches[0] || $x === 0;
                $touches[1] = $touches[1] || $x === ($width - 1);
                $touches[2] = $touches[2] || $y === 0;
                $touches[3] = $touches[3] || $y === ($height - 1);

                $neighbors = [
                    $x > 0 ? $index - 1 : null,
                    $x < ($width - 1) ? $index + 1 : null,
                    $y > 0 ? $index - $width : null,
                    $y < ($height - 1) ? $index + $width : null,
                ];

                foreach ($neighbors as $neighbor) {
                    if ($neighbor === null || isset($visited[$neighbor]) || $buckets[$neighbor] !== $bucket) {
                        continue;
                    }

                    $visited[$neighbor] = true;
                    $queue[] = $neighbor;
                }
            }

            $pixels = (int) $moments['m00'];
            $boxWidth = $maxX - $minX + 1;
            $boxHeight = $maxY - $minY + 1;
            $touchCount = count(array_filter($touches));

            if ($pixels < $minimumPixels
                || $boxWidth < 5
                || $boxHeight < 5
                || $pixels > ($totalPixels * 0.72)
                || ($touchCount >= 3 && $pixels > ($totalPixels * 0.18))) {
                continue;
            }

            $averageRed = $redTotal / $pixels;
            $averageGreen = $greenTotal / $pixels;
            $averageBlue = $blueTotal / $pixels;
            $maxColor = max($averageRed, $averageGreen, $averageBlue);
            $minColor = min($averageRed, $averageGreen, $averageBlue);

            $regions[] = [
                'pixels_ratio' => round($pixels / $totalPixels, 5),
                'aspect_ratio' => round(max($boxWidth / $boxHeight, $boxHeight / $boxWidth), 4),
                'fill_ratio' => round($pixels / max(1, $boxWidth * $boxHeight), 4),
                'hue' => round($this->rgbHue($averageRed, $averageGreen, $averageBlue), 2),
                'saturation' => round($maxColor - $minColor, 2),
                'brightness' => round(($averageRed + $averageGreen + $averageBlue) / 3, 2),
                'hu' => $this->huMoments($moments),
                'radial' => $this->radialSignature(
                    $queue,
                    $width,
                    $moments['m10'] / max(1, $pixels),
                    $moments['m01'] / max(1, $pixels)
                ),
            ];
        }

        usort($regions, fn (array $first, array $second) => $second['pixels_ratio'] <=> $first['pixels_ratio']);

        return array_slice($regions, 0, 10);
    }

    /**
     * Builds a rotation-independent contour profile. The comparison tries every
     * circular offset, so the photographed piece may be tilted in any direction.
     *
     * @param  array<int, int>  $pixels
     * @return array<int, float>
     */
    private function radialSignature(array $pixels, int $width, float $centerX, float $centerY): array
    {
        $binCount = 36;
        $radii = array_fill(0, $binCount, 0.0);

        foreach ($pixels as $index) {
            $x = $index % $width;
            $y = intdiv($index, $width);
            $deltaX = $x - $centerX;
            $deltaY = $y - $centerY;
            $radius = hypot($deltaX, $deltaY);

            if ($radius < 0.5) {
                continue;
            }

            $angle = atan2($deltaY, $deltaX) + M_PI;
            $bin = min($binCount - 1, (int) floor(($angle / (2 * M_PI)) * $binCount));
            $radii[$bin] = max($radii[$bin], $radius);
        }

        $maximum = max($radii);
        if ($maximum <= 0) {
            return [];
        }

        $normalized = array_map(fn (float $radius) => $radius / $maximum, $radii);
        $smoothed = [];

        for ($index = 0; $index < $binCount; $index++) {
            $previous = $normalized[($index - 1 + $binCount) % $binCount];
            $current = $normalized[$index];
            $next = $normalized[($index + 1) % $binCount];
            $smoothed[] = round(($previous + (2 * $current) + $next) / 4, 4);
        }

        return $smoothed;
    }

    /**
     * @param  array<string, float>  $moments
     * @return array<int, float>
     */
    private function huMoments(array $moments): array
    {
        $mass = max(1.0, $moments['m00']);
        $centerX = $moments['m10'] / $mass;
        $centerY = $moments['m01'] / $mass;
        $mu20 = $moments['m20'] - (2 * $centerX * $moments['m10']) + (($centerX ** 2) * $mass);
        $mu02 = $moments['m02'] - (2 * $centerY * $moments['m01']) + (($centerY ** 2) * $mass);
        $mu11 = $moments['m11'] - ($centerX * $moments['m01']) - ($centerY * $moments['m10']) + ($centerX * $centerY * $mass);
        $mu30 = $moments['m30'] - (3 * $centerX * $moments['m20']) + (3 * ($centerX ** 2) * $moments['m10']) - (($centerX ** 3) * $mass);
        $mu03 = $moments['m03'] - (3 * $centerY * $moments['m02']) + (3 * ($centerY ** 2) * $moments['m01']) - (($centerY ** 3) * $mass);
        $mu12 = $moments['m12'] - (2 * $centerY * $moments['m11']) + (($centerY ** 2) * $moments['m10']) - ($centerX * $moments['m02']) + (2 * $centerX * $centerY * $moments['m01']) - ($centerX * ($centerY ** 2) * $mass);
        $mu21 = $moments['m21'] - (2 * $centerX * $moments['m11']) + (($centerX ** 2) * $moments['m01']) - ($centerY * $moments['m20']) + (2 * $centerX * $centerY * $moments['m10']) - ($centerY * ($centerX ** 2) * $mass);

        $normalize = fn (float $value, int $order) => $value / ($mass ** (1 + ($order / 2)));
        $n20 = $normalize($mu20, 2);
        $n02 = $normalize($mu02, 2);
        $n11 = $normalize($mu11, 2);
        $n30 = $normalize($mu30, 3);
        $n03 = $normalize($mu03, 3);
        $n12 = $normalize($mu12, 3);
        $n21 = $normalize($mu21, 3);

        $first = $n20 + $n02;
        $second = (($n20 - $n02) ** 2) + (4 * ($n11 ** 2));
        $third = (($n30 - (3 * $n12)) ** 2) + (((3 * $n21) - $n03) ** 2);
        $fourth = (($n30 + $n12) ** 2) + (($n21 + $n03) ** 2);
        $fifth = ($n30 - (3 * $n12)) * ($n30 + $n12) * ((($n30 + $n12) ** 2) - (3 * (($n21 + $n03) ** 2)))
            + (((3 * $n21) - $n03) * ($n21 + $n03) * ((3 * (($n30 + $n12) ** 2)) - (($n21 + $n03) ** 2)));
        $sixth = ($n20 - $n02) * ((($n30 + $n12) ** 2) - (($n21 + $n03) ** 2))
            + (4 * $n11 * ($n30 + $n12) * ($n21 + $n03));
        $seventh = ((3 * $n21) - $n03) * ($n30 + $n12) * ((($n30 + $n12) ** 2) - (3 * (($n21 + $n03) ** 2)))
            - (($n30 - (3 * $n12)) * ($n21 + $n03) * ((3 * (($n30 + $n12) ** 2)) - (($n21 + $n03) ** 2)));

        return array_map(function (float $value): float {
            if (abs($value) < 1.0E-30) {
                return 0.0;
            }

            $sign = $value < 0 ? -1.0 : 1.0;

            return round(-$sign * log10(abs($value)), 6);
        }, [$first, $second, $third, $fourth, $fifth, $sixth, $seventh]);
    }

    private function rgbHue(float $red, float $green, float $blue): float
    {
        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);
        $delta = $max - $min;

        if ($delta < 0.0001) {
            return 0.0;
        }

        if ($max === $red) {
            $hue = 60 * fmod((($green - $blue) / $delta), 6);
        } elseif ($max === $green) {
            $hue = 60 * ((($blue - $red) / $delta) + 2);
        } else {
            $hue = 60 * ((($red - $green) / $delta) + 4);
        }

        return $hue < 0 ? $hue + 360 : $hue;
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
