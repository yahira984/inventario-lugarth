<?php

namespace App\Support;

use App\Models\Material;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class VisualEmbeddingService
{
    public const VERSION = 'clip-vit-b32-dinov2s-q8-v1';

    public function __construct(private readonly VisualImageDescriptor $visualDescriptor) {}

    public function isReady(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        return is_file(base_path('node_modules/@huggingface/transformers/package.json'))
            && is_file(storage_path('app/visual-ai/models/Xenova/dinov2-small/onnx/model_quantized.onnx'))
            && is_file(storage_path('app/visual-ai/models/Xenova/clip-vit-base-patch32/onnx/vision_model_quantized.onnx'));
    }

    /**
     * @return array{
     *     version: string,
     *     dimensions: int,
     *     embedding: array<int, float>,
     *     detail_dimensions: int,
     *     detail_embedding: array<int, float>
     * }
     */
    public function fromPath(string $path): array
    {
        if (! $this->isReady()) {
            throw new RuntimeException(
                'La IA visual aun no esta preparada. Ejecuta php artisan visual:ai-setup.'
            );
        }

        [$safePath, $temporary] = $this->prepareSafeImage($path);

        try {
            return $this->run(['embed', $safePath], 30);
        } finally {
            if ($temporary) {
                @unlink($safePath);
            }
        }
    }

    public function indexMaterial(Material $material): bool
    {
        $relativePath = trim((string) $material->fotografia);

        if ($relativePath === '') {
            return false;
        }

        $absolutePath = Storage::disk('public')->path(ltrim($relativePath, '/\\'));
        if (! is_file($absolutePath)) {
            return false;
        }

        try {
            $embedding = $this->fromPath($absolutePath);
        } catch (Throwable) {
            return false;
        }

        $descriptor = $this->visualDescriptor->forMaterial($material);
        $descriptor['ai'] = $embedding;

        $material->forceFill(['visual_descriptor' => $descriptor])->saveQuietly();

        return true;
    }

    /**
     * @param  array<int, array{id: int, path: string, signature: string|null}>  $manifest
     * @return array<int, array<string, mixed>>
     */
    public function batch(array $manifest): array
    {
        if (! $this->isReady()) {
            throw new RuntimeException(
                'La IA visual aun no esta preparada. Ejecuta php artisan visual:ai-setup.'
            );
        }

        $directory = storage_path('app/visual-ai/tmp');
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('No se pudo crear el directorio temporal de la IA visual.');
        }

        $manifestPath = $directory.'/manifest-'.bin2hex(random_bytes(8)).'.json';
        file_put_contents($manifestPath, json_encode($manifest, JSON_THROW_ON_ERROR));

        try {
            return $this->run(['batch', $manifestPath], max(120, count($manifest) * 3));
        } finally {
            @unlink($manifestPath);
        }
    }

    public function setup(): void
    {
        $this->run(['setup'], 180, true);
    }

    /**
     * @param  array<int, float|int>  $first
     * @param  array<int, float|int>  $second
     */
    public function cosineSimilarity(array $first, array $second): float
    {
        if ($first === [] || count($first) !== count($second)) {
            return 0.0;
        }

        $dot = 0.0;
        $firstNorm = 0.0;
        $secondNorm = 0.0;

        foreach ($first as $index => $value) {
            $firstValue = (float) $value;
            $secondValue = (float) $second[$index];
            $dot += $firstValue * $secondValue;
            $firstNorm += $firstValue * $firstValue;
            $secondNorm += $secondValue * $secondValue;
        }

        if ($firstNorm <= 0 || $secondNorm <= 0) {
            return 0.0;
        }

        return max(-1.0, min(1.0, $dot / (sqrt($firstNorm) * sqrt($secondNorm))));
    }

    /**
     * @param  array<int, string>  $arguments
     * @return array<string, mixed>|array<int, array<string, mixed>>
     */
    private function run(array $arguments, int $timeout, bool $allowDownload = false): array
    {
        $environment = [
            'VISUAL_AI_CACHE' => storage_path('app/visual-ai/models'),
            'VISUAL_AI_ALLOW_DOWNLOAD' => $allowDownload ? '1' : '0',
        ];

        $result = Process::path(base_path())
            ->env($environment)
            ->timeout($timeout)
            ->run([
                (string) config('services.visual_ai.node', 'node'),
                base_path('scripts/visual-ai.mjs'),
                ...$arguments,
            ]);

        if (! $result->successful()) {
            throw new RuntimeException(
                trim($result->errorOutput()) ?: 'No se pudo ejecutar el motor de IA visual.'
            );
        }

        $decoded = json_decode($result->output(), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('El motor de IA visual devolvio una respuesta invalida.');
        }

        return $decoded;
    }

    /**
     * Re-encodes user images before native AI libraries read them.
     *
     * @return array{0: string, 1: bool}
     */
    private function prepareSafeImage(string $path): array
    {
        if (! function_exists('imagecreatefromstring')) {
            return [$path, false];
        }

        $contents = @file_get_contents($path);
        $source = $contents === false ? false : @imagecreatefromstring($contents);
        unset($contents);

        if (! $source) {
            return [$path, false];
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, 1600 / max(1, $sourceWidth, $sourceHeight));
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        imagecopyresampled(
            $image,
            $source,
            0,
            0,
            0,
            0,
            $width,
            $height,
            $sourceWidth,
            $sourceHeight
        );
        imagedestroy($source);

        $directory = storage_path('app/visual-ai/tmp');
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            imagedestroy($image);

            return [$path, false];
        }

        $temporaryPath = $directory.'/query-'.bin2hex(random_bytes(8)).'.jpg';
        $written = imagejpeg($image, $temporaryPath, 88);
        imagedestroy($image);

        return $written ? [$temporaryPath, true] : [$path, false];
    }
}
