<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialCategoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403, 'No tienes permiso para administrar categorias.');

        $usoPorCategoria = Material::query()
            ->select('categoria', DB::raw('COUNT(*) as total'))
            ->where('es_plantilla_equipo', false)
            ->whereNotNull('categoria')
            ->where('categoria', 'not like', 'EQUIPO%')
            ->groupBy('categoria')
            ->pluck('total', 'categoria');

        $categorias = MaterialCategory::query()
            ->where('nombre', 'not like', 'EQUIPO%')
            ->orderByDesc('activa')
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return view('admin.categorias.index', [
            'categorias' => $categorias,
            'usoPorCategoria' => $usoPorCategoria,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403, 'No tienes permiso para crear categorias.');

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:material_categories,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.required' => 'Escribe el nombre de la categoria.',
            'nombre.unique' => 'Esa categoria ya existe.',
        ]);

        $categoria = MaterialCategory::create([
            'nombre' => trim($datos['nombre']),
            'descripcion' => $datos['descripcion'] ?? null,
            'activa' => true,
        ]);

        AuditLogger::registrar('Categorias', 'Alta de categoria', "Creo la categoria {$categoria->nombre}.", [
            'categoria_id' => $categoria->id,
        ], $request);

        return back()->with('success', 'Categoria creada correctamente.');
    }

    public function update(Request $request, MaterialCategory $categoria): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403, 'No tienes permiso para editar categorias.');

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('material_categories', 'nombre')->ignore($categoria)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activa' => ['nullable', 'boolean'],
        ], [
            'nombre.required' => 'Escribe el nombre de la categoria.',
            'nombre.unique' => 'Esa categoria ya existe.',
        ]);

        $nombreAnterior = $categoria->nombre;

        $categoria->update([
            'nombre' => trim($datos['nombre']),
            'descripcion' => $datos['descripcion'] ?? null,
            'activa' => $request->boolean('activa'),
        ]);

        if ($nombreAnterior !== $categoria->nombre) {
            Material::where('categoria', $nombreAnterior)->update(['categoria' => $categoria->nombre]);
        }

        AuditLogger::registrar('Categorias', 'Edicion de categoria', "Actualizo la categoria {$categoria->nombre}.", [
            'categoria_id' => $categoria->id,
            'nombre_anterior' => $nombreAnterior,
        ], $request);

        return back()->with('success', 'Categoria actualizada correctamente.');
    }

    public function destroy(Request $request, MaterialCategory $categoria): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403, 'No tienes permiso para eliminar categorias.');

        $enUso = Material::where('categoria', $categoria->nombre)->count();

        if ($enUso > 0) {
            return back()->withErrors([
                'categoria' => "No se puede eliminar porque hay {$enUso} materiales usando esta categoria. Puedes desactivarla.",
            ]);
        }

        $nombre = $categoria->nombre;
        $categoria->delete();

        AuditLogger::registrar('Categorias', 'Eliminacion de categoria', "Elimino la categoria {$nombre}.", [], $request);

        return back()->with('success', 'Categoria eliminada correctamente.');
    }
}
