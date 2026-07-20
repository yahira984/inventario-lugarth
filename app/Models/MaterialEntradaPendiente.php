<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialEntradaPendiente extends Model
{
    protected $table = 'material_entradas_pendientes';

    protected $fillable = [
        'material_id',
        'es_material_nuevo',
        'datos_material',
        'user_id',
        'approved_by',
        'rejected_by',
        'cantidad',
        'estado',
        'codigo_barras',
        'referencia',
        'motivo',
        'evidencia_foto',
        'fotografia',
        'proveedor',
        'costo_unitario',
        'approved_at',
        'rejected_at',
        'comentario_admin',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'es_material_nuevo' => 'boolean',
        'datos_material' => 'array',
        'costo_unitario' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
