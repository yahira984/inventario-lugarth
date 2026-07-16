<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentPackageItem extends Model
{
    protected $fillable = [
        'equipment_package_id',
        'material_id',
        'numero_parte',
        'descripcion',
        'apodo',
        'marca',
        'fotografia',
        'cantidad_por_paquete',
        'unidad',
        'notas',
    ];

    protected $casts = [
        'cantidad_por_paquete' => 'decimal:2',
    ];

    public function package()
    {
        return $this->belongsTo(EquipmentPackage::class, 'equipment_package_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
