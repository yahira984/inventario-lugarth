<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EtiquetaController extends Controller
{
    public function generar(Request $request, Material $material): RedirectResponse
    {
        $usuario = $request->user();

        abort_unless(
            $usuario instanceof User && $usuario->puedeAdministrarCatalogo(),
            403,
            'No tienes permiso para generar etiquetas.'
        );

        if ($material->codigo_barras) {
            return redirect()->route('materiales.etiqueta', $material);
        }

        $codigo = 'LUG-'.str_pad((string) $material->id, 6, '0', STR_PAD_LEFT).'-'.Str::upper(Str::random(4));
        $material->update(['codigo_barras' => $codigo]);

        return redirect()
            ->route('materiales.etiqueta', $material)
            ->with('success', 'Código QR generado correctamente.');
    }

    public function mostrar(Request $request, Material $material): View
    {
        $usuario = $request->user();

        abort_unless(
            $usuario instanceof User && $usuario->puedeMoverStock(),
            403,
            'No tienes permiso para ver etiquetas.'
        );

        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_DEPRECATED);

        try {
            $qrSvg = QrCode::format('svg')
                ->size(220)
                ->margin(1)
                ->generate($material->codigo_barras ?: (string) $material->id);
        } finally {
            error_reporting($errorReporting);
        }

        return view('materiales.etiqueta', [
            'material' => $material,
            'qrSvg' => $qrSvg,
        ]);
    }
}
