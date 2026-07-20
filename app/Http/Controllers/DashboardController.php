<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialEntradaPendiente;
use App\Models\MaterialMovimiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->esAdministrador(), 403, 'El dashboard gerencial solo esta disponible para administradores.');

        $inicioMes = Carbon::now()->startOfMonth();

        $consumoMensual = MaterialMovimiento::query()
            ->select('material_id', DB::raw('SUM(cantidad) as total'))
            ->with('material:id,descripcion')
            ->whereHas('material', fn ($query) => $query->where('es_plantilla_equipo', false))
            ->where('tipo', 'salida')
            ->where('created_at', '>=', $inicioMes)
            ->groupBy('material_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $valorPorCategoria = Material::query()
            ->where('es_plantilla_equipo', false)
            ->select('categoria', DB::raw('SUM(stock * costo_unitario) as total'))
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();

        $stockCritico = Material::query()
            ->where('es_plantilla_equipo', false)
            ->where('stock_minimo', '>', 0)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->limit(10)
            ->get();

        $salidasMes = MaterialMovimiento::query()
            ->whereHas('material', fn ($query) => $query->where('es_plantilla_equipo', false))
            ->where('tipo', 'salida')
            ->where('created_at', '>=', $inicioMes)
            ->sum('cantidad');

        $topProveedoresCompras = MaterialMovimiento::query()
            ->join('materials', 'materials.id', '=', 'material_movimientos.material_id')
            ->where('materials.es_plantilla_equipo', false)
            ->where('material_movimientos.tipo', 'entrada')
            ->whereRaw("COALESCE(NULLIF(material_movimientos.proveedor, ''), NULLIF(materials.proveedor, '')) IS NOT NULL")
            ->selectRaw("COALESCE(NULLIF(material_movimientos.proveedor, ''), materials.proveedor) as proveedor")
            ->selectRaw('SUM(material_movimientos.cantidad * COALESCE(material_movimientos.costo_unitario, materials.costo_unitario, 0)) as total')
            ->selectRaw('SUM(material_movimientos.cantidad) as piezas')
            ->groupByRaw("COALESCE(NULLIF(material_movimientos.proveedor, ''), materials.proveedor)")
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $proveedoresCatalogo = Material::query()
            ->whereNotNull('proveedor')
            ->where('proveedor', '<>', '')
            ->select('proveedor', DB::raw('COUNT(*) as productos'))
            ->groupBy('proveedor')
            ->orderByDesc('productos')
            ->orderBy('proveedor')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'consumoLabels' => $consumoMensual
                ->map(fn (MaterialMovimiento $movimiento) => $movimiento->material?->descripcion ?? 'Material eliminado')
                ->values(),
            'consumoData' => $consumoMensual->pluck('total')->map(fn ($total) => (int) $total)->values(),
            'valorLabels' => $valorPorCategoria->pluck('categoria')->map(fn ($categoria) => $categoria ?: 'Sin categoría')->values(),
            'valorData' => $valorPorCategoria->pluck('total')->map(fn ($total) => round((float) $total, 2))->values(),
            'totalMateriales' => Material::where('es_plantilla_equipo', false)->count(),
            'stockTotal' => Material::where('es_plantilla_equipo', false)->sum('stock'),
            'valorInventario' => Material::query()->where('es_plantilla_equipo', false)->sum(DB::raw('stock * costo_unitario')),
            'stockCriticoTotal' => Material::query()
                ->where('es_plantilla_equipo', false)
                ->where('stock_minimo', '>', 0)
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->count(),
            'stockCritico' => $stockCritico,
            'salidasMes' => $salidasMes,
            'entradasPendientes' => MaterialEntradaPendiente::query()
                ->where('estado', 'pendiente')
                ->count(),
            'topProveedoresCompras' => $topProveedoresCompras,
            'proveedoresCatalogo' => $proveedoresCatalogo,
        ]);
    }
}
