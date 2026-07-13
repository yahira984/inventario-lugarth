<?php

use App\Http\Controllers\FacturaXmlController;
use App\Http\Controllers\IdentificadorVisualController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('materiales.index');
    })->name('dashboard');

    Route::get('materiales/buscar-por-codigo', [MaterialController::class, 'buscarPorCodigo'])
        ->name('materiales.buscarPorCodigo');

    Route::get('materiales/importar-xml', [FacturaXmlController::class, 'create'])
        ->name('materiales.xml.create');
    Route::post('materiales/importar-xml/preview', [FacturaXmlController::class, 'preview'])
        ->name('materiales.xml.preview');
    Route::post('materiales/importar-xml/guardar', [FacturaXmlController::class, 'store'])
        ->name('materiales.xml.store');

    Route::get('materiales/identificador-visual', [IdentificadorVisualController::class, 'create'])
        ->name('materiales.visual.create');
    Route::post('materiales/identificador-visual/buscar', [IdentificadorVisualController::class, 'search'])
        ->name('materiales.visual.search');

    Route::resource('materiales', MaterialController::class)
        ->parameters(['materiales' => 'material']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
