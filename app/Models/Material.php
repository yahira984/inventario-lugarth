<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'categoria',
        'almacen',
        'numero_parte',
        'codigo_barras',
        'clave_sat',
        'clave_unidad',
        'unidad',
        'descripcion',
        'marca',
        'proveedor',
        'proveedor_rfc',
        'stock',
        'stock_minimo',
        'stock_maximo',
        'costo_unitario',
        'moneda',
        'factura_uuid',
        'factura_folio',
        'factura_fecha',
        'xml_importado_at',
        'fotografia',
        'evidencia_foto',
    ];

    protected $casts = [
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
        'costo_unitario' => 'decimal:2',
        'factura_fecha' => 'datetime',
        'xml_importado_at' => 'datetime',
    ];

    public function requiereReposicion(): bool
    {
        return $this->stock_minimo > 0 && $this->stock <= $this->stock_minimo;
    }

    public function movimientos()
    {
        return $this->hasMany(MaterialMovimiento::class);
    }
}
