<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\EquipmentPackage;
use App\Models\Material;
use App\Models\MaterialMovimiento;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $like = '%' . addcslashes($query, '%_\\') . '%';
        $results = collect();

        Material::query()
            ->where('es_plantilla_equipo', false)
            ->where(function ($builder) use ($like): void {
                $builder->where('descripcion', 'like', $like)
                    ->orWhere('apodo', 'like', $like)
                    ->orWhere('numero_parte', 'like', $like)
                    ->orWhere('codigo_barras', 'like', $like)
                    ->orWhere('categoria', 'like', $like)
                    ->orWhere('almacen', 'like', $like);
            })
            ->orderBy('descripcion')
            ->limit(8)
            ->get()
            ->each(function (Material $material) use ($results): void {
                $inventoryTerm = $material->codigo_barras
                    ?: ($material->numero_parte ?: $material->descripcion);

                $results->push([
                    'type' => 'Material',
                    'title' => $material->nombreBusqueda(),
                    'meta' => trim(implode(' · ', array_filter([
                        $material->numero_parte,
                        $material->categoria,
                        $material->almacen,
                        $material->stock . ' ' . ($material->unidad ?: 'pzas'),
                    ]))),
                    'url' => route('materiales.index', [
                        'material_id' => $material->id,
                        'buscar' => $inventoryTerm,
                        'destacar' => $material->id,
                    ]) . '#material-' . $material->id,
                    'tone' => 'blue',
                ]);
            });

        if ($request->user()?->puedeMoverStock()) {
            EquipmentPackage::query()
                ->where(function ($builder) use ($like): void {
                    $builder->where('nombre', 'like', $like)
                        ->orWhere('codigo', 'like', $like)
                        ->orWhere('descripcion', 'like', $like);
                })
                ->orderBy('nombre')
                ->limit(5)
                ->get()
                ->each(function (EquipmentPackage $equipo) use ($results): void {
                    $results->push([
                        'type' => 'Equipo',
                        'title' => $equipo->nombre,
                        'meta' => $equipo->codigo ?: 'Sin codigo interno',
                        'url' => route('equipos.show', $equipo),
                        'tone' => 'purple',
                    ]);
                });
        }

        if ($request->user()?->puedeAdministrarCatalogo()) {
            Material::query()
                ->whereNotNull('proveedor')
                ->where('proveedor', '<>', '')
                ->where('proveedor', 'like', $like)
                ->select('proveedor')
                ->selectRaw('COUNT(*) as productos')
                ->groupBy('proveedor')
                ->orderBy('proveedor')
                ->limit(5)
                ->get()
                ->each(function ($proveedor) use ($results): void {
                    $results->push([
                        'type' => 'Proveedor',
                        'title' => $proveedor->proveedor,
                        'meta' => $proveedor->productos . ' materiales relacionados',
                        'url' => route('admin.proveedores.show', ['proveedor' => $proveedor->proveedor]),
                        'tone' => 'amber',
                    ]);
                });

            User::query()
                ->where(function ($builder) use ($like): void {
                    $builder->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->orderBy('name')
                ->limit(5)
                ->get()
                ->each(function (User $user) use ($results): void {
                    $results->push([
                        'type' => 'Usuario',
                        'title' => $user->name,
                        'meta' => $user->email . ' · ' . Str::headline($user->role),
                        'url' => route('usuarios.roles.index') . '#usuario-' . $user->id,
                        'tone' => 'indigo',
                    ]);
                });

            MaterialMovimiento::query()
                ->with(['material:id,descripcion', 'user:id,name'])
                ->where(function ($builder) use ($like): void {
                    $builder->where('referencia', 'like', $like)
                        ->orWhere('motivo', 'like', $like)
                        ->orWhere('codigo_barras', 'like', $like);
                })
                ->latest()
                ->limit(5)
                ->get()
                ->each(function (MaterialMovimiento $movimiento) use ($results): void {
                    $results->push([
                        'type' => 'Movimiento',
                        'title' => ucfirst($movimiento->tipo) . ': ' . ($movimiento->material?->descripcion ?? 'Material eliminado'),
                        'meta' => ($movimiento->referencia ?: 'Sin referencia') . ' · ' . $movimiento->created_at?->format('d/m/Y H:i'),
                        'url' => route('admin.auditoria.index', ['buscar' => $movimiento->referencia ?: $movimiento->codigo_barras]),
                        'tone' => $movimiento->tipo === 'salida' ? 'red' : 'green',
                    ]);
                });

            AuditLog::query()
                ->where(function ($builder) use ($like): void {
                    $builder->where('descripcion', 'like', $like)
                        ->orWhere('accion', 'like', $like)
                        ->orWhere('modulo', 'like', $like);
                })
                ->latest()
                ->limit(3)
                ->get()
                ->each(function (AuditLog $log) use ($results): void {
                    $results->push([
                        'type' => 'Auditoria',
                        'title' => $log->accion,
                        'meta' => $log->modulo . ' · ' . Str::limit($log->descripcion, 90),
                        'url' => route('admin.auditoria.index', ['buscar' => $log->accion]),
                        'tone' => 'slate',
                    ]);
                });
        }

        return response()->json(['results' => $results->take(20)->values()]);
    }
}
