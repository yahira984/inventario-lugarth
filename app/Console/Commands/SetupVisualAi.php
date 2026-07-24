<?php

namespace App\Console\Commands;

use App\Support\VisualEmbeddingService;
use Illuminate\Console\Command;
use Throwable;

class SetupVisualAi extends Command
{
    protected $signature = 'visual:ai-setup {--no-index : Solo descarga y verifica el modelo}';

    protected $description = 'Instala el modelo visual local gratuito y prepara las fotos del inventario.';

    public function handle(VisualEmbeddingService $visualAi): int
    {
        $this->components->info('Preparando CLIP + DINOv2 local. La primera descarga pesa aproximadamente 110 MB.');

        try {
            $visualAi->setup();
        } catch (Throwable $exception) {
            $this->components->error('No se pudo preparar la IA visual: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Modelo visual listo y guardado localmente.');

        if (! $this->option('no-index')) {
            return $this->call('visual:reindex', ['--ai' => true, '--force' => true]);
        }

        return self::SUCCESS;
    }
}
