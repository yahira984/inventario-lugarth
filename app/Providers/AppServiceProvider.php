<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Material;
use App\Models\MaterialEntradaPendiente;
use App\Models\User;
use App\Observers\MaterialObserver;
use App\Support\ChatFeatures;
use App\Support\ChatRetention;
use App\Support\VisualEmbeddingService;
use App\Support\VisualImageDescriptor;
use App\Support\VisualImagePreprocessor;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(VisualImageDescriptor::class);
        $this->app->singleton(VisualImagePreprocessor::class);
        $this->app->singleton(VisualEmbeddingService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Material::observe(MaterialObserver::class);

        Gate::define('mover-stock', fn ($user) => $user->puedeMoverStock());
        Gate::define('administrar-catalogo', fn ($user) => $user->puedeAdministrarCatalogo());

        View::composer('materiales.partials.sidebar', function ($view): void {
            $user = auth()->user();
            $isAdmin = $user?->puedeAdministrarCatalogo() ?? false;

            $stockAlerts = Material::query()
                ->where('es_plantilla_equipo', false)
                ->where('stock_minimo', '>', 0)
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->count();
            $pendingEntries = $isAdmin
                ? MaterialEntradaPendiente::query()->where('estado', 'pendiente')->count()
                : 0;
            $pendingUsers = $isAdmin
                ? User::query()->whereNull('approved_at')->count()
                : 0;

            $view->with([
                'workspaceStockAlerts' => $stockAlerts,
                'workspacePendingEntries' => $pendingEntries,
                'workspacePendingUsers' => $pendingUsers,
                'workspaceRecentActivity' => $isAdmin
                    ? AuditLog::query()->with('user:id,name')->latest()->limit(5)->get()
                    : collect(),
                'workspaceChatRetentionDays' => app(ChatRetention::class)->days(),
                'workspaceChatStickers' => app(ChatFeatures::class)->stickers(),
            ]);
        });
    }
}
