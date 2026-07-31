<?php

namespace App\Console\Commands;

use App\Models\InventorySnapshot;
use App\Models\Material;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SnapshotInventory extends Command
{
    protected $signature = 'inventario:captura-diaria
        {--date= : Fecha YYYY-MM-DD; por defecto hoy}
        {--previous-day : Captura el cierre confirmado del dia anterior}';

    protected $description = 'Guarda el stock y valor diario de cada material para consultas historicas.';

    public function handle(): int
    {
        $defaultDate = $this->option('previous-day') ? yesterday() : today();
        $date = Carbon::parse($this->option('date') ?: $defaultDate)
            ->startOfDay();
        $count = 0;

        Material::query()
            ->where('es_plantilla_equipo', false)
            ->orderBy('id')
            ->chunkById(250, function ($materials) use ($date, &$count): void {
                foreach ($materials as $material) {
                    InventorySnapshot::updateOrCreate(
                        [
                            'snapshot_date' => $date,
                            'material_id' => $material->id,
                        ],
                        [
                            'stock' => (int) $material->stock,
                            'costo_unitario' => (float) $material->costo_unitario,
                            'valor_total' => round(
                                (int) $material->stock * (float) $material->costo_unitario,
                                4
                            ),
                            'almacen' => $material->almacen,
                            'categoria' => $material->categoria,
                            'proveedor' => $material->proveedor,
                            'origen' => 'captura_diaria',
                            'exactitud' => 'confirmada',
                        ]
                    );
                    $count++;
                }
            });

        $this->info("Captura {$date->toDateString()} guardada para {$count} materiales.");

        return self::SUCCESS;
    }
}
