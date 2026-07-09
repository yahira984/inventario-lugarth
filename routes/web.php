<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaterialController;

Route::resource('materiales', MaterialController::class);
Route::get('/', function () {
    return view('welcome');
});
