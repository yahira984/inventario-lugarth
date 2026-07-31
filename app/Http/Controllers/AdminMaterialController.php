<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Material;
use App\Models\MaterialMovimiento;
use App\Models\MaterialSupplierPrice;
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

        $precios = MaterialSupplierPrice::query()
            ->where('material_id', $material->id)
            ->latest('registrado_en')
            ->latest('id')
            ->paginate(20, ['*'], 'precios_page');
        $seriePrecios = MaterialSupplierPrice::query()
            ->where('material_id', $material->id)
            ->orderBy('registrado_en')
            ->orderBy('id')
            ->limit(60)
            ->get();
        $resumenProveedores = $seriePrecios
            ->groupBy(fn (MaterialSupplierPrice $price): string => mb_strtolower($price->proveedor))
            ->map(function ($items): array {
                $latest = $items->last();

                return [
                    'proveedor' => $latest->proveedor,
                    'precio_actual' => (float) $latest->precio_unitario,
                    'registros' => $items->count(),
                    'ultima_fecha' => $latest->registrado_en,
                ];
            })
            ->sortBy('precio_actual')
            ->values();

        return view('admin.materiales.historial', compact(
            'material',
            'movimientos',
            'logs',
            'precios',
            'seriePrecios',
            'resumenProveedores',
        ));
    }
}
