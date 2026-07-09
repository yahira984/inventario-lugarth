<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'numero_parte', 
        'descripcion', 
        'marca', 
        'proveedor', 
        'categoria', 
        'stock', 
        'fotografia'
    ];
}