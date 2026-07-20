<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialMovimiento;
use App\Support\AuditLogger;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DevolucionMermaController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para registrar devoluciones o mermas.');

        $buscar = trim((string) $request->query('buscar', ''));

        $materiales = Material::query()
            ->where('es_plantilla_equipo', false)
            ->when($buscar !== '', function ($query) use ($buscar): void {
                $query->where(function ($q) use ($buscar): void {
                    $q->where('descripcion', 'LIKE', "%{$buscar}%")
                        ->orWhere('apodo', 'LIKE', "%{$buscar}%")
                        ->orWhere('numero_parte', 'LIKE', "%{$buscar}%")
                        ->orWhere('codigo_barras', 'LIKE', "%{$buscar}%")
                        ->orWhere('marca', 'LIKE', "%{$buscar}%")
                        ->orWhere('categoria', 'LIKE', "%{$buscar}%")
                        ->orWhere('almacen', 'LIKE', "%{$buscar}%");
                });
            })
            ->orderBy('descripcion')
            ->limit(40)
            ->get();

        $movimientosRecientes = MaterialMovimiento::query()
            ->with(['material', 'user'])
            ->whereIn('tipo', ['devolucion', 'merma'])
            ->latest()
            ->limit(20)
            ->get();

        return view('materiales.devoluciones', compact('materiales', 'movimientosRecientes', 'buscar'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para registrar devoluciones o mermas.');

        $datos = $request->validate([
            'material_id' => ['required', 'integer', 'exists:materials,id'],
            'tipo' => ['required', 'in:devolucion,merma'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'referencia' => ['nullable', 'string', 'max:120'],
            'motivo' => ['nullable', 'string', 'max:255'],
            'evidencia_foto' => ['nullable', 'image', 'max:6144', 'required_if:tipo,merma'],
        ], [
            'material_id.required' => 'Selecciona el material que regresa o se va a merma.',
            'tipo.required' => 'Selecciona si es devolucion o merma.',
            'cantidad.required' => 'Escribe cuantas piezas se moveran.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'evidencia_foto.required_if' => 'Para una merma se necesita foto de evidencia.',
            'evidencia_foto.image' => 'La evidencia debe ser una imagen.',
        ]);

        $material = Material::query()
            ->whereKey($datos['material_id'])
            ->where('es_plantilla_equipo', false)
            ->firstOrFail();

        $cantidad = (int) $datos['cantidad'];
        $tipo = $datos['tipo'];
        $evidencia = $request->hasFile('evidencia_foto')
            ? ImageStorage::storeOptimized($request->file('evidencia_foto'), 'evidencias-merma', 1600, 72)
            : null;

        DB::transaction(function () use ($material, $cantidad, $tipo, $datos, $request, $evidencia): void {
            $bloqueado = Material::query()->whereKey($material->id)->lockForUpdate()->firstOrFail();
            $stockAnterior = $bloqueado->stock;
            $stockNuevo = $tipo === 'devolucion'
                ? $stockAnterior + $cantidad
                : $stockAnterior - $cantidad;

            if ($tipo === 'merma' && $stockNuevo < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => "No puedes mandar a merma mas piezas de las disponibles. Stock actual: {$stockAnterior}.",
                ]);
            }

            $bloqueado->update(['stock' => $stockNuevo]);

            MaterialMovimiento::create([
                'material_id' => $bloqueado->id,
                'user_id' => $request->user()?->id,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'codigo_barras' => $bloqueado->codigo_barras,
                'referencia' => $datos['referencia'] ?? null,
                'motivo' => $datos['motivo'] ?? null,
                'evidencia_foto' => $evidencia,
            ]);
        });

        $accion = $tipo === 'devolucion' ? 'Devolucion registrada' : 'Merma registrada';

        AuditLogger::registrar('Inventario', $accion, "{$accion}: {$cantidad} pzas de {$material->descripcion}.", [
            'material_id' => $material->id,
            'cantidad' => $cantidad,
            'tipo' => $tipo,
        ], $request);

        return redirect()
            ->route('materiales.devoluciones.create')
            ->with('success', "{$accion}. Stock actualizado correctamente.");
    }
}
