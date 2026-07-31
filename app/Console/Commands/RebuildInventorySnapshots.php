<?php

namespace App\Console\Commands;

use App\Models\InventorySnapshot;
use App\Support\InventoryAnalyticsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RebuildInventorySnapshots extends Command
{
    protected $signature = 'inventario:reconstruir-capturas
        {--from= : Fecha inicial YYYY-MM-DD}
        {--to= : Fecha final YYYY-MM-DD}
        {--overwrite : Reemplaza capturas existentes}';

    protected $description = 'Reconstruye capturas historicas desde los movimientos confirmados, sin inventar existencias.';

    public function handle(InventoryAnalyticsService $analytics): int
    {
        $from = Carbon::parse($this->option('from') ?: now()->subDays(30)->toDateString())->startOfDay();
        $to = Carbon::parse($this->option('to') ?: yesterday()->toDateString())->startOfDay();

        if ($from->greaterThan($to)) {
            $this->components->error('La fecha inicial debe ser anterior o igual a la fecha final.');

            return self::FAILURE;
        }

        if ($from->diffInDays($to) > 730) {
            $this->components->error('Reconstruye como maximo 731 dias por ejecucion para no saturar la base.');

            return self::FAILURE;
        }

        $saved = 0;
        for ($date = $from->copy(); $date->lessThanOrEqualTo($to); $date->addDay()) {
            $materials = $analytics->stockAt($date);

            foreach ($materials as $material) {
                $existing = InventorySnapshot::query()
                    ->whereDate('snapshot_date', $date->toDateString())
                    ->where('material_id', $material->id)
                    ->first();

                if ($existing && ! $this->option('overwrite')) {
                    continue;
                }

                InventorySnapshot::updateOrCreate(
                    ['snapshot_date' => $date->toDateString(), 'material_id' => $material->id],
                    [
                        'stock' => (int) $material->stock_historico,
                        'costo_unitario' => (float) $material->costo_historico,
                        'valor_total' => (float) $material->valor_historico,
                        'almacen' => $material->almacen_historico,
                        'categoria' => $material->categoria_historica,
                        'proveedor' => $material->proveedor_historico,
                        'origen' => 'reconstruccion_movimientos',
                        'exactitud' => 'movimientos_registrados',
                    ]
                );
                $saved++;
            }
        }

        $this->info("Reconstruccion terminada: {$saved} capturas historicas guardadas.");

        return self::SUCCESS;
    }
}
