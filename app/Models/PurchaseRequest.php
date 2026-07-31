<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'requested_by',
        'reviewed_by',
        'estado',
        'prioridad',
        'origen',
        'motivo',
        'comentario_revision',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function order()
    {
        return $this->hasOne(PurchaseOrder::class);
    }
}
