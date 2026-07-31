<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventorySnapshot extends Model
{
    protected $fillable = [
        'snapshot_date',
        'material_id',
        'stock',
        'costo_unitario',
        'valor_total',
        'almacen',
        'categoria',
        'proveedor',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'stock' => 'integer',
        'costo_unitario' => 'decimal:4',
        'valor_total' => 'decimal:4',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
