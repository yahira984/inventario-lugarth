<?php

namespace App\Providers;

use App\Models\Material;
use App\Observers\MaterialObserver;
use App\Support\VisualImageDescriptor;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(VisualImageDescriptor::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Material::observe(MaterialObserver::class);

        Gate::define('mover-stock', fn ($user) => $user->puedeMoverStock());
        Gate::define('administrar-catalogo', fn ($user) => $user->puedeAdministrarCatalogo());
    }
}
