<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    /**
     * Mostrar el listado de materiales.
     */
    public function index(Request $request)
    {
        $query = Material::query();

        // Filtro por categoría
        if ($request->filled('filtrar_categoria')) {
            $query->where('categoria', $request->filtrar_categoria);
        }

        // Buscador por número de parte, código de barras o descripción
        if ($request->boolean('sin_codigo')) {
            $query->where(function ($q) {
                $q->whereNull('codigo_barras')
                    ->orWhere('codigo_barras', '');
            });
        }

        if ($request->filled('buscar')) {
            $termino = trim($request->buscar);

            $query->where(function ($q) use ($termino) {
                $q->where('numero_parte', 'LIKE', '%' . $termino . '%')
                    ->orWhere('codigo_barras', 'LIKE', '%' . $termino . '%')
                    ->orWhere('descripcion', 'LIKE', '%' . $termino . '%');
            });
        }

        $materiales = $query
            ->orderBy('id', 'desc')
            ->get();

        return view('materiales.index', compact('materiales'));
    }

    /**
     * Mostrar el formulario para registrar un material.
     */
    public function create()
    {
        return view('materiales.create');
    }

    /**
     * Buscar un material usando su código de barras.
     */
    public function buscarPorCodigo(Request $request)
    {
        $codigo = trim((string) $request->query('codigo', ''));

        if ($codigo === '') {
            return response()->json([
                'encontrado' => false,
            ]);
        }

        $material = Material::where('codigo_barras', $codigo)->first();

        if (!$material) {
            return response()->json([
                'encontrado' => false,
            ]);
        }

        return response()->json([
            'encontrado' => true,
            'id' => $material->id,
            'categoria' => $material->categoria,
            'numero_parte' => $material->numero_parte,
            'codigo_barras' => $material->codigo_barras,
            'descripcion' => $material->descripcion,
            'marca' => $material->marca,
            'proveedor' => $material->proveedor,
            'stock' => $material->stock,
            'fotografia' => $material->fotografia,
        ]);
    }

    /**
     * Guardar un nuevo material.
     */
    public function store(Request $request)
    {
        /*
         * Primero revisamos si el código de barras ya pertenece
         * a un material registrado.
         */
        $entrada = $request->validate([
            'codigo_barras' => ['nullable', 'string', 'max:255'],
            'stock' => ['required', 'integer', 'min:0'],
        ], [
            'stock.required' => 'La cantidad o stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock no puede ser negativo.',
        ]);

        $codigoBarras = trim((string) ($entrada['codigo_barras'] ?? ''));

        $request->merge([
            'codigo_barras' => $codigoBarras !== '' ? $codigoBarras : null,
        ]);

        /*
         * Si el material ya existe, solamente incrementamos su stock.
         */
        if ($codigoBarras !== '') {
            $materialExistente = Material::where(
                'codigo_barras',
                $codigoBarras
            )->first();

            if ($materialExistente) {
                $cantidadEntrada = (int) $entrada['stock'];

                $materialExistente->increment(
                    'stock',
                    $cantidadEntrada
                );

                $materialExistente->refresh();

                return redirect()
                    ->route('materiales.index')
                    ->with(
                        'success',
                        "Entrada registrada: se agregaron {$cantidadEntrada} piezas a {$materialExistente->descripcion}. Stock actual: {$materialExistente->stock} piezas."
                    );
            }
        }

        $tablaMateriales = (new Material())->getTable();

        /*
         * Si el código no existe, registramos un material nuevo.
         */
        $datos = $request->validate([
            'categoria' => ['required', 'string', 'max:255'],
            'numero_parte' => ['nullable', 'string', 'max:255'],
            'codigo_barras' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique($tablaMateriales, 'codigo_barras'),
            ],
            'descripcion' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'proveedor' => ['nullable', 'string', 'max:255'],
            'stock' => ['required', 'integer', 'min:0'],
            'fotografia' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
            ],
        ], [
            'categoria.required' => 'La categoría es obligatoria.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'codigo_barras.unique' => 'Este código de barras ya está registrado.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock no puede ser negativo.',
            'fotografia.image' => 'El archivo seleccionado debe ser una imagen.',
            'fotografia.mimes' => 'La fotografía debe ser JPG, JPEG, PNG o WEBP.',
            'fotografia.max' => 'La fotografía no debe pesar más de 2 MB.',
        ]);

        if ($request->hasFile('fotografia')) {
            $datos['fotografia'] = $request
                ->file('fotografia')
                ->store('materiales', 'public');
        }

        Material::create($datos);

        return redirect()
            ->route('materiales.index')
            ->with(
                'success',
                'Material registrado correctamente en el almacén.'
            );
    }

    /**
     * Mostrar la información de un material.
     */
    public function show(Material $material)
    {
        return view('materiales.show', compact('material'));
    }

    /**
     * Mostrar el formulario de edición.
     */
    public function edit(Material $material)
    {
        return view('materiales.edit', compact('material'));
    }

    /**
     * Actualizar un material.
     */
    public function update(Request $request, Material $material)
    {
        /*
         * Convertimos un código vacío en null.
         */
        $codigoBarras = trim(
            (string) $request->input('codigo_barras', '')
        );

        $request->merge([
            'codigo_barras' => $codigoBarras !== ''
                ? $codigoBarras
                : null,
        ]);

        $tablaMateriales = $material->getTable();

        $datos = $request->validate([
            'categoria' => ['required', 'string', 'max:255'],
            'numero_parte' => ['nullable', 'string', 'max:255'],
            'codigo_barras' => [
                'nullable',
                'string',
                'max:255',

                /*
                 * Permite conservar el código actual del material,
                 * pero evita utilizar el código de otro material.
                 */
                Rule::unique($tablaMateriales, 'codigo_barras')
                    ->ignore($material->id),
            ],
            'descripcion' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'proveedor' => ['nullable', 'string', 'max:255'],
            'stock' => ['required', 'integer', 'min:0'],
            'fotografia' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
            ],
        ], [
            'categoria.required' => 'La categoría es obligatoria.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'codigo_barras.unique' => 'Este código de barras pertenece a otro material.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock no puede ser negativo.',
            'fotografia.image' => 'El archivo seleccionado debe ser una imagen.',
            'fotografia.mimes' => 'La fotografía debe ser JPG, JPEG, PNG o WEBP.',
            'fotografia.max' => 'La fotografía no debe pesar más de 2 MB.',
        ]);

        /*
         * Si seleccionaron una fotografía nueva,
         * eliminamos la anterior y guardamos la nueva.
         */
        if ($request->hasFile('fotografia')) {
            if (
                $material->fotografia &&
                Storage::disk('public')->exists($material->fotografia)
            ) {
                Storage::disk('public')->delete(
                    $material->fotografia
                );
            }

            $datos['fotografia'] = $request
                ->file('fotografia')
                ->store('materiales', 'public');
        }

        $material->update($datos);

        return redirect()
            ->route('materiales.index')
            ->with(
                'success',
                'Material actualizado correctamente.'
            );
    }

    /**
     * Eliminar un material.
     */
    public function destroy(Material $material)
    {
        /*
         * Eliminamos la fotografía del almacenamiento,
         * siempre que exista.
         */
        if (
            $material->fotografia &&
            Storage::disk('public')->exists($material->fotografia)
        ) {
            Storage::disk('public')->delete(
                $material->fotografia
            );
        }

        $material->delete();

        return redirect()
            ->route('materiales.index')
            ->with(
                'success',
                'Material eliminado del inventario.'
            );
    }
}
