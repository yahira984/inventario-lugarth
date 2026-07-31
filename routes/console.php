<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('inventario:alertas-stock')
    ->dailyAt('08:00')
    ->timezone('America/Mexico_City');

Schedule::command('inventario:captura-diaria')
    ->dailyAt('23:55')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping();

Schedule::command('chat:limpiar')
    ->dailyAt('03:30')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping();
