<?php

namespace App\Http\Controllers;

use App\Models\Material;
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
            ->where('tipo', 'salida')
            ->where('created_at', '>=', $inicioMes)
            ->sum('cantidad');

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
        ]);
    }
}
