<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Support\VisualEmbeddingService;
use App\Support\VisualImageDescriptor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReindexVisualDescriptors extends Command
{
    protected $signature = 'visual:reindex
        {--force : Recalcula incluso las huellas vigentes}
        {--ai : Genera tambien las huellas inteligentes CLIP + DINOv2}';

    protected $description = 'Prepara las huellas del identificador visual sin afectar el tiempo de respuesta web.';

    public function handle(
        VisualImageDescriptor $visualDescriptor,
        VisualEmbeddingService $visualAi
    ): int {
        $query = Material::query()
            ->where('es_plantilla_equipo', false)
            ->whereNotNull('fotografia')
            ->where('fotografia', '<>', '');

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No hay materiales con fotografia para indexar.');

            return self::SUCCESS;
        }

        $processed = 0;
        $comparable = 0;
        $missing = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById(100, function ($materials) use (
            $visualDescriptor,
            &$processed,
            &$comparable,
            &$missing,
            $bar
        ): void {
            foreach ($materials as $material) {
                $descriptor = $visualDescriptor->forMaterial($material, (bool) $this->option('force'));
                $processed++;

                if ($descriptor === []) {
                    $missing++;
                } elseif (($descriptor['calidad'] ?? null) === 'ok') {
                    $comparable++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Indice visual listo: {$processed} materiales procesados.");
        $this->line("Comparables: {$comparable}. Archivos faltantes o invalidos: {$missing}.");

        if ($this->option('ai')) {
            return $this->indexArtificialIntelligence($query, $visualDescriptor, $visualAi);
        }

        return self::SUCCESS;
    }

    private function indexArtificialIntelligence(
        $query,
        VisualImageDescriptor $visualDescriptor,
        VisualEmbeddingService $visualAi
    ): int {
        if (! $visualAi->isReady()) {
            $this->components->error(
                'CLIP + DINOv2 no estan instalados. Ejecuta php artisan visual:ai-setup.'
            );

            return self::FAILURE;
        }

        $materials = (clone $query)->get()->filter(function (Material $material): bool {
            if ($this->option('force')) {
                return true;
            }

            return data_get($material->visual_descriptor, 'ai.version') !== VisualEmbeddingService::VERSION;
        })->values();

        if ($materials->isEmpty()) {
            $this->components->info('Las huellas inteligentes ya estaban actualizadas.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->components->info("Generando huellas CLIP + DINOv2 para {$materials->count()} materiales.");
        $bar = $this->output->createProgressBar($materials->count());
        $bar->start();
        $indexed = 0;
        $failed = 0;

        foreach ($materials->chunk(120) as $chunk) {
            $manifest = $chunk->map(function (Material $material): array {
                $relativePath = ltrim((string) $material->fotografia, '/\\');

                return [
                    'id' => $material->id,
                    'path' => Storage::disk('public')->path($relativePath),
                    'signature' => $material->visual_descriptor_signature,
                ];
            })->values()->all();

            try {
                $results = collect($visualAi->batch($manifest))->keyBy('id');
            } catch (Throwable $exception) {
                $bar->finish();
                $this->newLine(2);
                $this->components->error('La IA visual se detuvo: '.$exception->getMessage());

                return self::FAILURE;
            }

            foreach ($chunk as $material) {
                $result = $results->get($material->id);

                if (! is_array($result) || isset($result['error']) || empty($result['embedding'])) {
                    $failed++;
                    $bar->advance();

                    continue;
                }

                $descriptor = $visualDescriptor->forMaterial($material);
                $descriptor['ai'] = [
                    'version' => $result['version'],
                    'dimensions' => $result['dimensions'],
                    'embedding' => $result['embedding'],
                    'detail_dimensions' => $result['detail_dimensions'],
                    'detail_embedding' => $result['detail_embedding'],
                ];

                $material->forceFill(['visual_descriptor' => $descriptor])->saveQuietly();
                $indexed++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->components->info("IA visual lista: {$indexed} materiales indexados.");

        if ($failed > 0) {
            $this->components->warn("No se pudieron indexar {$failed} imagenes.");
        }

        return self::SUCCESS;
    }
}
