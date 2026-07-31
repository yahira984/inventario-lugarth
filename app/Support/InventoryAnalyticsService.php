<?php

namespace App\Support;

use App\Models\InventorySnapshot;
use App\Models\Material;
use App\Models\MaterialMovimiento;
use App\Models\MaterialSupplierPrice;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class InventoryAnalyticsService
{
    public function stockAt(CarbonInterface $date): Collection
    {
        $end = $date->copy()->endOfDay();
        $futureDeltas = MaterialMovimiento::query()
            ->where('created_at', '>', $end)
            ->select('material_id')
            ->selectRaw('SUM(stock_nuevo - stock_anterior) as delta')
            ->groupBy('material_id')
            ->pluck('delta', 'material_id');
        $snapshots = InventorySnapshot::query()
            ->whereDate('snapshot_date', $end->toDateString())
            ->get()
            ->keyBy('material_id');
        $historicalCosts = MaterialSupplierPrice::query()
            ->where('registrado_en', '<=', $end)
            ->orderByDesc('registrado_en')
            ->orderByDesc('id')
            ->get()
            ->unique('material_id')
            ->keyBy('material_id');

        return Material::query()
            ->where('es_plantilla_equipo', false)
            ->orderBy('descripcion')
            ->get()
            ->map(function (Material $material) use ($futureDeltas, $snapshots, $historicalCosts, $end): Material {
                $snapshot = $snapshots->get($material->id);
                $stock = $snapshot
                    ? (int) $snapshot->stock
                    : ($material->created_at?->greaterThan($end)
                        ? 0
                        : (int) $material->stock - (int) ($futureDeltas[$material->id] ?? 0));
                $historicalPrice = $historicalCosts->get($material->id);
                $cost = $snapshot
                    ? (float) $snapshot->costo_unitario
                    : (float) ($historicalPrice?->precio_unitario ?? $material->costo_unitario);
                $material->setAttribute('stock_historico', max(0, $stock));
                $material->setAttribute('costo_historico', $cost);
                $material->setAttribute(
                    'valor_historico',
                    round(max(0, $stock) * $cost, 2)
                );
                $material->setAttribute('almacen_historico', $snapshot?->almacen ?? $material->almacen);
                $material->setAttribute('categoria_historica', $snapshot?->categoria ?? $material->categoria);
                $material->setAttribute('proveedor_historico', $snapshot?->proveedor ?? $material->proveedor);
                $material->setAttribute(
                    'origen_historico',
                    $snapshot ? 'Captura diaria' : ($historicalPrice ? 'Precio conocido en la fecha' : 'Reconstruccion estimada')
                );

                return $material;
            });
    }

    public function valueBreakdowns(?Collection $materials = null): array
    {
        if ($materials !== null) {
            return [
                'almacenes' => $this->groupHistoricalValues($materials, 'almacen_historico', 'Sin almacen'),
                'categorias' => $this->groupHistoricalValues($materials, 'categoria_historica', 'Sin categoria'),
                'proveedores' => $this->groupHistoricalValues($materials, 'proveedor_historico', 'Sin proveedor'),
            ];
        }

        $base = Material::query()->where('es_plantilla_equipo', false);

        return [
            'almacenes' => (clone $base)
                ->selectRaw("COALESCE(NULLIF(almacen, ''), 'Sin almacen') as etiqueta")
                ->selectRaw('SUM(stock * costo_unitario) as valor')
                ->selectRaw('SUM(stock) as piezas')
                ->groupBy('etiqueta')
                ->orderByDesc('valor')
                ->get(),
            'categorias' => (clone $base)
                ->selectRaw("COALESCE(NULLIF(categoria, ''), 'Sin categoria') as etiqueta")
                ->selectRaw('SUM(stock * costo_unitario) as valor')
                ->selectRaw('SUM(stock) as piezas')
                ->groupBy('etiqueta')
                ->orderByDesc('valor')
                ->get(),
            'proveedores' => (clone $base)
                ->selectRaw("COALESCE(NULLIF(proveedor, ''), 'Sin proveedor') as etiqueta")
                ->selectRaw('SUM(stock * costo_unitario) as valor')
                ->selectRaw('SUM(stock) as piezas')
                ->groupBy('etiqueta')
                ->orderByDesc('valor')
                ->get(),
        ];
    }

    private function groupHistoricalValues(Collection $materials, string $attribute, string $fallback): Collection
    {
        return $materials
            ->groupBy(fn (Material $material): string => trim((string) $material->getAttribute($attribute)) ?: $fallback)
            ->map(function (Collection $group, string $label): array {
                return [
                    'etiqueta' => $label,
                    'valor' => round((float) $group->sum('valor_historico'), 2),
                    'piezas' => (int) $group->sum('stock_historico'),
                ];
            })
            ->sortByDesc('valor')
            ->values();
    }
}
