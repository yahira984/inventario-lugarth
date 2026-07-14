<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialMovimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SalidaMaterialController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para registrar salidas.');

        $buscar = trim((string) $request->query('buscar', ''));

        $materiales = Material::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('descripcion', 'LIKE', '%' . $buscar . '%')
                        ->orWhere('numero_parte', 'LIKE', '%' . $buscar . '%')
                        ->orWhere('codigo_barras', 'LIKE', '%' . $buscar . '%')
                        ->orWhere('marca', 'LIKE', '%' . $buscar . '%');
                });
            })
            ->orderByRaw('stock <= 0')
            ->orderBy('descripcion')
            ->limit(40)
            ->get();

        $salidasRecientes = MaterialMovimiento::query()
            ->with(['material', 'user'])
            ->where('tipo', 'salida')
            ->latest()
            ->limit(20)
            ->get();

        return view('materiales.salidas', [
            'materiales' => $materiales,
            'salidasRecientes' => $salidasRecientes,
            'buscar' => $buscar,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para registrar salidas.');

        $datos = $request->validate([
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'codigo_barras' => ['nullable', 'string', 'max:255'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'referencia' => ['nullable', 'string', 'max:120'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ], [
            'material_id.exists' => 'El material seleccionado ya no existe. Busca el producto otra vez.',
            'cantidad.required' => 'Escribe cuántas piezas van a salir.',
            'cantidad.integer' => 'La cantidad debe ser un número entero. Ejemplo: 1, 2, 10.',
            'cantidad.min' => 'La salida debe ser de al menos 1 pieza.',
            'referencia.max' => 'La referencia no debe tener más de 120 caracteres.',
            'motivo.max' => 'El motivo no debe tener más de 255 caracteres.',
        ]);

        $codigoBarras = trim((string) ($datos['codigo_barras'] ?? ''));
        $materialId = $datos['material_id'] ?? null;

        if (!$materialId && $codigoBarras === '') {
            throw ValidationException::withMessages([
                'codigo_barras' => 'Escanea un código o selecciona un material manualmente.',
            ]);
        }

        $material = $materialId
            ? Material::find($materialId)
            : Material::where('codigo_barras', $codigoBarras)->first();

        if (!$material) {
            throw ValidationException::withMessages([
                'codigo_barras' => 'No encontramos un material con ese código. Puedes buscarlo manualmente.',
            ]);
        }

        $cantidad = (int) $datos['cantidad'];

        DB::transaction(function () use ($material, $cantidad, $codigoBarras, $datos, $request) {
            $materialBloqueado = Material::query()
                ->whereKey($material->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($cantidad > $materialBloqueado->stock) {
                throw ValidationException::withMessages([
                    'cantidad' => "No hay stock suficiente. Disponible: {$materialBloqueado->stock} pzas.",
                ]);
            }

            $stockAnterior = $materialBloqueado->stock;
            $stockNuevo = $stockAnterior - $cantidad;

            $materialBloqueado->update([
                'stock' => $stockNuevo,
            ]);

            MaterialMovimiento::create([
                'material_id' => $materialBloqueado->id,
                'user_id' => $request->user()?->id,
                'tipo' => 'salida',
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'codigo_barras' => $codigoBarras !== ''
                    ? $codigoBarras
                    : $materialBloqueado->codigo_barras,
                'referencia' => $datos['referencia'] ?? null,
                'motivo' => $datos['motivo'] ?? null,
            ]);
        });

        return redirect()
            ->route('materiales.salidas.create')
            ->with(
                'success',
                "Salida registrada: {$cantidad} pzas de {$material->descripcion}. Stock actualizado."
            );
    }
}
