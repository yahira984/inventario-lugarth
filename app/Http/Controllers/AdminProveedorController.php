<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminProveedorController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->puedeAdministrarCatalogo(), 403);

        $proveedores = Material::query()
            ->select(
                DB::raw('COALESCE(NULLIF(proveedor, ""), "Sin proveedor") as proveedor_nombre'),
                DB::raw('MAX(proveedor_rfc) as proveedor_rfc'),
                DB::raw('COUNT(*) as productos'),
                DB::raw('SUM(stock) as piezas'),
                DB::raw('SUM(stock * costo_unitario) as valor')
            )
            ->groupBy('proveedor_nombre')
            ->orderBy('proveedor_nombre')
            ->paginate(25);

        return view('admin.proveedores.index', compact('proveedores'));
    }

    public function show(Request $request, string $proveedor): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $nombre = urldecode($proveedor);
        $materiales = Material::query()
            ->where(function ($query) use ($nombre) {
                if ($nombre === 'Sin proveedor') {
                    $query->whereNull('proveedor')->orWhere('proveedor', '');
                    return;
                }

                $query->where('proveedor', $nombre);
            })
            ->orderBy('descripcion')
            ->paginate(25)
            ->withQueryString();

        return view('admin.proveedores.show', [
            'proveedor' => $nombre,
            'materiales' => $materiales,
        ]);
    }
}
