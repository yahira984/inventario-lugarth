<?php

namespace App\Http\Controllers;

use App\Models\MaterialMovimiento;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSalidaController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $salidas = MaterialMovimiento::query()
            ->with(['material', 'user'])
            ->where('tipo', 'salida')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.salidas.index', compact('salidas'));
    }
}
