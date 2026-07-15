<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    
   protected $fillable = [
    'categoria',
    'numero_parte',
     'codigo_barras',
      'descripcion',
       'marca',
        'proveedor',
         'stock',
          'fotografia',
           'evidencia_foto'
           ];

    protected $casts = [
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'costo_unitario' => 'decimal:2',
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
