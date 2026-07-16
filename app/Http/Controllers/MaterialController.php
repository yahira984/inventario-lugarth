<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialMovimiento;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Request $request): View
    {
        $query = Material::query();

        if (! ($request->boolean('ver_plantillas') && $this->usuarioPuedeAdministrarCatalogo($request))) {
            $query->where('es_plantilla_equipo', false);
        }

        if ($request->boolean('sin_codigo')) {
            $query->whereNull('codigo_barras');
        }

        if ($request->filled('filtrar_categoria')) {
            $query->where('categoria', $request->filtrar_categoria);
        }

        if ($request->filled('buscar')) {
            $termino = trim((string) $request->buscar);
            $query->where(function ($q) use ($termino) {
                $q->where('numero_parte', 'LIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'LIKE', "%{$termino}%")
                    ->orWhere('apodo', 'LIKE', "%{$termino}%")
                    ->orWhere('codigo_barras', 'LIKE', "%{$termino}%")
                    ->orWhere('marca', 'LIKE', "%{$termino}%")
                    ->orWhere('proveedor', 'LIKE', "%{$termino}%")
                    ->orWhere('almacen', 'LIKE', "%{$termino}%");
            });
        }

        $materiales = $query
            ->orderBy('descripcion')
            ->paginate(25)
            ->withQueryString();

        return view('materiales.index', [
            'materiales' => $materiales,
            'categorias' => $this->categoriasDisponibles(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->autorizarMoverStock($request, 'No tienes permiso para registrar entradas.');

        return view('materiales.create', [
            'categorias' => $this->categoriasDisponibles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->autorizarMoverStock($request, 'No tienes permiso para registrar entradas.');

        $codigo = trim((string) $request->input('codigo_barras', ''));

        if ($codigo !== '') {
            $material = Material::where('codigo_barras', $codigo)
                ->where('es_plantilla_equipo', false)
                ->first();

            if ($material) {
                $cantidad = max(0, (int) $request->input('stock', 0));

                if ($cantidad <= 0) {
                    throw ValidationException::withMessages([
                        'stock' => 'Ese codigo ya existe. Escribe cuantas piezas entraron para sumar stock.',
                    ]);
                }

                DB::transaction(function () use ($material, $cantidad, $request) {
                    $bloqueado = Material::whereKey($material->id)->lockForUpdate()->firstOrFail();
                    $anterior = $bloqueado->stock;
                    $nuevo = $anterior + $cantidad;
                    $bloqueado->update(['stock' => $nuevo]);

                    MaterialMovimiento::create([
                        'material_id' => $bloqueado->id,
                        'user_id' => $request->user()?->id,
                        'tipo' => 'entrada',
                        'cantidad' => $cantidad,
                        'stock_anterior' => $anterior,
                        'stock_nuevo' => $nuevo,
                        'codigo_barras' => $bloqueado->codigo_barras,
                        'referencia' => 'Entrada por codigo existente',
                        'motivo' => 'Registro de entrada',
                    ]);
                });

                AuditLogger::registrar('Inventario', 'Entrada', "Sumo {$cantidad} piezas a {$material->descripcion}.", [
                    'material_id' => $material->id,
                    'cantidad' => $cantidad,
                ], $request);

                return redirect()
                    ->route('materiales.index')
                    ->with('success', "Ese codigo ya existia. Se sumaron {$cantidad} piezas al stock.");
            }
        }

        if (! $this->usuarioPuedeAdministrarCatalogo($request)) {
            throw ValidationException::withMessages([
                'codigo_barras' => 'Este codigo no existe. Pide a un administrador registrar el material nuevo.',
            ]);
        }

        $datos = $this->validarMaterial($request);

        if ($request->hasFile('fotografia')) {
            $datos['fotografia'] = $this->comprimirYGuardar($request->file('fotografia'), 'materiales');
        }

        if ($request->hasFile('evidencia_foto')) {
            $datos['evidencia_foto'] = $this->comprimirYGuardar($request->file('evidencia_foto'), 'evidencias');
        }

        $material = Material::create($datos);

        if ($material->stock > 0) {
            MaterialMovimiento::create([
                'material_id' => $material->id,
                'user_id' => $request->user()?->id,
                'tipo' => 'entrada',
                'cantidad' => $material->stock,
                'stock_anterior' => 0,
                'stock_nuevo' => $material->stock,
                'codigo_barras' => $material->codigo_barras,
                'referencia' => 'Alta de material',
                'motivo' => 'Registro inicial',
            ]);
        }

        AuditLogger::registrar('Inventario', 'Alta de material', "Registro el material {$material->descripcion}.", [
            'material_id' => $material->id,
            'stock' => $material->stock,
        ], $request);

        return redirect()->route('materiales.index')->with('success', 'Material registrado correctamente en el almacen.');
    }

    public function edit(Request $request, Material $material): View
    {
        $this->autorizarAdministrarCatalogo($request, 'No tienes permiso para editar materiales.');

        return view('materiales.edit', [
            'material' => $material,
            'categorias' => $this->categoriasDisponibles(),
        ]);
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $this->autorizarAdministrarCatalogo($request, 'No tienes permiso para editar materiales.');

        $datos = $this->validarMaterial($request, $material);

        if ($request->hasFile('fotografia')) {
            if ($material->fotografia) {
                Storage::disk('public')->delete($material->fotografia);
            }

            $datos['fotografia'] = $this->comprimirYGuardar($request->file('fotografia'), 'materiales');
        }

        if ($request->hasFile('evidencia_foto')) {
            if ($material->evidencia_foto) {
                Storage::disk('public')->delete($material->evidencia_foto);
            }

            $datos['evidencia_foto'] = $this->comprimirYGuardar($request->file('evidencia_foto'), 'evidencias');
        }

        $material->update($datos);

        AuditLogger::registrar('Inventario', 'Edicion de material', "Actualizo datos de {$material->descripcion}.", [
            'material_id' => $material->id,
        ], $request);

        return redirect()->route('materiales.index')->with('success', 'Material actualizado correctamente.');
    }

    public function destroy(Request $request, Material $material): RedirectResponse
    {
        $this->autorizarAdministrarCatalogo($request, 'No tienes permiso para eliminar materiales.');

        $descripcion = $material->descripcion;

        if ($material->fotografia) {
            Storage::disk('public')->delete($material->fotografia);
        }

        if ($material->evidencia_foto) {
            Storage::disk('public')->delete($material->evidencia_foto);
        }

        $material->delete();

        AuditLogger::registrar('Inventario', 'Eliminacion de material', "Elimino el material {$descripcion}.", [
            'material_id' => $material->id,
        ], $request);

        return redirect()->route('materiales.index')->with('success', 'Material eliminado correctamente.');
    }

    public function guardarCodigoBarras(Request $request, Material $material): RedirectResponse
    {
        $this->autorizarAdministrarCatalogo($request, 'No tienes permiso para agregar codigos de barras.');

        $datos = $request->validate([
            'codigo_barras' => [
                'required',
                'string',
                'max:255',
                Rule::unique('materials', 'codigo_barras')->ignore($material),
            ],
        ], [
            'codigo_barras.required' => 'Escribe o escanea el codigo de barras del producto.',
            'codigo_barras.unique' => 'Ese codigo de barras ya esta registrado en otro material.',
            'codigo_barras.max' => 'El codigo de barras no debe tener mas de 255 caracteres.',
        ]);

        $codigo = trim((string) $datos['codigo_barras']);
        $material->update(['codigo_barras' => $codigo]);

        AuditLogger::registrar('Inventario', 'Codigo de barras', "Agrego el codigo de barras a {$material->descripcion}.", [
            'material_id' => $material->id,
            'codigo_barras' => $codigo,
        ], $request);

        return back()->with('success', "Codigo de barras agregado a {$material->descripcion}.");
    }

    public function buscarPorCodigo(Request $request)
    {
        $materiales = Material::where('codigo_barras', $request->codigo)
            ->where('es_plantilla_equipo', false)
            ->orderBy('categoria')
            ->orderBy('descripcion')
            ->limit(10)
            ->get();

        if ($materiales->count() > 1) {
            return response()->json([
                'encontrado' => false,
                'multiples' => true,
                'mensaje' => 'Ese codigo aparece en varios materiales. Selecciona el producto manualmente para elegir la categoria correcta.',
                'resultados' => $materiales->map(fn (Material $material) => [
                    'id' => $material->id,
                    'categoria' => $material->categoria,
                    'almacen' => $material->almacen,
                    'numero_parte' => $material->numero_parte,
                    'codigo_barras' => $material->codigo_barras,
                    'descripcion' => $material->descripcion,
                    'apodo' => $material->apodo,
                    'marca' => $material->marca,
                    'proveedor' => $material->proveedor,
                    'stock' => $material->stock,
                    'fotografia' => $material->fotografia,
                ])->values(),
            ]);
        }

        $material = $materiales->first();

        if ($material) {
            return response()->json([
                'encontrado' => true,
                'id' => $material->id,
                'categoria' => $material->categoria,
                'almacen' => $material->almacen,
                'numero_parte' => $material->numero_parte,
                'codigo_barras' => $material->codigo_barras,
                'descripcion' => $material->descripcion,
                'apodo' => $material->apodo,
                'marca' => $material->marca,
                'proveedor' => $material->proveedor,
                'stock' => $material->stock,
                'fotografia' => $material->fotografia,
            ]);
        }

        return response()->json(['encontrado' => false]);
    }

    private function validarMaterial(Request $request, ?Material $material = null): array
    {
        $datos = $request->validate([
            'categoria' => ['nullable', 'string', 'max:255'],
            'almacen' => ['nullable', 'string', 'max:120'],
            'codigo_barras' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('materials', 'codigo_barras')->ignore($material),
            ],
            'numero_parte' => ['nullable', 'string', 'max:255'],
            'clave_sat' => ['nullable', 'string', 'max:30'],
            'clave_unidad' => ['nullable', 'string', 'max:30'],
            'unidad' => ['nullable', 'string', 'max:80'],
            'descripcion' => ['required', 'string'],
            'apodo' => ['nullable', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'proveedor' => ['nullable', 'string', 'max:255'],
            'proveedor_rfc' => ['nullable', 'string', 'max:20'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'stock_maximo' => ['nullable', 'integer', 'min:0'],
            'costo_unitario' => ['nullable', 'numeric', 'min:0'],
            'moneda' => ['nullable', 'string', 'max:10'],
            'fotografia' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'evidencia_foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ], [
            'codigo_barras.unique' => 'Ese codigo de barras ya pertenece a otro material.',
            'descripcion.required' => 'Escribe la descripcion del material.',
            'stock.integer' => 'El stock debe ser un numero entero.',
            'stock.min' => 'El stock no puede ser negativo.',
            'stock_minimo.integer' => 'El stock minimo debe ser un numero entero.',
            'stock_maximo.integer' => 'El stock maximo debe ser un numero entero.',
            'costo_unitario.numeric' => 'El precio por unidad debe ser numerico.',
        ]);

        $datos['codigo_barras'] = trim((string) ($datos['codigo_barras'] ?? '')) ?: null;
        $datos['categoria'] = trim((string) ($datos['categoria'] ?? '')) ?: ($material?->categoria ?: 'Sin categoria');
        $datos['stock'] = array_key_exists('stock', $datos) && $datos['stock'] !== null
            ? (int) $datos['stock']
            : (int) ($material?->stock ?? 0);
        $datos['stock_minimo'] = array_key_exists('stock_minimo', $datos) && $datos['stock_minimo'] !== null
            ? (int) $datos['stock_minimo']
            : (int) ($material?->stock_minimo ?? 0);
        $datos['stock_maximo'] = array_key_exists('stock_maximo', $datos) && $datos['stock_maximo'] !== null
            ? (int) $datos['stock_maximo']
            : (int) ($material?->stock_maximo ?? 0);
        $datos['costo_unitario'] = array_key_exists('costo_unitario', $datos) && $datos['costo_unitario'] !== null
            ? (float) $datos['costo_unitario']
            : (float) ($material?->costo_unitario ?? 0);
        $datos['moneda'] = trim((string) ($datos['moneda'] ?? '')) ?: ($material?->moneda ?: 'MXN');

        return $datos;
    }

    private function categoriasDisponibles()
    {
        return MaterialCategory::query()
            ->where('activa', true)
            ->orderBy('nombre')
            ->pluck('nombre')
            ->merge(Material::query()->where('es_plantilla_equipo', false)->whereNotNull('categoria')->distinct()->orderBy('categoria')->pluck('categoria'))
            ->map(fn ($categoria) => trim((string) $categoria))
            ->filter()
            ->unique(fn ($categoria) => strtoupper($categoria))
            ->values();
    }

    private function autorizarMoverStock(Request $request, string $mensaje): void
    {
        /** @var \App\Models\User|null $usuario */
        $usuario = $request->user();

        abort_unless($usuario?->puedeMoverStock(), 403, $mensaje);
    }

    private function autorizarAdministrarCatalogo(Request $request, string $mensaje): void
    {
        abort_unless($this->usuarioPuedeAdministrarCatalogo($request), 403, $mensaje);
    }

    private function usuarioPuedeAdministrarCatalogo(Request $request): bool
    {
        /** @var \App\Models\User|null $usuario */
        $usuario = $request->user();

        return (bool) $usuario?->puedeAdministrarCatalogo();
    }

    private function comprimirYGuardar($imagen, string $carpetaDestino): string
    {
        $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
        $rutaAbsoluta = storage_path('app/public/' . $carpetaDestino);

        if (! file_exists($rutaAbsoluta)) {
            mkdir($rutaAbsoluta, 0777, true);
        }

        $rutaFinal = $rutaAbsoluta . '/' . $nombreImagen;
        $info = getimagesize($imagen->getRealPath());

        if (($info['mime'] ?? '') === 'image/jpeg' || ($info['mime'] ?? '') === 'image/jpg') {
            $img = imagecreatefromjpeg($imagen->getRealPath());
            imagejpeg($img, $rutaFinal, 70);
            imagedestroy($img);
        } elseif (($info['mime'] ?? '') === 'image/png') {
            $img = imagecreatefrompng($imagen->getRealPath());
            imagepng($img, $rutaFinal, 6);
            imagedestroy($img);
        } else {
            $imagen->storeAs($carpetaDestino, $nombreImagen, 'public');
        }

        return $carpetaDestino . '/' . $nombreImagen;
    }
}
