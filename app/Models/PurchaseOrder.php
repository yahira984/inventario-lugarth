<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'user_id',
        'proveedor',
        'referencia',
        'estado',
        'fecha_orden',
        'fecha_esperada',
        'notas',
        'total',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_esperada' => 'date',
        'total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
