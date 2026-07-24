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

    /** @var array<int, string>|null */
    private ?array $availableNodeBinaries = null;

    /** @var array<string, string> */
    private array $nodeProbeErrors = [];

    public function __construct(private readonly VisualImageDescriptor $visualDescriptor) {}

    public function isReady(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        return is_file(base_path('node_modules/@huggingface/transformers/package.json'))
            && is_file(storage_path('app/visual-ai/models/Xenova/dinov2-small/onnx/model_quantized.onnx'))
            && is_file(storage_path('app/visual-ai/models/Xenova/clip-vit-base-patch32/onnx/vision_model_quantized.onnx'))
            && $this->nodeBinaries() !== [];
    }

    /**
     * @return array{
     *     ready: bool,
     *     transformers: bool,
     *     semantic_model: bool,
     *     detail_model: bool,
     *     node_candidates: array<int, string>,
     *     node_binaries: array<int, string>,
     *     node_errors: array<string, string>
     * }
     */
    public function diagnostics(): array
    {
        $nodeBinaries = $this->nodeBinaries();

        return [
            'ready' => is_file(base_path('node_modules/@huggingface/transformers/package.json'))
                && is_file(storage_path('app/visual-ai/models/Xenova/dinov2-small/onnx/model_quantized.onnx'))
                && is_file(storage_path('app/visual-ai/models/Xenova/clip-vit-base-patch32/onnx/vision_model_quantized.onnx'))
                && $nodeBinaries !== [],
            'transformers' => is_file(base_path('node_modules/@huggingface/transformers/package.json')),
            'semantic_model' => is_file(storage_path('app/visual-ai/models/Xenova/clip-vit-base-patch32/onnx/vision_model_quantized.onnx')),
            'detail_model' => is_file(storage_path('app/visual-ai/models/Xenova/dinov2-small/onnx/model_quantized.onnx')),
            'node_candidates' => $this->nodeCandidates(),
            'node_binaries' => $nodeBinaries,
            'node_errors' => $this->nodeProbeErrors,
        ];
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
            throw new RuntimeException($this->readinessMessage());
        }

        [$safePath, $temporary] = $this->prepareSafeImage($path);

        try {
            return $this->run(['embed', $safePath], 120);
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
            throw new RuntimeException($this->readinessMessage());
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
        $errors = [];

        foreach ($this->nodeBinaries() as $binary) {
            try {
                $result = Process::path(base_path())
                    ->env($environment)
                    ->timeout($timeout)
                    ->run([
                        $binary,
                        base_path('scripts/visual-ai.mjs'),
                        ...$arguments,
                    ]);
            } catch (Throwable $exception) {
                $errors[] = $this->nodeLabel($binary).': '.$exception->getMessage();

                continue;
            }

            if (! $result->successful()) {
                $errors[] = $this->nodeLabel($binary).': '.(
                    trim($result->errorOutput()) ?: 'El proceso terminó sin explicar el error.'
                );

                continue;
            }

            $decoded = json_decode($result->output(), true);
            if (! is_array($decoded)) {
                $errors[] = $this->nodeLabel($binary).': respuesta JSON inválida.';

                continue;
            }

            return $decoded;
        }

        throw new RuntimeException(
            $errors === []
                ? $this->readinessMessage()
                : 'No se pudo ejecutar el motor visual. '.implode(' | ', array_unique($errors))
        );
    }

    /**
     * @return array<int, string>
     */
    private function nodeBinaries(): array
    {
        if ($this->availableNodeBinaries !== null) {
            return $this->availableNodeBinaries;
        }

        $this->nodeProbeErrors = [];
        $available = [];

        foreach ($this->nodeCandidates() as $candidate) {
            if ($this->looksLikePath($candidate) && ! is_file($candidate)) {
                $this->nodeProbeErrors[$candidate] = 'El archivo no existe.';

                continue;
            }

            try {
                $probe = Process::timeout(8)->run([$candidate, '--version']);
            } catch (Throwable $exception) {
                $this->nodeProbeErrors[$candidate] = $exception->getMessage();

                continue;
            }

            if (! $probe->successful()) {
                $this->nodeProbeErrors[$candidate] = trim($probe->errorOutput())
                    ?: 'Node.js no respondió correctamente.';

                continue;
            }

            if (! preg_match('/v(\d+)/', trim($probe->output()), $matches) || (int) $matches[1] < 18) {
                $this->nodeProbeErrors[$candidate] = 'Se requiere Node.js 18 o superior.';

                continue;
            }

            $available[] = $candidate;
        }

        return $this->availableNodeBinaries = array_values(array_unique($available));
    }

    /**
     * @return array<int, string>
     */
    private function nodeCandidates(): array
    {
        $configured = trim((string) config('services.visual_ai.node', ''));
        $candidates = [$configured !== '' ? $configured : null];

        if (PHP_OS_FAMILY === 'Windows') {
            $programFiles = getenv('ProgramFiles') ?: 'C:\\Program Files';
            $programFilesX86 = getenv('ProgramFiles(x86)') ?: null;
            $localAppData = getenv('LOCALAPPDATA') ?: null;
            $appData = getenv('APPDATA') ?: null;
            $userProfile = getenv('USERPROFILE') ?: null;
            $nvmSymlink = getenv('NVM_SYMLINK') ?: null;

            array_push(
                $candidates,
                $nvmSymlink ? rtrim($nvmSymlink, '\\/').'\\node.exe' : null,
                $programFiles.'\\nodejs\\node.exe',
                $programFilesX86 ? $programFilesX86.'\\nodejs\\node.exe' : null,
                $localAppData ? $localAppData.'\\Programs\\nodejs\\node.exe' : null,
                $localAppData ? $localAppData.'\\nvm\\node.exe' : null,
                $appData ? $appData.'\\nvm\\current\\node.exe' : null,
                $userProfile ? $userProfile.'\\.nvm\\current\\node.exe' : null,
                $userProfile ? $userProfile.'\\.config\\herd\\bin\\nvm\\current\\node.exe' : null,
            );
        }

        $candidates[] = 'node';

        return array_values(array_unique(array_filter($candidates)));
    }

    private function looksLikePath(string $candidate): bool
    {
        return str_contains($candidate, '\\')
            || str_contains($candidate, '/')
            || preg_match('/^[A-Za-z]:/', $candidate) === 1;
    }

    private function nodeLabel(string $binary): string
    {
        return is_file($binary) ? basename($binary).' ('.$binary.')' : $binary;
    }

    private function readinessMessage(): string
    {
        $diagnostics = $this->diagnostics();

        if (! $diagnostics['transformers']) {
            return 'Falta instalar el motor de JavaScript. Ejecuta npm ci.';
        }

        if (! $diagnostics['semantic_model'] || ! $diagnostics['detail_model']) {
            return 'Faltan los modelos visuales. Ejecuta php artisan visual:ai-setup --no-index.';
        }

        if ($diagnostics['node_binaries'] === []) {
            return 'Laravel/Herd no puede ejecutar Node.js 18 o superior. Ejecuta php artisan visual:ai-diagnose para ver la ruta que falla.';
        }

        return 'La IA visual no está preparada. Ejecuta php artisan visual:ai-diagnose.';
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
