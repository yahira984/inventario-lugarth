<?php

namespace App\Support;

use App\Models\Material;
use App\Models\MaterialSupplierPrice;
use Illuminate\Support\Str;

class SupplierPriceService
{
    public const SIGNIFICANT_INCREASE_PERCENT = 15.0;

    public function __construct(private readonly InventoryNotifier $notifier) {}

    public function record(
        Material $material,
        ?string $supplier,
        float $unitPrice,
        string $currency = 'MXN',
        string $origin = 'manual',
        ?string $reference = null,
        ?string $supplierRfc = null,
        mixed $recordedAt = null,
    ): ?MaterialSupplierPrice {
        $supplier = trim((string) $supplier);

        if ($supplier === '' || $unitPrice <= 0) {
            return null;
        }

        if ($reference && MaterialSupplierPrice::query()
            ->where('material_id', $material->id)
            ->where('proveedor', $supplier)
            ->where('origen', $origin)
            ->where('referencia', $reference)
            ->exists()) {
            return null;
        }

        $previous = MaterialSupplierPrice::query()
            ->where('material_id', $material->id)
            ->whereRaw('LOWER(proveedor) = ?', [mb_strtolower($supplier)])
            ->latest('registrado_en')
            ->latest('id')
            ->first();
        $previousPrice = (float) ($previous?->precio_unitario ?? 0);
        $variation = $previousPrice > 0
            ? (($unitPrice - $previousPrice) / $previousPrice) * 100
            : null;
        $significant = $variation !== null
            && $variation >= self::SIGNIFICANT_INCREASE_PERCENT;

        $price = MaterialSupplierPrice::create([
            'material_id' => $material->id,
            'proveedor' => $supplier,
            'proveedor_rfc' => trim((string) $supplierRfc) ?: null,
            'precio_unitario' => round($unitPrice, 4),
            'precio_anterior' => $previousPrice > 0 ? round($previousPrice, 4) : null,
            'variacion_porcentaje' => $variation !== null ? round($variation, 3) : null,
            'aumento_significativo' => $significant,
            'moneda' => trim($currency) ?: 'MXN',
            'origen' => $origin,
            'referencia' => $reference,
            'registrado_en' => $recordedAt ?: now(),
        ]);

        if ($significant) {
            $percent = number_format((float) $variation, 1);
            $this->notifier->admins(
                'Aumento importante de precio',
                "{$supplier} aumento {$percent}% el precio de {$material->descripcion}.",
                route('admin.proveedores.comparador', ['material_id' => $material->id]),
                'amber',
                [
                    'material_id' => $material->id,
                    'supplier_price_id' => $price->id,
                ]
            );
        }

        return $price;
    }

    public function productKey(Material $material): string
    {
        $part = trim((string) $material->numero_parte);

        if ($part !== '' && strtoupper($part) !== 'N/A') {
            return 'part:'.Str::upper($part);
        }

        return 'name:'.(string) Str::of($material->descripcion)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', ' ')
            ->squish();
    }
}
