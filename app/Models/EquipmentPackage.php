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
}
