<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Material;
use App\Models\MaterialEntradaPendiente;
use App\Models\PurchaseRequest;
use App\Models\ToolLoan;
use App\Models\User;
use App\Models\UserPreference;
use App\Observers\MaterialObserver;
use App\Support\ChatFeatures;
use App\Support\ChatRetention;
use App\Support\VisualEmbeddingService;
use App\Support\VisualImageDescriptor;
use App\Support\VisualImagePreprocessor;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        $forwardedScheme = request()->header('x-forwarded-proto');
        if ($forwardedScheme === 'https' || str_starts_with(ltrim((string) config('app.url')), 'https://')) {
            URL::forceScheme('https');
        }

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
            $pendingPurchases = PurchaseRequest::query()
                ->whereIn('estado', ['solicitada', 'autorizada', 'ordenada'])
                ->when(! $isAdmin, fn ($query) => $query->where('requested_by', $user?->id))
                ->count();
            $activeToolLoans = $user?->puedeMoverStock()
                ? ToolLoan::query()->whereIn('status', ['activo', 'reparacion'])->count()
                : 0;
            $databaseNotifications = $user
                ? $user->notifications()->latest()->limit(12)->get()
                : collect();
            $unreadDatabaseNotifications = $user
                ? $user->unreadNotifications()->count()
                : 0;
            $preferences = $user
                ? UserPreference::query()
                    ->where('user_id', $user->id)
                    ->get()
                    ->mapWithKeys(fn (UserPreference $preference): array => [
                        $preference->key => $preference->value,
                    ])
                    ->all()
                : [];

            $view->with([
                'workspaceStockAlerts' => $stockAlerts,
                'workspacePendingEntries' => $pendingEntries,
                'workspacePendingUsers' => $pendingUsers,
                'workspacePendingPurchases' => $pendingPurchases,
                'workspaceActiveToolLoans' => $activeToolLoans,
                'workspaceDatabaseNotifications' => $databaseNotifications,
                'workspaceUnreadDatabaseNotifications' => $unreadDatabaseNotifications,
                'workspacePreferences' => $preferences,
                'workspaceRecentActivity' => $isAdmin
                    ? AuditLog::query()->with('user:id,name')->latest()->limit(5)->get()
                    : collect(),
                'workspaceChatRetentionDays' => app(ChatRetention::class)->days(),
                'workspaceChatStickers' => app(ChatFeatures::class)->stickers(),
            ]);
        });
    }
}
