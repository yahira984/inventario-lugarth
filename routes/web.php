<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MaterialController;

/*
|--------------------------------------------------------------------------
| 1. RUTAS PÚBLICAS (Antes de iniciar sesión)
|--------------------------------------------------------------------------
*/

// Al entrar a la raíz, te avienta directo a tu login colorido
Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| 2. RUTAS PROTEGIDAS (Solo para usuarios logueados)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // Panel de Control Principal (Dashboard)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // --- TUS RUTAS DE MATERIALES (¡Ya recuperadas!) ---
    Route::get('materiales/buscar-por-codigo', [MaterialController::class, 'buscarPorCodigo'])->name('materiales.buscarPorCodigo');
    Route::resource('materiales', MaterialController::class);

    // Gestión del Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| 3. RUTAS DE AUTENTICACIÓN (Breeze / Login / Registro)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
