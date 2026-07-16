<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialMovimiento;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function inventarioCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_inventario.csv"',
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Categoria', 'Almacen', 'No. Parte', 'Codigo Barras', 'Clave SAT', 'Unidad', 'Descripcion', 'Marca', 'Proveedor', 'RFC Proveedor', 'Stock', 'Stock minimo', 'Stock maximo', 'Costo unitario', 'Moneda', 'Valor']);

            Material::query()->where('es_plantilla_equipo', false)->orderBy('descripcion')->chunk(200, function ($materiales) use ($out) {
                foreach ($materiales as $material) {
                    fputcsv($out, [
                        $material->categoria,
                        $material->almacen,
                        $material->numero_parte,
                        $material->codigo_barras,
                        $material->clave_sat,
                        $material->unidad,
                        $material->descripcion,
                        $material->marca,
                        $material->proveedor,
                        $material->proveedor_rfc,
                        $material->stock,
                        $material->stock_minimo,
                        $material->stock_maximo,
                        $material->costo_unitario,
                        $material->moneda,
                        (float) $material->stock * (float) $material->costo_unitario,
                    ]);
                }
            });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function salidasCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_salidas.csv"',
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Fecha', 'Material', 'No. Parte', 'Codigo', 'Cantidad', 'Stock anterior', 'Stock nuevo', 'Referencia', 'Motivo', 'Usuario']);

            MaterialMovimiento::query()
                ->with(['material', 'user'])
                ->where('tipo', 'salida')
                ->latest()
                ->chunk(200, function ($salidas) use ($out) {
                    foreach ($salidas as $salida) {
                        fputcsv($out, [
                            $salida->created_at?->format('d/m/Y H:i'),
                            $salida->material?->descripcion,
                            $salida->material?->numero_parte,
                            $salida->codigo_barras,
                            $salida->cantidad,
                            $salida->stock_anterior,
                            $salida->stock_nuevo,
                            $salida->referencia,
                            $salida->motivo,
                            $salida->user?->name,
                        ]);
                    }
                });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function inventarioPdf(): View
    {
        return view('reportes.inventario_pdf', [
            'materiales' => Material::query()->where('es_plantilla_equipo', false)->orderBy('descripcion')->get(),
        ]);
    }

    public function salidasPdf(): View
    {
        return view('reportes.salidas_pdf', [
            'salidas' => MaterialMovimiento::query()
                ->with(['material', 'user'])
                ->where('tipo', 'salida')
                ->latest()
                ->limit(500)
                ->get(),
        ]);
    }
}
