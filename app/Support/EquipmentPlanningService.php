<?php

namespace App\Support;

use App\Models\EquipmentPackage;
use Illuminate\Support\Collection;

class EquipmentPlanningService
{
    /**
     * @return array{
     *     fabricables: int,
     *     costo_unitario: float,
     *     valor_stock_fabricable: float,
     *     listo: bool,
     *     sin_vincular: Collection,
     *     limitantes: Collection,
     *     requisitos: Collection
     * }
     */
    public function analyze(EquipmentPackage $package): array
    {
        $package->loadMissing('items.material.latestInvoicePrice');

        $unlinked = $package->items
            ->whereNull('material_id')
            ->pluck('descripcion')
            ->filter()
            ->values();

        $requirements = $package->items
            ->whereNotNull('material_id')
            ->groupBy('material_id')
            ->map(function (Collection $items): array {
                $material = $items->first()->material;
                $required = (float) $items->sum('cantidad_por_paquete');
                $stock = (int) ($material?->stock ?? 0);
                $possible = $required > 0 ? (int) floor($stock / $required) : 0;
                $invoicePrice = $material?->latestInvoicePrice;
                $unitCost = (float) (
                    $invoicePrice?->precio_unitario
                    ?? $material?->costo_unitario
                    ?? 0
                );
                $costSource = $invoicePrice
                    ? 'Ultima factura '.$invoicePrice->registrado_en?->format('d/m/Y')
                    : (filled($material?->factura_uuid)
                        ? 'Ultima factura registrada'
                        : 'Costo actual sin factura historica');

                return [
                    'material' => $material,
                    'descripcion' => $material?->descripcion ?? $items->first()->descripcion,
                    'cantidad_por_equipo' => $required,
                    'stock' => $stock,
                    'fabricables' => max(0, $possible),
                    'costo_unitario' => $unitCost,
                    'costo_por_equipo' => round($required * $unitCost, 2),
                    'origen_costo' => $costSource,
                    'costo_con_factura' => $invoicePrice !== null || filled($material?->factura_uuid),
                ];
            })
            ->values();

        $hasCompleteRecipe = $package->items->isNotEmpty()
            && $unlinked->isEmpty()
            && $requirements->isNotEmpty();
        $manufacturable = $hasCompleteRecipe
            ? (int) $requirements->min('fabricables')
            : 0;
        $limiting = $hasCompleteRecipe
            ? $requirements
                ->where('fabricables', $manufacturable)
                ->sortBy('descripcion')
                ->values()
            : collect();
        $unitCost = round((float) $requirements->sum('costo_por_equipo'), 2);
        $missingInvoiceCosts = $requirements
            ->where('costo_con_factura', false)
            ->pluck('descripcion')
            ->values();

        return [
            'fabricables' => $manufacturable,
            'costo_unitario' => $unitCost,
            'valor_stock_fabricable' => round($manufacturable * $unitCost, 2),
            'listo' => $hasCompleteRecipe,
            'sin_vincular' => $unlinked,
            'limitantes' => $limiting,
            'requisitos' => $requirements,
            'costos_sin_factura' => $missingInvoiceCosts,
        ];
    }
}
