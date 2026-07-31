<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialPhoto extends Model
{
    protected $fillable = [
        'material_id',
        'path',
        'angulo',
        'es_principal',
        'visual_descriptor',
        'visual_descriptor_signature',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'visual_descriptor' => 'array',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
