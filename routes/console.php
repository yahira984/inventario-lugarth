<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\IndexPendingVisualDescriptors;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('inventario:alertas-stock')
    ->dailyAt('08:00')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping();

Schedule::command('inventario:captura-diaria --previous-day')
    ->dailyAt('00:10')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping();

Schedule::call(fn () => IndexPendingVisualDescriptors::dispatch(15))
    ->name('inventario:indice-visual-pendiente')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('chat:limpiar')
    ->dailyAt('03:30')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping();
