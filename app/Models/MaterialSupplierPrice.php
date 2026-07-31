<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialSupplierPrice extends Model
{
    protected $fillable = [
        'material_id',
        'proveedor',
        'proveedor_rfc',
        'precio_unitario',
        'precio_anterior',
        'variacion_porcentaje',
        'aumento_significativo',
        'moneda',
        'origen',
        'referencia',
        'registrado_en',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:4',
        'precio_anterior' => 'decimal:4',
        'variacion_porcentaje' => 'decimal:3',
        'aumento_significativo' => 'boolean',
        'registrado_en' => 'datetime',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
