<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'material_id',
        'cantidad_solicitada',
        'cantidad_autorizada',
        'consumo_diario_estimado',
        'dias_cobertura',
        'razon',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'decimal:2',
        'cantidad_autorizada' => 'decimal:2',
        'consumo_diario_estimado' => 'decimal:4',
    ];

    public function request()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
