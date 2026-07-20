<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Material;
use App\Models\MaterialMovimiento;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMaterialController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $buscar = trim((string) $request->query('buscar', ''));

        $materiales = Material::query()
            ->where('es_plantilla_equipo', false)
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('descripcion', 'LIKE', "%{$buscar}%")
                        ->orWhere('numero_parte', 'LIKE', "%{$buscar}%")
                        ->orWhere('codigo_barras', 'LIKE', "%{$buscar}%")
                        ->orWhere('apodo', 'LIKE', "%{$buscar}%")
                        ->orWhere('clave_sat', 'LIKE', "%{$buscar}%")
                        ->orWhere('proveedor', 'LIKE', "%{$buscar}%")
                        ->orWhere('almacen', 'LIKE', "%{$buscar}%");
                });
            })
            ->orderBy('descripcion')
            ->paginate(25)
            ->withQueryString();

        return view('admin.materiales.index', compact('materiales', 'buscar'));
    }

    public function historial(Request $request, Material $material): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        abort_if($material->es_plantilla_equipo, 404);

        $movimientos = MaterialMovimiento::query()
            ->with('user')
            ->where('material_id', $material->id)
            ->latest()
            ->paginate(25, ['*'], 'movimientos_page');

        $logs = AuditLog::query()
            ->with('user')
            ->where('datos->material_id', $material->id)
            ->latest()
            ->paginate(20, ['*'], 'logs_page');

        return view('admin.materiales.historial', compact('material', 'movimientos', 'logs'));
    }
}
