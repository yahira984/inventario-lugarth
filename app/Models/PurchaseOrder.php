<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'user_id',
        'authorized_by',
        'received_by',
        'invoiced_by',
        'proveedor',
        'referencia',
        'estado',
        'fecha_orden',
        'fecha_esperada',
        'authorized_at',
        'ordered_at',
        'received_at',
        'invoiced_at',
        'invoice_uuid',
        'invoice_folio',
        'moneda',
        'notas',
        'total',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_esperada' => 'date',
        'authorized_at' => 'datetime',
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
        'invoiced_at' => 'datetime',
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

    public function request()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function authorizer()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function invoicer()
    {
        return $this->belongsTo(User::class, 'invoiced_by');
    }
}
