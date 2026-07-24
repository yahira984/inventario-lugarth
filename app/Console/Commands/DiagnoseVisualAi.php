<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Support\VisualEmbeddingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DiagnoseVisualAi extends Command
{
    protected $signature = 'visual:ai-diagnose';

    protected $description = 'Revisa Node.js, modelos, fotografías e inferencia del identificador visual.';

    public function handle(VisualEmbeddingService $visualAi): int
    {
        $diagnostics = $visualAi->diagnostics();

        $this->components->info('Diagnóstico del identificador visual');
        $this->table(
            ['Componente', 'Estado'],
            [
                ['Node.js ejecutable desde Laravel', $diagnostics['node_binaries'] !== [] ? 'Correcto' : 'Falla'],
                ['Librería @huggingface/transformers', $diagnostics['transformers'] ? 'Correcto' : 'Falta'],
                ['Modelo CLIP', $diagnostics['semantic_model'] ? 'Correcto' : 'Falta'],
                ['Modelo DINOv2', $diagnostics['detail_model'] ? 'Correcto' : 'Falta'],
            ]
        );

        if ($diagnostics['node_binaries'] !== []) {
            $this->line('Node.js usado por Laravel: '.$diagnostics['node_binaries'][0]);
        } else {
            $this->components->error('Laravel no logró ejecutar ninguna instalación de Node.js.');

            foreach ($diagnostics['node_errors'] as $candidate => $error) {
                $this->line(" - {$candidate}: {$error}");
            }
        }

        if (! $diagnostics['transformers']) {
            $this->newLine();
            $this->components->warn('Ejecuta: npm ci');
        }

        if (! $diagnostics['semantic_model'] || ! $diagnostics['detail_model']) {
            $this->newLine();
            $this->components->warn('Ejecuta: php artisan visual:ai-setup --no-index');
        }

        if (! $diagnostics['ready']) {
            return self::FAILURE;
        }

        $photoCount = Material::query()
            ->where('es_plantilla_equipo', false)
            ->whereNotNull('fotografia')
            ->where('fotografia', '<>', '')
            ->count();
        $indexedCount = Material::query()
            ->where('es_plantilla_equipo', false)
            ->where('visual_descriptor->ai->version', VisualEmbeddingService::VERSION)
            ->count();

        $this->newLine();
        $this->line("Fotografías reales: {$photoCount}. Fotografías indexadas con IA: {$indexedCount}.");

        $material = Material::query()
            ->where('es_plantilla_equipo', false)
            ->whereNotNull('fotografia')
            ->where('fotografia', '<>', '')
            ->first();

        if (! $material) {
            $this->components->warn('No hay una fotografía de inventario disponible para probar la inferencia.');

            return self::SUCCESS;
        }

        $path = Storage::disk('public')->path(ltrim((string) $material->fotografia, '/\\'));

        if (! is_file($path)) {
            $this->components->error('La base de datos apunta a una fotografía que no existe en storage/app/public.');

            return self::FAILURE;
        }

        $startedAt = microtime(true);

        try {
            $embedding = $visualAi->fromPath($path);
        } catch (Throwable $exception) {
            $this->components->error('La inferencia falló: '.$exception->getMessage());

            return self::FAILURE;
        }

        $seconds = number_format(microtime(true) - $startedAt, 2);
        $dimensions = (int) ($embedding['dimensions'] ?? 0);
        $detailDimensions = (int) ($embedding['detail_dimensions'] ?? 0);

        if ($dimensions === 0 || $detailDimensions === 0) {
            $this->components->error('La IA respondió, pero devolvió una huella vacía.');

            return self::FAILURE;
        }

        $this->components->info(
            "Motor funcionando: CLIP {$dimensions}D + DINOv2 {$detailDimensions}D en {$seconds} segundos."
        );

        if ($indexedCount < $photoCount) {
            $this->components->warn('Faltan fotografías por indexar. Ejecuta: php artisan visual:reindex --ai');
        }

        return self::SUCCESS;
    }
}
