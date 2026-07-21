<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Support\VisualImageDescriptor;
use Illuminate\Console\Command;

class ReindexVisualDescriptors extends Command
{
    protected $signature = 'visual:reindex {--force : Recalcula incluso las huellas vigentes}';

    protected $description = 'Prepara las huellas del identificador visual sin afectar el tiempo de respuesta web.';

    public function handle(VisualImageDescriptor $visualDescriptor): int
    {
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

        return self::SUCCESS;
    }
}
