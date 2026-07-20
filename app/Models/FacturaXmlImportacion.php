<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaXmlImportacion extends Model
{
    protected $table = 'factura_xml_importaciones';

    protected $guarded = [];

    protected $casts = [
        'fecha' => 'datetime',
        'tipo_cambio' => 'decimal:6',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos_trasladados' => 'decimal:2',
        'impuestos_retenidos' => 'decimal:2',
        'total' => 'decimal:2',
        'conceptos_count' => 'integer',
        'datos' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
