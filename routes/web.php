<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaterialController;

// 1. Ruta personalizada para el escáner (Debe ir ANTES del resource)
Route::get('materiales/buscar-por-codigo', [MaterialController::class, 'buscarPorCodigo'])->name('materiales.buscarPorCodigo');

// 2. Resource automático (Crea index, create, store, show, edit, update y destroy)
Route::resource('materiales', MaterialController::class)
    ->parameters([
        'materiales' => 'material',
    ]);

// 3. Ruta de la página de inicio 
Route::get('/', function () {
    return view('welcome');
});