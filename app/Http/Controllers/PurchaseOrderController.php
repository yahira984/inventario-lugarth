<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $estado = trim((string) $request->query('estado', ''));
        $buscar = trim((string) $request->query('buscar', ''));

        return view('admin.ordenes.index', [
            'ordenes' => PurchaseOrder::query()
                ->with(['items.material', 'user'])
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
            'materiales' => Material::query()
                ->where('es_plantilla_equipo', false)
                ->orderBy('descripcion')
                ->get(['id', 'descripcion', 'apodo', 'numero_parte', 'proveedor', 'costo_unitario']),
            'proveedores' => Material::query()
                ->whereNotNull('proveedor')
                ->where('proveedor', '<>', '')
                ->distinct()
                ->orderBy('proveedor')
                ->pluck('proveedor'),
            'estado' => $estado,
            'buscar' => $buscar,
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
            'estado' => ['required', Rule::in(['borrador', 'enviada', 'recibida', 'cancelada'])],
        ]);

        $anterior = $orden->estado;
        $orden->update(['estado' => $datos['estado']]);

        AuditLogger::registrar('Compras', 'Estado de orden', "Cambio la orden {$orden->referencia} de {$anterior} a {$orden->estado}.", [
            'purchase_order_id' => $orden->id,
        ], $request);

        return back()->with('success', 'Estado de la orden actualizado. El stock solo cambia mediante entradas aprobadas.');
    }
}
