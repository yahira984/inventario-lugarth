<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaterialController;

Route::get('materiales/buscar-por-codigo', [MaterialController::class, 'buscarPorCodigo'])->name('materiales.buscarPorCodigo');
Route::resource('materiales', MaterialController::class);
Route::get('/', function () {
    return view('welcome');
});
