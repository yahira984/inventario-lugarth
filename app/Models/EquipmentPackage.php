<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentPackage extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(EquipmentPackageItem::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(EquipmentPackageWithdrawal::class);
    }

    public function evaluarDisponibilidad(int $cantidadPaquetes = 1): array
    {
        $this->loadMissing('items.material');
        $cantidadPaquetes = max(1, $cantidadPaquetes);
        $sinVincular = $this->items
            ->whereNull('material_id')
            ->pluck('descripcion')
            ->filter()
            ->values();

        $faltantes = $this->items
            ->whereNotNull('material_id')
            ->groupBy('material_id')
            ->map(function ($items) use ($cantidadPaquetes): ?array {
                $material = $items->first()->material;
                $requerido = (int) ceil(
                    ((float) $items->sum('cantidad_por_paquete')) * $cantidadPaquetes
                );
                $disponible = (int) ($material?->stock ?? 0);

                if ($material && $disponible >= $requerido) {
                    return null;
                }

                return [
                    'descripcion' => $material?->descripcion ?? $items->first()->descripcion,
                    'disponible' => $disponible,
                    'requerido' => $requerido,
                    'faltan' => max($requerido - $disponible, 0),
                ];
            })
            ->filter()
            ->values();

        return [
            'sin_piezas' => $this->items->isEmpty(),
            'sin_vincular' => $sinVincular,
            'faltantes' => $faltantes,
            'listo' => $this->items->isNotEmpty()
                && $sinVincular->isEmpty()
                && $faltantes->isEmpty(),
        ];
    }
}
