<?php

namespace App\Support;

use App\Models\Material;
use App\Models\MaterialPhoto;
use Illuminate\Support\Facades\Storage;
use Throwable;

class VisualIndexMaintenanceService
{
    public function __construct(
        private readonly VisualImageDescriptor $descriptors,
        private readonly VisualEmbeddingService $embeddings,
    ) {}

    /**
     * Revisa metadatos ligeros y solo abre los archivos que realmente necesitan indice.
     *
     * @return array{processed:int,indexed:int,failed:int,pending:int,ai:bool}
     */
    public function process(int $limit = 15): array
    {
        $limit = max(1, min(100, $limit));
        $aiReady = $this->embeddings->isReady();
        $processed = 0;
        $indexed = 0;
        $failed = 0;

        foreach ($this->pendingMaterials($aiReady)->take($limit) as $material) {
            $processed++;

            try {
                $descriptor = $this->descriptors->forMaterial($material, true);
                if ($descriptor === []) {
                    $failed++;

                    continue;
                }

                if ($aiReady) {
                    $path = Storage::disk('public')->path(ltrim((string) $material->fotografia, '/\\'));
                    $descriptor['ai'] = $this->embeddings->fromPath($path);
                    $material->forceFill(['visual_descriptor' => $descriptor])->saveQuietly();
                }

                $indexed++;
            } catch (Throwable) {
                $failed++;
            }
        }

        $remaining = max(0, $limit - $processed);
        if ($remaining > 0) {
            foreach ($this->pendingPhotos($aiReady)->take($remaining) as $photo) {
                $processed++;
                $this->embeddings->indexPhoto($photo) ? $indexed++ : $failed++;
            }
        }

        return [
            'processed' => $processed,
            'indexed' => $indexed,
            'failed' => $failed,
            'pending' => $this->pendingCount($aiReady),
            'ai' => $aiReady,
        ];
    }

    private function pendingMaterials(bool $aiReady): \Illuminate\Support\LazyCollection
    {
        return Material::query()
            ->where('es_plantilla_equipo', false)
            ->whereNotNull('fotografia')
            ->where('fotografia', '<>', '')
            ->orderBy('id')
            ->cursor()
            ->filter(fn (Material $material): bool => $this->materialNeedsIndex($material, $aiReady));
    }

    private function pendingPhotos(bool $aiReady): \Illuminate\Support\LazyCollection
    {
        return MaterialPhoto::query()
            ->orderBy('id')
            ->cursor()
            ->filter(fn (MaterialPhoto $photo): bool => $this->photoNeedsIndex($photo, $aiReady));
    }

    private function pendingCount(bool $aiReady): int
    {
        return $this->pendingMaterials($aiReady)->take(501)->count()
            + $this->pendingPhotos($aiReady)->take(501)->count();
    }

    private function materialNeedsIndex(Material $material, bool $aiReady): bool
    {
        $descriptor = $material->visual_descriptor;

        return ! is_array($descriptor)
            || $descriptor === []
            || ($aiReady && data_get($descriptor, 'ai.version') !== VisualEmbeddingService::VERSION);
    }

    private function photoNeedsIndex(MaterialPhoto $photo, bool $aiReady): bool
    {
        $descriptor = $photo->visual_descriptor;

        return ! is_array($descriptor)
            || $descriptor === []
            || ($aiReady && data_get($descriptor, 'ai.version') !== VisualEmbeddingService::VERSION);
    }
}
