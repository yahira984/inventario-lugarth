<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialSupplierPrice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Support\AuditLogger;
use App\Support\InventoryAnalyticsService;
use App\Support\InventoryNotifier;
use App\Support\ProcurementSuggestionService;
use App\Support\SupplierPriceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProcurementController extends Controller
{
    public function __construct(
        private readonly ProcurementSuggestionService $suggestions,
        private readonly InventoryAnalyticsService $analytics,
        private readonly SupplierPriceService $prices,
        private readonly InventoryNotifier $notifier,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeMoverStock(), 403);

        $isAdmin = $request->user()?->puedeAdministrarCatalogo() ?? false;
        $inactiveDays = (int) $request->integer('sin_movimiento', 90);
        $inactiveDays = in_array($inactiveDays, [30, 90, 180], true) ? $inactiveDays : 90;
        $requestFilter = (string) $request->query('solicitudes', 'pendientes');
        $requestFilter = in_array($requestFilter, ['pendientes', 'autorizadas', 'con_orden', 'rechazadas', 'todas'], true)
            ? $requestFilter
            : 'pendientes';

        $requestsQuery = PurchaseRequest::query()
            ->when(! $isAdmin, fn ($query) => $query->where('requested_by', $request->user()->id));
        $requestCounts = [
            'pendientes' => (clone $requestsQuery)->where('estado', 'solicitada')->count(),
            'autorizadas' => (clone $requestsQuery)->where('estado', 'autorizada')->count(),
            'con_orden' => (clone $requestsQuery)->whereIn('estado', ['ordenada', 'recibida', 'facturada'])->count(),
            'rechazadas' => (clone $requestsQuery)->where('estado', 'rechazada')->count(),
            'todas' => (clone $requestsQuery)->count(),
        ];

        $requestsQuery->when($requestFilter === 'pendientes', fn ($query) => $query->where('estado', 'solicitada'))
            ->when($requestFilter === 'autorizadas', fn ($query) => $query->where('estado', 'autorizada'))
            ->when($requestFilter === 'con_orden', fn ($query) => $query->whereIn('estado', ['ordenada', 'recibida', 'facturada']))
            ->when($requestFilter === 'rechazadas', fn ($query) => $query->where('estado', 'rechazada'));

        return view('admin.compras.index', [
            'sugerencias' => $this->suggestions->suggestions(60),
            'solicitudes' => $requestsQuery
                ->with([
                    'items.material.supplierPrices' => fn ($query) => $query->latest('registrado_en')->latest('id'),
                    'requester',
                    'reviewer',
                    'order',
                ])
                ->latest()
                ->paginate(15, ['*'], 'solicitudes_page')
                ->withQueryString(),
            'requestFilter' => $requestFilter,
            'requestCounts' => $requestCounts,
            'excesos' => Material::query()
                ->where('es_plantilla_equipo', false)
                ->where('stock_maximo', '>', 0)
                ->whereColumn('stock', '>', 'stock_maximo')
                ->orderByRaw('(stock - stock_maximo) DESC')
                ->limit(30)
                ->get(),
            'sinMovimiento' => $this->suggestions->inactive($inactiveDays)->take(40),
            'inactiveDays' => $inactiveDays,
            'aumentos' => $isAdmin
                ? MaterialSupplierPrice::query()
                    ->with('material:id,descripcion,numero_parte,fotografia')
                    ->where('aumento_significativo', true)
                    ->latest('registrado_en')
                    ->limit(12)
                    ->get()
                : collect(),
            'isAdmin' => $isAdmin,
        ]);
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->puedeMoverStock(), 403);

        $data = $request->validate([
            'material_id' => ['required', 'array', 'min:1'],
            'material_id.*' => ['required', 'integer', 'distinct', 'exists:materials,id'],
            'cantidad' => ['required', 'array', 'min:1'],
            'cantidad.*' => ['required', 'numeric', 'min:0.01', 'max:1000000000'],
            'prioridad' => ['required', Rule::in(['normal', 'alta', 'urgente'])],
            'motivo' => ['nullable', 'string', 'max:1000'],
            'origen' => ['nullable', Rule::in(['manual', 'stock_minimo', 'consumo', 'equipo'])],
        ], [
            'material_id.required' => 'Selecciona al menos un material.',
            'cantidad.*.min' => 'La cantidad solicitada debe ser mayor a cero.',
        ]);

        $materials = Material::query()
            ->whereIn('id', $data['material_id'])
            ->where('es_plantilla_equipo', false)
            ->get()
            ->keyBy('id');

        if ($materials->count() !== count($data['material_id'])) {
            throw ValidationException::withMessages([
                'material_id' => 'Una de las piezas seleccionadas ya no pertenece al inventario real.',
            ]);
        }

        $existingMaterialIds = PurchaseRequest::query()
            ->whereIn('estado', ['solicitada', 'autorizada', 'ordenada'])
            ->whereHas('items', fn ($query) => $query->whereIn('material_id', $data['material_id']))
            ->with('items:id,purchase_request_id,material_id')
            ->get()
            ->flatMap->items
            ->pluck('material_id')
            ->unique();

        if ($existingMaterialIds->isNotEmpty() && ! $request->boolean('permitir_duplicados')) {
            $names = $materials->whereIn('id', $existingMaterialIds)->pluck('descripcion')->implode(', ');
            throw ValidationException::withMessages([
                'material_id' => "Ya existe una solicitud activa para: {$names}. Revísala antes de duplicar la compra.",
            ]);
        }

        $purchaseRequest = DB::transaction(function () use ($request, $data, $materials): PurchaseRequest {
            $purchaseRequest = PurchaseRequest::create([
                'requested_by' => $request->user()?->id,
                'estado' => 'solicitada',
                'prioridad' => $data['prioridad'],
                'origen' => $data['origen'] ?? 'manual',
                'motivo' => trim((string) ($data['motivo'] ?? '')) ?: null,
            ]);

            foreach ($data['material_id'] as $index => $materialId) {
                $material = $materials->get((int) $materialId);
                $suggestion = $this->suggestions->forMaterial($material);
                $purchaseRequest->items()->create([
                    'material_id' => $material->id,
                    'cantidad_solicitada' => (float) (
                        $data['cantidad'][$material->id]
                        ?? $data['cantidad'][$index]
                        ?? 0
                    ),
                    'consumo_diario_estimado' => $suggestion['consumo_diario'],
                    'dias_cobertura' => ProcurementSuggestionService::DEFAULT_COVERAGE_DAYS,
                    'razon' => $suggestion['razon'],
                ]);
            }

            return $purchaseRequest;
        });

        AuditLogger::registrar(
            'Compras',
            'Solicitud de compra',
            "Registro la solicitud #{$purchaseRequest->id} con {$purchaseRequest->items()->count()} materiales.",
            ['purchase_request_id' => $purchaseRequest->id],
            $request
        );

        $this->notifier->admins(
            'Nueva solicitud de compra',
            "{$request->user()->name} solicito {$purchaseRequest->items()->count()} materiales.",
            route('admin.compras.index').'#solicitud-'.$purchaseRequest->id,
            $purchaseRequest->prioridad === 'urgente' ? 'red' : 'amber',
            ['purchase_request_id' => $purchaseRequest->id],
            $request->user()->id
        );

        return redirect()
            ->route('admin.compras.index')
            ->with('success', "Solicitud #{$purchaseRequest->id} creada y enviada a Compras para revisión. No modifica el stock ni realiza una compra hasta que sea autorizada y recibida.");
    }

    public function quickRequest(Request $request, Material $material): RedirectResponse
    {
        abort_unless($request->user()?->puedeMoverStock(), 403);
        abort_if($material->es_plantilla_equipo, 404);

        $activeRequest = PurchaseRequest::query()
            ->whereIn('estado', ['solicitada', 'autorizada', 'ordenada'])
            ->whereHas('items', fn ($query) => $query->where('material_id', $material->id))
            ->latest()
            ->first();

        if ($activeRequest) {
            return back()->with(
                'warning',
                "No se creó otra solicitud: {$material->descripcion} ya está en la solicitud #{$activeRequest->id} (".ucfirst($activeRequest->estado)."). Revísala en Planeación de compras."
            );
        }

        $suggestion = $this->suggestions->forMaterial($material);
        $quantity = max(1, (int) $request->integer('cantidad', $suggestion['cantidad_sugerida']));
        $request->merge([
            'material_id' => [$material->id],
            'cantidad' => [$material->id => $quantity],
            'prioridad' => $request->input('prioridad', $material->stock <= 0 ? 'urgente' : 'alta'),
            'motivo' => $request->input('motivo', $suggestion['razon']),
            'origen' => 'stock_minimo',
        ]);

        return $this->storeRequest($request);
    }

    public function destroyRequest(Request $request, PurchaseRequest $solicitud): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        DB::transaction(function () use ($solicitud, $request): void {
            $locked = PurchaseRequest::query()
                ->with('items')
                ->whereKey($solicitud->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($locked->order()->exists(), 409, 'Esta solicitud ya generó una orden. Administra o cancela la orden desde Órdenes de compra.');
            abort_unless(in_array($locked->estado, ['solicitada', 'autorizada', 'rechazada'], true), 409, 'Solo pueden eliminarse solicitudes que no han iniciado recepción.');

            $itemCount = $locked->items->count();
            $requestId = $locked->id;
            $locked->items()->delete();
            $locked->delete();

            AuditLogger::registrar(
                'Compras',
                'Solicitud eliminada',
                "Eliminó la solicitud #{$requestId} con {$itemCount} piezas antes de crear una orden.",
                ['purchase_request_id' => $requestId],
                $request
            );
        });

        return back()->with('success', 'Solicitud eliminada. No se modificó el inventario.');
    }

    public function authorizeRequest(Request $request, PurchaseRequest $solicitud): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);
        abort_unless($solicitud->estado === 'solicitada', 409, 'La solicitud ya fue revisada.');

        $solicitud->load('items');
        $data = $request->validate([
            'cantidad_autorizada' => ['required', 'array'],
            'cantidad_autorizada.*' => ['required', 'numeric', 'min:0.01', 'max:1000000000'],
            'comentario_revision' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($solicitud, $data, $request): void {
            $locked = PurchaseRequest::query()->whereKey($solicitud->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->estado === 'solicitada', 409, 'La solicitud ya fue revisada.');

            foreach ($solicitud->items as $item) {
                $quantity = (float) ($data['cantidad_autorizada'][$item->id] ?? 0);
                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        "cantidad_autorizada.{$item->id}" => 'Indica una cantidad autorizada mayor a cero.',
                    ]);
                }
                $item->update(['cantidad_autorizada' => $quantity]);
            }

            $locked->update([
                'estado' => 'autorizada',
                'reviewed_by' => $request->user()?->id,
                'reviewed_at' => now(),
                'comentario_revision' => trim((string) ($data['comentario_revision'] ?? '')) ?: null,
            ]);
        });

        $this->notifyRequester($solicitud->fresh(), 'Solicitud autorizada', 'Tu solicitud fue autorizada y ya puede convertirse en orden.', 'green');

        return back()->with('success', 'Solicitud autorizada. El siguiente paso es generar la orden de compra.');
    }

    public function rejectRequest(Request $request, PurchaseRequest $solicitud): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);
        abort_unless($solicitud->estado === 'solicitada', 409, 'La solicitud ya fue revisada.');

        $data = $request->validate([
            'comentario_revision' => ['required', 'string', 'max:1000'],
        ], [
            'comentario_revision.required' => 'Explica por que se rechaza la solicitud.',
        ]);

        $solicitud->update([
            'estado' => 'rechazada',
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'comentario_revision' => trim($data['comentario_revision']),
        ]);

        $this->notifyRequester($solicitud, 'Solicitud rechazada', $solicitud->comentario_revision, 'red');

        return back()->with('success', 'Solicitud rechazada sin afectar stock ni órdenes.');
    }

    public function createOrder(Request $request, PurchaseRequest $solicitud): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);
        abort_unless($solicitud->estado === 'autorizada', 409, 'La solicitud debe estar autorizada.');
        abort_if($solicitud->order()->exists(), 409, 'Esta solicitud ya tiene una orden.');

        $solicitud->load(['items.material.latestSupplierPrice', 'items.material.supplierPrices']);
        if ($request->input('proveedor') === '__otro__') {
            $request->merge(['proveedor' => $request->input('proveedor_otro')]);
        }
        $data = $request->validate([
            'proveedor' => ['required', 'string', 'max:255'],
            'proveedor_otro' => ['nullable', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:120', 'unique:purchase_orders,referencia'],
            'fecha_esperada' => ['nullable', 'date', 'after_or_equal:today'],
            'costo_unitario' => ['nullable', 'array'],
            'costo_unitario.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = DB::transaction(function () use ($request, $solicitud, $data): PurchaseOrder {
            $order = PurchaseOrder::create([
                'purchase_request_id' => $solicitud->id,
                'user_id' => $request->user()?->id,
                'authorized_by' => $solicitud->reviewed_by,
                'proveedor' => trim($data['proveedor']),
                'referencia' => trim((string) ($data['referencia'] ?? '')) ?: 'OC-'.now()->format('Ymd').'-'.$solicitud->id,
                'estado' => 'borrador',
                'fecha_orden' => today(),
                'fecha_esperada' => $data['fecha_esperada'] ?? null,
                'authorized_at' => $solicitud->reviewed_at,
                'moneda' => 'MXN',
                'notas' => "Generada desde solicitud #{$solicitud->id}.",
                'total' => 0,
            ]);

            $total = 0.0;
            foreach ($solicitud->items as $item) {
                $supplierPrices = $item->material?->supplierPrices ?? collect();
                $supplierPrice = $supplierPrices
                    ->filter(fn (MaterialSupplierPrice $price): bool => mb_strtolower(trim($price->proveedor)) === mb_strtolower(trim($data['proveedor'])))
                    ->sortByDesc(fn (MaterialSupplierPrice $price): int => $price->registrado_en?->getTimestamp() ?? 0)
                    ->first();
                $submittedCost = $data['costo_unitario'][$item->id] ?? null;
                $cost = $submittedCost !== null && $submittedCost !== ''
                    ? (float) $submittedCost
                    : (float) ($supplierPrice?->precio_unitario
                        ?? $item->material?->latestSupplierPrice?->precio_unitario
                        ?? $item->material?->costo_unitario
                        ?? 0);
                $quantity = (float) ($item->cantidad_autorizada ?: $item->cantidad_solicitada);
                $subtotal = round($quantity * $cost, 2);
                $total += $subtotal;
                $order->items()->create([
                    'material_id' => $item->material_id,
                    'descripcion' => $item->material?->descripcion ?? 'Material eliminado',
                    'cantidad' => $quantity,
                    'cantidad_recibida' => 0,
                    'costo_unitario' => $cost,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update(['total' => round($total, 2)]);
            $solicitud->update(['estado' => 'ordenada']);

            return $order;
        });

        $this->notifyRequester($solicitud, 'Orden de compra creada', "Se genero la orden {$order->referencia}.", 'blue');

        return redirect()
            ->route('admin.ordenes.index', ['buscar' => $order->referencia])
            ->with('success', 'Orden creada desde la solicitud autorizada.');
    }

    public function comparator(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $search = trim((string) $request->query('buscar', ''));
        $materialId = $request->integer('material_id');
        $materials = Material::query()
            ->where('es_plantilla_equipo', false)
            ->with(['supplierPrices' => fn ($query) => $query->latest('registrado_en')])
            ->when($materialId, fn ($query) => $query->whereKey($materialId))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('descripcion', 'like', "%{$search}%")
                        ->orWhere('apodo', 'like', "%{$search}%")
                        ->orWhere('numero_parte', 'like', "%{$search}%")
                        ->orWhere('proveedor', 'like', "%{$search}%");
                });
            })
            ->orderBy('descripcion')
            ->limit(500)
            ->get();

        $groups = $materials
            ->groupBy(fn (Material $material): string => $this->prices->productKey($material))
            ->map(function (Collection $group): array {
                $offers = $group->flatMap(function (Material $material): Collection {
                    $history = $material->supplierPrices->groupBy(
                        fn (MaterialSupplierPrice $price): string => mb_strtolower($price->proveedor)
                    )->map->first()->values();

                    if ($history->isEmpty() && filled($material->proveedor) && (float) $material->costo_unitario > 0) {
                        return collect([[
                            'material_id' => $material->id,
                            'proveedor' => $material->proveedor,
                            'proveedor_rfc' => $material->proveedor_rfc,
                            'precio' => (float) $material->costo_unitario,
                            'moneda' => $material->moneda ?: 'MXN',
                            'fecha' => $material->factura_fecha ?: $material->updated_at,
                            'variacion' => null,
                            'aumento' => false,
                        ]]);
                    }

                    return $history->map(fn (MaterialSupplierPrice $price): array => [
                        'material_id' => $material->id,
                        'proveedor' => $price->proveedor,
                        'proveedor_rfc' => $price->proveedor_rfc,
                        'precio' => (float) $price->precio_unitario,
                        'moneda' => $price->moneda,
                        'fecha' => $price->registrado_en,
                        'variacion' => $price->variacion_porcentaje,
                        'aumento' => $price->aumento_significativo,
                    ]);
                })->sortBy('precio')->values();

                return [
                    'materiales' => $group,
                    'representante' => $group->first(),
                    'ofertas' => $offers,
                    'mejor_precio' => (float) ($offers->first()['precio'] ?? 0),
                    'ahorro_maximo' => $offers->count() > 1
                        ? round((float) $offers->max('precio') - (float) $offers->min('precio'), 2)
                        : 0,
                ];
            })
            ->filter(fn (array $group): bool => $group['ofertas']->isNotEmpty())
            ->sortBy(fn (array $group): string => $group['representante']->descripcion)
            ->values();

        $monthly = MaterialSupplierPrice::query()
            ->select('proveedor')
            ->selectRaw("substr(registrado_en, 1, 7) as periodo")
            ->selectRaw('AVG(precio_unitario) as precio_promedio')
            ->selectRaw('COUNT(*) as compras')
            ->where('registrado_en', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('proveedor', 'periodo')
            ->orderBy('periodo')
            ->get();

        return view('admin.compras.comparador', compact('groups', 'search', 'monthly'));
    }

    public function historicalInventory(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $request->validate([
            'fecha' => ['nullable', 'date', 'before_or_equal:today'],
            'buscar' => ['nullable', 'string', 'max:255'],
        ]);
        $date = Carbon::parse($request->query('fecha', today()->toDateString()));
        $search = trim((string) $request->query('buscar', ''));
        $materials = $this->analytics->stockAt($date)
            ->when($search !== '', function (Collection $items) use ($search): Collection {
                $needle = mb_strtolower($search);

                return $items->filter(fn (Material $material): bool => str_contains(
                    mb_strtolower(implode(' ', [
                        $material->descripcion,
                        $material->apodo,
                        $material->numero_parte,
                        $material->categoria,
                        $material->almacen,
                    ])),
                    $needle
                ))->values();
            });
        $page = max(1, $request->integer('page', 1));
        $perPage = 50;
        $paginated = new LengthAwarePaginator(
            $materials->forPage($page, $perPage)->values(),
            $materials->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $breakdowns = $this->analytics->valueBreakdowns($materials);

        return view('admin.compras.historico', [
            'fecha' => $date,
            'materiales' => $paginated,
            'valorHistorico' => $materials->sum('valor_historico'),
            'piezasHistoricas' => $materials->sum('stock_historico'),
            'breakdowns' => $breakdowns,
            'search' => $search,
        ]);
    }

    private function notifyRequester(
        PurchaseRequest $request,
        string $title,
        string $message,
        string $tone
    ): void {
        $request->loadMissing('requester');
        $this->notifier->user(
            $request->requester,
            $title,
            $message,
            route('admin.compras.index').'#solicitud-'.$request->id,
            $tone,
            ['purchase_request_id' => $request->id]
        );
    }
}
