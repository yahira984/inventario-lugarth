<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMaterialController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $buscar = trim((string) $request->query('buscar', ''));

        $materiales = Material::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('descripcion', 'LIKE', "%{$buscar}%")
                        ->orWhere('numero_parte', 'LIKE', "%{$buscar}%")
                        ->orWhere('codigo_barras', 'LIKE', "%{$buscar}%")
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
}
