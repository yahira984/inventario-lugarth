<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialMovimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $inicioMes = Carbon::now()->startOfMonth();

        $consumoMensual = MaterialMovimiento::query()
            ->select('material_id', DB::raw('SUM(cantidad) as total'))
            ->with('material:id,descripcion')
            ->where('tipo', 'salida')
            ->where('created_at', '>=', $inicioMes)
            ->groupBy('material_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $valorPorCategoria = Material::query()
            ->select('categoria', DB::raw('SUM(stock * costo_unitario) as total'))
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();

        $stockCritico = Material::query()
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
            'totalMateriales' => Material::count(),
            'stockTotal' => Material::sum('stock'),
            'valorInventario' => Material::query()->sum(DB::raw('stock * costo_unitario')),
            'stockCriticoTotal' => Material::query()
                ->where('stock_minimo', '>', 0)
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->count(),
            'stockCritico' => $stockCritico,
            'salidasMes' => $salidasMes,
        ]);
    }
}
