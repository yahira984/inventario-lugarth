<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialMovimiento;
use App\Models\PurchaseOrder;
use App\Support\AuditLogger;
use App\Support\InventoryNotifier;
use App\Support\SupplierPriceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly SupplierPriceService $prices,
        private readonly InventoryNotifier $notifier,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeMoverStock(), 403);

        $estado = trim((string) $request->query('estado', ''));
        $buscar = trim((string) $request->query('buscar', ''));
        $isAdmin = $request->user()?->puedeAdministrarCatalogo() ?? false;

        return view('admin.ordenes.index', [
            'ordenes' => PurchaseOrder::query()
                ->with(['items.material', 'user', 'request.requester', 'authorizer', 'receiver'])
                ->when(! $isAdmin, fn ($query) => $query->whereIn('estado', [
                    'enviada',
                    'recepcion_parcial',
                    'recibida',
                    'facturada',
                ]))
                ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
                ->when($buscar !== '', function ($query) use ($buscar): void {
                    $query->where(function ($builder) use ($buscar): void {
                        $builder->where('proveedor', 'like', "%{$buscar}%")
                            ->orWhere('referencia', 'like', "%{$buscar}%");
                    });
                })
                ->latest('fecha_orden')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'materiales' => $isAdmin
                ? Material::query()
                    ->where('es_plantilla_equipo', false)
                    ->orderBy('descripcion')
                    ->get(['id', 'descripcion', 'apodo', 'numero_parte', 'proveedor', 'costo_unitario'])
                : collect(),
            'proveedores' => $isAdmin
                ? Material::query()
                    ->whereNotNull('proveedor')
                    ->where('proveedor', '<>', '')
                    ->distinct()
                    ->orderBy('proveedor')
                    ->pluck('proveedor')
                : collect(),
            'estado' => $estado,
            'buscar' => $buscar,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $datos = $request->validate([
            'proveedor' => ['required', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:120', 'unique:purchase_orders,referencia'],
            'fecha_orden' => ['required', 'date'],
            'fecha_esperada' => ['nullable', 'date', 'after_or_equal:fecha_orden'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'material_id' => ['required', 'array', 'min:1'],
            'material_id.*' => ['nullable', 'integer', 'exists:materials,id'],
            'descripcion' => ['required', 'array', 'min:1'],
            'descripcion.*' => ['required', 'string', 'max:255'],
            'cantidad' => ['required', 'array', 'min:1'],
            'cantidad.*' => ['required', 'numeric', 'min:0.01'],
            'costo_unitario' => ['required', 'array', 'min:1'],
            'costo_unitario.*' => ['required', 'numeric', 'min:0'],
        ], [
            'proveedor.required' => 'Selecciona o escribe el proveedor.',
            'fecha_orden.required' => 'Indica la fecha de la orden.',
            'descripcion.*.required' => 'Cada renglon necesita una descripcion.',
            'cantidad.*.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $orden = DB::transaction(function () use ($datos, $request): PurchaseOrder {
            $orden = PurchaseOrder::create([
                'user_id' => $request->user()?->id,
                'proveedor' => trim($datos['proveedor']),
                'referencia' => trim((string) ($datos['referencia'] ?? '')) ?: null,
                'estado' => 'borrador',
                'fecha_orden' => $datos['fecha_orden'],
                'fecha_esperada' => $datos['fecha_esperada'] ?? null,
                'notas' => trim((string) ($datos['notas'] ?? '')) ?: null,
                'moneda' => 'MXN',
                'total' => 0,
            ]);

            $total = 0.0;
            foreach ($datos['descripcion'] as $index => $descripcion) {
                $cantidad = (float) ($datos['cantidad'][$index] ?? 0);
                $costo = (float) ($datos['costo_unitario'][$index] ?? 0);
                $subtotal = round($cantidad * $costo, 2);
                $total += $subtotal;

                $orden->items()->create([
                    'material_id' => $datos['material_id'][$index] ?: null,
                    'descripcion' => trim($descripcion),
                    'cantidad' => $cantidad,
                    'costo_unitario' => $costo,
                    'subtotal' => $subtotal,
                ]);
            }

            $orden->update(['total' => round($total, 2)]);

            return $orden;
        });

        AuditLogger::registrar('Compras', 'Orden creada', "Creo la orden de compra {$orden->referencia} para {$orden->proveedor}.", [
            'purchase_order_id' => $orden->id,
            'total' => $orden->total,
        ], $request);

        return back()->with('success', 'Orden de compra guardada como borrador.');
    }

    public function updateStatus(Request $request, PurchaseOrder $orden): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $datos = $request->validate([
            'estado' => ['required', Rule::in(['borrador', 'autorizada', 'enviada', 'cancelada'])],
        ]);

        $anterior = $orden->estado;
        $nuevoEstado = $datos['estado'];

        if (in_array($anterior, ['recibida', 'facturada', 'cancelada'], true)) {
            throw ValidationException::withMessages([
                'estado' => 'Esta orden ya esta cerrada y no puede regresar a otra etapa.',
            ]);
        }

        $updates = ['estado' => $nuevoEstado];
        if ($nuevoEstado === 'autorizada') {
            $updates['authorized_by'] = $request->user()?->id;
            $updates['authorized_at'] = now();
        } elseif ($nuevoEstado === 'enviada') {
            if (! in_array($anterior, ['autorizada', 'enviada'], true)) {
                throw ValidationException::withMessages([
                    'estado' => 'Primero autoriza la orden antes de marcarla como enviada.',
                ]);
            }
            $updates['ordered_at'] = $orden->ordered_at ?: now();
        }

        $orden->update($updates);

        AuditLogger::registrar('Compras', 'Estado de orden', "Cambio la orden {$orden->referencia} de {$anterior} a {$orden->estado}.", [
            'purchase_order_id' => $orden->id,
        ], $request);

        return back()->with('success', 'Etapa de la orden actualizada. El stock cambia solamente al registrar la recepcion.');
    }

    public function receive(Request $request, PurchaseOrder $orden): RedirectResponse
    {
        abort_unless($request->user()?->puedeMoverStock(), 403);

        if (! in_array($orden->estado, ['enviada', 'recepcion_parcial'], true)) {
            throw ValidationException::withMessages([
                'recepcion' => 'La orden debe estar enviada antes de registrar su recepcion.',
            ]);
        }

        $data = $request->validate([
            'cantidad_recibida' => ['nullable', 'array'],
            'cantidad_recibida.*' => ['nullable', 'numeric', 'min:0'],
            'referencia_recepcion' => ['nullable', 'string', 'max:120'],
        ]);

        $received = DB::transaction(function () use ($request, $orden, $data): array {
            $lockedOrder = PurchaseOrder::query()
                ->whereKey($orden->id)
                ->lockForUpdate()
                ->firstOrFail();
            $items = $lockedOrder->items()->with('material')->lockForUpdate()->get();
            $materialIds = $items->pluck('material_id')->filter()->unique();
            $materials = Material::query()
                ->whereIn('id', $materialIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $receivedItems = [];

            foreach ($items as $item) {
                $remaining = max(0, (float) $item->cantidad - (float) $item->cantidad_recibida);
                $quantity = array_key_exists($item->id, $data['cantidad_recibida'] ?? [])
                    ? (float) $data['cantidad_recibida'][$item->id]
                    : $remaining;

                if ($quantity <= 0) {
                    continue;
                }
                if ($quantity > $remaining + 0.0001) {
                    throw ValidationException::withMessages([
                        "cantidad_recibida.{$item->id}" => "No puedes recibir mas de {$remaining} unidades pendientes.",
                    ]);
                }
                if (abs($quantity - round($quantity)) > 0.0001) {
                    throw ValidationException::withMessages([
                        "cantidad_recibida.{$item->id}" => 'El inventario maneja piezas enteras. Corrige la cantidad recibida.',
                    ]);
                }

                $material = $materials->get($item->material_id);
                if (! $material) {
                    throw ValidationException::withMessages([
                        "cantidad_recibida.{$item->id}" => "Vincula {$item->descripcion} con un material real antes de recibirlo.",
                    ]);
                }

                $integerQuantity = (int) round($quantity);
                $previousStock = (int) $material->stock;
                $newStock = $previousStock + $integerQuantity;
                $material->update([
                    'stock' => $newStock,
                    'proveedor' => $lockedOrder->proveedor,
                    'costo_unitario' => (float) $item->costo_unitario > 0
                        ? $item->costo_unitario
                        : $material->costo_unitario,
                ]);

                MaterialMovimiento::create([
                    'material_id' => $material->id,
                    'user_id' => $request->user()?->id,
                    'tipo' => 'entrada',
                    'cantidad' => $integerQuantity,
                    'stock_anterior' => $previousStock,
                    'stock_nuevo' => $newStock,
                    'codigo_barras' => $material->codigo_barras,
                    'referencia' => trim((string) ($data['referencia_recepcion'] ?? ''))
                        ?: $lockedOrder->referencia,
                    'motivo' => "Recepcion de orden {$lockedOrder->referencia}",
                    'proveedor' => $lockedOrder->proveedor,
                    'costo_unitario' => $item->costo_unitario,
                ]);

                $item->update([
                    'cantidad_recibida' => (float) $item->cantidad_recibida + $quantity,
                ]);
                $receivedItems[] = [$material, $item, $integerQuantity];
            }

            if ($receivedItems === []) {
                throw ValidationException::withMessages([
                    'recepcion' => 'No hay cantidades nuevas por recibir.',
                ]);
            }

            $allReceived = $lockedOrder->items()
                ->whereColumn('cantidad_recibida', '<', 'cantidad')
                ->doesntExist();
            $lockedOrder->update([
                'estado' => $allReceived ? 'recibida' : 'recepcion_parcial',
                'received_by' => $request->user()?->id,
                'received_at' => $allReceived ? now() : $lockedOrder->received_at,
            ]);

            if ($allReceived && $lockedOrder->request) {
                $lockedOrder->request->update(['estado' => 'recibida']);
            }

            return [$receivedItems, $allReceived];
        });

        [$receivedItems, $allReceived] = $received;
        foreach ($receivedItems as [$material, $item]) {
            $this->prices->record(
                $material,
                $orden->proveedor,
                (float) $item->costo_unitario,
                $orden->moneda ?: 'MXN',
                'orden_compra',
                $orden->referencia,
                null,
                now()
            );
        }

        $orden->refresh()->load('request.requester');
        if ($orden->request?->requester) {
            $this->notifier->user(
                $orden->request->requester,
                $allReceived ? 'Orden recibida' : 'Recepcion parcial',
                "Se recibieron materiales de la orden {$orden->referencia}.",
                route('admin.ordenes.index', ['buscar' => $orden->referencia]),
                'green'
            );
        }

        AuditLogger::registrar('Compras', 'Recepcion de orden', "Registro recepcion de {$orden->referencia}.", [
            'purchase_order_id' => $orden->id,
            'recepcion_completa' => $allReceived,
        ], $request);

        return back()->with(
            'success',
            $allReceived
                ? 'Orden recibida por completo. El stock y los precios ya fueron actualizados.'
                : 'Recepcion parcial guardada. Solo se sumaron las cantidades recibidas.'
        );
    }

    public function invoice(Request $request, PurchaseOrder $orden): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        if ($orden->estado !== 'recibida') {
            throw ValidationException::withMessages([
                'factura' => 'Primero recibe por completo la orden.',
            ]);
        }

        $data = $request->validate([
            'invoice_uuid' => ['nullable', 'string', 'max:40'],
            'invoice_folio' => ['required_without:invoice_uuid', 'nullable', 'string', 'max:120'],
        ], [
            'invoice_folio.required_without' => 'Escribe el folio o el UUID de la factura.',
        ]);

        $orden->update([
            'estado' => 'facturada',
            'invoice_uuid' => strtoupper(trim((string) ($data['invoice_uuid'] ?? ''))) ?: null,
            'invoice_folio' => trim((string) ($data['invoice_folio'] ?? '')) ?: null,
            'invoiced_by' => $request->user()?->id,
            'invoiced_at' => now(),
        ]);
        $orden->request?->update(['estado' => 'facturada']);

        AuditLogger::registrar('Compras', 'Orden facturada', "Vinculo factura a {$orden->referencia}.", [
            'purchase_order_id' => $orden->id,
            'invoice_uuid' => $orden->invoice_uuid,
        ], $request);

        return back()->with('success', 'Factura vinculada. El flujo de compra quedo completo y trazable.');
    }
}
