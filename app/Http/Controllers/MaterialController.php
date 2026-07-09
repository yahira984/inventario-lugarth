<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Empezamos una consulta base
        $query = Material::query();

        // Si el usuario seleccionó una categoría en el filtro, filtramos los resultados
        if ($request->has('filtrar_categoria') && $request->filtrar_categoria != '') {
            $query->where('categoria', $request->filtrar_categoria);
        }

        // Obtenemos los materiales ya filtrados (o todos si no se seleccionó filtro)
        $materiales = $query->get(); 

        return view('materiales.index', compact('materiales')); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Esta vista la puedes armar después con calma
        return view('materiales.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'categoria'    => 'required|string', // Validación de categoría
            'numero_parte' => 'nullable|string',
            'descripcion'  => 'required|string',
            'marca'        => 'nullable|string',
            'proveedor'    => 'nullable|string',
            'stock'        => 'required|integer',
            'fotografia'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('fotografia')) {
            // Guarda la imagen en storage/app/public/materiales
            $rutaImagen = $request->file('fotografia')->store('materiales', 'public');
            $data['fotografia'] = $rutaImagen;
        }

        Material::create($data);

        return redirect()->route('materiales.index')->with('success', 'Material registrado correctamente en el almacén.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
        return view('materiales.show', compact('material'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Material $material)
    {
        return view('materiales.edit', compact('material'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Material $material)
    {
        $request->validate([
            'categoria'    => 'required|string', // Validación de categoría para cuando edites
            'numero_parte' => 'nullable|string',
            'descripcion'  => 'required|string',
            'marca'        => 'nullable|string',
            'proveedor'    => 'nullable|string',
            'stock'        => 'required|integer',
            'fotografia'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('fotografia')) {
            // Si el material ya tenía una foto local, la borramos para no hacer basura
            if ($material->fotografia) {
                Storage::disk('public')->delete($material->fotografia);
            }
            
            // Guardamos la nueva foto
            $rutaImagen = $request->file('fotografia')->store('materiales', 'public');
            $data['fotografia'] = $rutaImagen;
        }

        $material->update($data);

        return redirect()->route('materiales.index')->with('success', 'Material actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        // Eliminar la imagen del almacenamiento local antes de borrar el registro de la base de datos
        if ($material->fotografia) {
            Storage::disk('public')->delete($material->fotografia);
        }
        
        $material->delete();

        return redirect()->route('materiales.index')->with('success', 'Material eliminado del inventario.');
    }
}