<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentPackageWithdrawal extends Model
{
    protected $fillable = [
        'equipment_package_id',
        'user_id',
        'cantidad_paquetes',
        'tipo',
        'referencia',
        'notas',
    ];

    protected $casts = [
        'cantidad_paquetes' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(EquipmentPackage::class, 'equipment_package_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
