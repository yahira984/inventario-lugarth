<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialEntradaPendiente;
use App\Models\MaterialMovimiento;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminEntradaPendienteController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $estado = $request->query('estado', 'pendiente');

        $entradas = MaterialEntradaPendiente::query()
            ->with(['material', 'user', 'approver', 'rejecter'])
            ->when($estado !== 'todas', fn ($query) => $query->where('estado', $estado))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.entradas.index', compact('entradas', 'estado'));
    }

    public function approve(Request $request, MaterialEntradaPendiente $entrada): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        if ($entrada->estado !== 'pendiente') {
            return back()->withErrors(['entrada' => 'Esta entrada ya fue revisada.']);
        }

        $esMaterialNuevo = (bool) $entrada->es_material_nuevo;

        DB::transaction(function () use ($entrada, $request): void {
            $entradaBloqueada = MaterialEntradaPendiente::query()
                ->whereKey($entrada->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($entradaBloqueada->estado !== 'pendiente') {
                throw ValidationException::withMessages([
                    'entrada' => 'Esta entrada ya fue revisada por otro administrador.',
                ]);
            }

            if ($entradaBloqueada->es_material_nuevo) {
                $datosMaterial = $entradaBloqueada->datos_material ?? [];
                $descripcion = trim((string) data_get($datosMaterial, 'descripcion', ''));
                $codigo = trim((string) ($entradaBloqueada->codigo_barras ?? ''));

                if ($descripcion === '') {
                    throw ValidationException::withMessages([
                        'entrada' => 'La solicitud no contiene el nombre del material. Rechazala y pide al almacenista capturarlo nuevamente.',
                    ]);
                }

                $material = $codigo !== ''
                    ? Material::query()
                        ->where('codigo_barras', $codigo)
                        ->where('es_plantilla_equipo', false)
                        ->lockForUpdate()
                        ->first()
                    : null;

                if (! $material) {
                    $material = Material::create(array_merge(
                        Arr::only($datosMaterial, [
                            'categoria',
                            'almacen',
                            'codigo_barras',
                            'numero_parte',
                            'clave_sat',
                            'clave_unidad',
                            'unidad',
                            'descripcion',
                            'apodo',
                            'marca',
                            'proveedor',
                            'proveedor_rfc',
                            'stock_minimo',
                            'stock_maximo',
                            'costo_unitario',
                            'moneda',
                        ]),
                        [
                            'stock' => 0,
                            'es_plantilla_equipo' => false,
                            'fotografia' => $entradaBloqueada->fotografia,
                        ]
                    ));
                }
            } else {
                $material = Material::query()
                    ->whereKey($entradaBloqueada->material_id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $anterior = $material->stock;
            $nuevo = $anterior + $entradaBloqueada->cantidad;
            $material->update([
                'stock' => $nuevo,
                'proveedor' => $entradaBloqueada->proveedor ?: $material->proveedor,
                'costo_unitario' => (float) $entradaBloqueada->costo_unitario > 0
                    ? $entradaBloqueada->costo_unitario
                    : $material->costo_unitario,
            ]);

            MaterialMovimiento::create([
                'material_id' => $material->id,
                'user_id' => $entradaBloqueada->user_id,
                'tipo' => 'entrada',
                'cantidad' => $entradaBloqueada->cantidad,
                'stock_anterior' => $anterior,
                'stock_nuevo' => $nuevo,
                'codigo_barras' => $entradaBloqueada->codigo_barras ?: $material->codigo_barras,
                'referencia' => $entradaBloqueada->referencia ?: 'Entrada aprobada',
                'motivo' => $entradaBloqueada->motivo ?: 'Aprobada por administrador',
                'evidencia_foto' => $entradaBloqueada->evidencia_foto,
                'proveedor' => $entradaBloqueada->proveedor ?: $material->proveedor,
                'costo_unitario' => (float) $entradaBloqueada->costo_unitario > 0
                    ? $entradaBloqueada->costo_unitario
                    : $material->costo_unitario,
            ]);

            $entradaBloqueada->update([
                'material_id' => $material->id,
                'estado' => 'aprobada',
                'approved_by' => $request->user()?->id,
                'approved_at' => now(),
                'comentario_admin' => $request->input('comentario_admin'),
            ]);
        });

        $entrada->refresh();

        AuditLogger::registrar('Entradas', 'Entrada aprobada', "Aprobo entrada de {$entrada->cantidad} piezas.", [
            'entrada_pendiente_id' => $entrada->id,
            'material_id' => $entrada->material_id,
            'cantidad' => $entrada->cantidad,
        ], $request);

        return back()->with(
            'success',
            $esMaterialNuevo
                ? 'Material creado y entrada aprobada. La pieza ya aparece en inventario con su stock actualizado.'
                : 'Entrada aprobada. El stock ya fue actualizado.'
        );
    }

    public function reject(Request $request, MaterialEntradaPendiente $entrada): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        if ($entrada->estado !== 'pendiente') {
            return back()->withErrors(['entrada' => 'Esta entrada ya fue revisada.']);
        }

        DB::transaction(function () use ($entrada, $request): void {
            $entradaBloqueada = MaterialEntradaPendiente::query()
                ->whereKey($entrada->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($entradaBloqueada->estado !== 'pendiente') {
                throw ValidationException::withMessages([
                    'entrada' => 'Esta entrada ya fue revisada por otro administrador.',
                ]);
            }

            $entradaBloqueada->update([
                'estado' => 'rechazada',
                'rejected_by' => $request->user()?->id,
                'rejected_at' => now(),
                'comentario_admin' => $request->input('comentario_admin'),
            ]);
        });

        AuditLogger::registrar('Entradas', 'Entrada rechazada', "Rechazo entrada de {$entrada->cantidad} piezas.", [
            'entrada_pendiente_id' => $entrada->id,
            'material_id' => $entrada->material_id,
            'cantidad' => $entrada->cantidad,
        ], $request);

        return back()->with('success', 'Entrada rechazada. El stock no se modifico.');
    }
}
