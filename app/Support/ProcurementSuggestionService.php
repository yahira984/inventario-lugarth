<?php

namespace App\Support;

use App\Models\Material;
use App\Models\MaterialMovimiento;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ProcurementSuggestionService
{
    public const CONSUMPTION_WINDOW_DAYS = 90;

    public const DEFAULT_COVERAGE_DAYS = 30;

    /**
     * @return array{
     *     material: Material,
     *     consumo_periodo: int,
     *     consumo_diario: float,
     *     dias_cobertura: int|null,
     *     objetivo: int,
     *     cantidad_sugerida: int,
     *     razon: string,
     *     nivel: string
     * }
     */
    public function forMaterial(Material $material, int $coverageDays = self::DEFAULT_COVERAGE_DAYS): array
    {
        $consumption = (int) MaterialMovimiento::query()
            ->where('material_id', $material->id)
            ->where('tipo', 'salida')
            ->where('created_at', '>=', now()->subDays(self::CONSUMPTION_WINDOW_DAYS))
            ->sum('cantidad');
        $daily = $consumption / self::CONSUMPTION_WINDOW_DAYS;
        $forecast = (int) ceil($daily * max(1, $coverageDays));
        $minimum = max(0, (int) $material->stock_minimo);
        $maximum = max(0, (int) $material->stock_maximo);
        $target = $maximum > 0
            ? $maximum
            : max($minimum * 2, $minimum + $forecast, $forecast);
        $suggested = max(0, $target - (int) $material->stock);
        $coverage = $daily > 0 ? (int) floor((int) $material->stock / $daily) : null;

        if ((int) $material->stock <= 0) {
            $reason = 'Sin existencias';
            $level = 'danger';
        } elseif ($minimum > 0 && (int) $material->stock <= $minimum) {
            $reason = 'Stock en minimo o por debajo';
            $level = 'danger';
        } elseif ($coverage !== null && $coverage <= $coverageDays) {
            $reason = "Cobertura estimada de {$coverage} dias";
            $level = 'warning';
        } else {
            $reason = 'Reposicion preventiva por consumo';
            $level = 'info';
        }

        return [
            'material' => $material,
            'consumo_periodo' => $consumption,
            'consumo_diario' => round($daily, 4),
            'dias_cobertura' => $coverage,
            'objetivo' => $target,
            'cantidad_sugerida' => $suggested,
            'razon' => $reason,
            'nivel' => $level,
        ];
    }

    public function suggestions(int $limit = 100): Collection
    {
        return Material::query()
            ->where('es_plantilla_equipo', false)
            ->get()
            ->map(fn (Material $material): array => $this->forMaterial($material))
            ->filter(fn (array $suggestion): bool => $suggestion['cantidad_sugerida'] > 0
                && (
                    $suggestion['material']->stock <= $suggestion['material']->stock_minimo
                    || $suggestion['consumo_periodo'] > 0
                ))
            ->sortBy([
                fn (array $item): int => $item['nivel'] === 'danger' ? 0 : 1,
                fn (array $item): int => -$item['cantidad_sugerida'],
            ])
            ->take($limit)
            ->values();
    }

    public function inactive(int $days): Collection
    {
        $threshold = now()->subDays(max(1, $days));

        return Material::query()
            ->where('es_plantilla_equipo', false)
            ->where('stock', '>', 0)
            ->whereDoesntHave('movimientos', function ($query) use ($threshold): void {
                $query->where('created_at', '>=', $threshold);
            })
            ->withMax('movimientos', 'created_at')
            ->orderByDesc('stock')
            ->get();
    }
}
