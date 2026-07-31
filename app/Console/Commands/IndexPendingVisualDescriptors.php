<?php

namespace App\Console\Commands;

use App\Support\VisualIndexMaintenanceService;
use Illuminate\Console\Command;

class IndexPendingVisualDescriptors extends Command
{
    protected $signature = 'visual:reindex-pendientes {--limit=15 : Maximo de fotografias por ejecucion}';

    protected $description = 'Actualiza en segundo plano solo las fotografias que faltan en el indice visual.';

    public function handle(VisualIndexMaintenanceService $maintenance): int
    {
        $summary = $maintenance->process((int) $this->option('limit'));

        $this->info("Indice incremental: {$summary['indexed']} actualizadas, {$summary['failed']} con error, {$summary['pending']} pendientes.");

        return self::SUCCESS;
    }
}
