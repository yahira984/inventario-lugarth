<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialEntradaPendiente;
use App\Models\MaterialMovimiento;
use App\Support\AuditLogger;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminEntradaPendienteController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $estado = $request->query('estado', 'pendiente');

        $entradas = MaterialEntradaPendiente::query()
            ->with(['material', 'user', 'approver', 'rejecter'])
            ->when($estado !== 'todas', fn ($query) => $query->where('estado', $estado))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.entradas.index', compact('entradas', 'estado'));
    }

    public function edit(Request $request, MaterialEntradaPendiente $entrada): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);
        abort_if($entrada->estado !== 'pendiente', 409, 'Solo se pueden corregir entradas pendientes.');

        $entrada->load(['material', 'user']);

        return view('admin.entradas.edit', [
            'entrada' => $entrada,
            'materiales' => Material::query()
                ->where('es_plantilla_equipo', false)
                ->orderBy('descripcion')
                ->get(['id', 'descripcion', 'apodo', 'numero_parte', 'categoria', 'almacen', 'stock', 'fotografia']),
            'categorias' => $this->categoriasDisponibles(),
        ]);
    }

    public function update(Request $request, MaterialEntradaPendiente $entrada): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        if ($entrada->estado !== 'pendiente') {
            return back()->withErrors(['entrada' => 'Esta entrada ya fue revisada y no puede modificarse.']);
        }

        $datos = $this->validarCorreccion($request, $entrada);
        $nuevaEvidencia = $request->hasFile('evidencia_foto')
            ? ImageStorage::storeOptimized($request->file('evidencia_foto'), 'entradas-pendientes', 1600, 72)
            : null;
        $nuevaFotografia = $entrada->es_material_nuevo && $request->hasFile('fotografia')
            ? ImageStorage::storeOptimized($request->file('fotografia'), 'entradas-pendientes/materiales', 1600, 72)
            : null;
        $evidenciaAnterior = $entrada->evidencia_foto;
        $fotografiaAnterior = $entrada->fotografia;
        $antes = [
            'material_id' => $entrada->material_id,
            'cantidad' => $entrada->cantidad,
            'codigo_barras' => $entrada->codigo_barras,
            'proveedor' => $entrada->proveedor,
            'costo_unitario' => $entrada->costo_unitario,
            'referencia' => $entrada->referencia,
            'motivo' => $entrada->motivo,
            'datos_material' => $entrada->datos_material,
        ];

        try {
            DB::transaction(function () use ($entrada, $datos, $nuevaEvidencia, $nuevaFotografia): void {
                $entradaBloqueada = MaterialEntradaPendiente::query()
                    ->whereKey($entrada->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($entradaBloqueada->estado !== 'pendiente') {
                    throw ValidationException::withMessages([
                        'entrada' => 'Esta entrada ya fue revisada por otro administrador.',
                    ]);
                }

                $actualizacion = [
                    'cantidad' => (int) $datos['cantidad'],
                    'proveedor' => $this->textoOpcional($datos, 'proveedor'),
                    'costo_unitario' => (float) ($datos['costo_unitario'] ?? 0),
                    'referencia' => $this->textoOpcional($datos, 'referencia'),
                    'motivo' => $this->textoOpcional($datos, 'motivo'),
                    'comentario_admin' => $this->textoOpcional($datos, 'comentario_admin'),
                ];

                if ($nuevaEvidencia) {
                    $actualizacion['evidencia_foto'] = $nuevaEvidencia;
                }

                if ($entradaBloqueada->es_material_nuevo) {
                    $codigo = $this->textoOpcional($datos, 'codigo_barras');
                    $datosMaterial = array_merge($entradaBloqueada->datos_material ?? [], [
                        'categoria' => $this->textoOpcional($datos, 'categoria') ?: 'Sin categoria',
                        'almacen' => $this->textoOpcional($datos, 'almacen'),
                        'codigo_barras' => $codigo,
                        'numero_parte' => $this->textoOpcional($datos, 'numero_parte'),
                        'clave_sat' => $this->textoOpcional($datos, 'clave_sat'),
                        'clave_unidad' => $this->textoOpcional($datos, 'clave_unidad'),
                        'unidad' => $this->textoOpcional($datos, 'unidad'),
                        'descripcion' => trim((string) $datos['descripcion']),
                        'apodo' => $this->textoOpcional($datos, 'apodo'),
                        'marca' => $this->textoOpcional($datos, 'marca'),
                        'proveedor' => $this->textoOpcional($datos, 'proveedor'),
                        'proveedor_rfc' => $this->textoOpcional($datos, 'proveedor_rfc'),
                        'stock_minimo' => (int) ($datos['stock_minimo'] ?? 0),
                        'stock_maximo' => (int) ($datos['stock_maximo'] ?? 0),
                        'costo_unitario' => (float) ($datos['costo_unitario'] ?? 0),
                        'moneda' => $this->textoOpcional($datos, 'moneda') ?: 'MXN',
                    ]);

                    $actualizacion['codigo_barras'] = $codigo;
                    $actualizacion['datos_material'] = $datosMaterial;

                    if ($nuevaFotografia) {
                        $actualizacion['fotografia'] = $nuevaFotografia;
                    }
                } else {
                    $material = Material::query()
                        ->whereKey($datos['material_id'])
                        ->where('es_plantilla_equipo', false)
                        ->first();

                    if (! $material) {
                        throw ValidationException::withMessages([
                            'material_id' => 'Selecciona una pieza valida del inventario.',
                        ]);
                    }

                    $actualizacion['material_id'] = $material->id;
                    $actualizacion['codigo_barras'] = $material->codigo_barras;
                }

                $entradaBloqueada->update($actualizacion);
            });
        } catch (\Throwable $exception) {
            ImageStorage::delete($nuevaEvidencia);
            ImageStorage::delete($nuevaFotografia);

            throw $exception;
        }

        if ($nuevaEvidencia && $evidenciaAnterior !== $nuevaEvidencia) {
            ImageStorage::delete($evidenciaAnterior);
        }
        if ($nuevaFotografia && $fotografiaAnterior !== $nuevaFotografia) {
            ImageStorage::delete($fotografiaAnterior);
        }

        $entrada->refresh();

        AuditLogger::registrar('Entradas', 'Entrada corregida', "Corrigio la entrada pendiente #{$entrada->id} antes de aprobarla.", [
            'entrada_pendiente_id' => $entrada->id,
            'antes' => $antes,
            'despues' => [
                'material_id' => $entrada->material_id,
                'cantidad' => $entrada->cantidad,
                'codigo_barras' => $entrada->codigo_barras,
                'proveedor' => $entrada->proveedor,
                'costo_unitario' => $entrada->costo_unitario,
                'referencia' => $entrada->referencia,
                'motivo' => $entrada->motivo,
                'datos_material' => $entrada->datos_material,
            ],
        ], $request);

        return redirect()
            ->route('admin.entradas.index', ['estado' => 'pendiente'])
            ->with('success', 'Correcciones guardadas. Revisa los datos y aprueba la entrada cuando todo este correcto.');
    }

    public function approve(Request $request, MaterialEntradaPendiente $entrada): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        if ($entrada->estado !== 'pendiente') {
            return back()->withErrors(['entrada' => 'Esta entrada ya fue revisada.']);
        }

        $esMaterialNuevo = (bool) $entrada->es_material_nuevo;

        DB::transaction(function () use ($entrada, $request): void {
            $entradaBloqueada = MaterialEntradaPendiente::query()
                ->whereKey($entrada->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($entradaBloqueada->estado !== 'pendiente') {
                throw ValidationException::withMessages([
                    'entrada' => 'Esta entrada ya fue revisada por otro administrador.',
                ]);
            }

            if ($entradaBloqueada->es_material_nuevo) {
                $datosMaterial = $entradaBloqueada->datos_material ?? [];
                $descripcion = trim((string) data_get($datosMaterial, 'descripcion', ''));
                $codigo = trim((string) ($entradaBloqueada->codigo_barras ?? ''));

                if ($descripcion === '') {
                    throw ValidationException::withMessages([
                        'entrada' => 'La solicitud no contiene el nombre del material. Rechazala y pide al almacenista capturarlo nuevamente.',
                    ]);
                }

                $material = $codigo !== ''
                    ? Material::query()
                        ->where('codigo_barras', $codigo)
                        ->where('es_plantilla_equipo', false)
                        ->lockForUpdate()
                        ->first()
                    : null;

                if (! $material) {
                    $material = Material::create(array_merge(
                        Arr::only($datosMaterial, [
                            'categoria',
                            'almacen',
                            'codigo_barras',
                            'numero_parte',
                            'clave_sat',
                            'clave_unidad',
                            'unidad',
                            'descripcion',
                            'apodo',
                            'marca',
                            'proveedor',
                            'proveedor_rfc',
                            'stock_minimo',
                            'stock_maximo',
                            'costo_unitario',
                            'moneda',
                        ]),
                        [
                            'stock' => 0,
                            'es_plantilla_equipo' => false,
                            'fotografia' => $entradaBloqueada->fotografia,
                        ]
                    ));
                }
            } else {
                $material = Material::query()
                    ->whereKey($entradaBloqueada->material_id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $anterior = $material->stock;
            $nuevo = $anterior + $entradaBloqueada->cantidad;
            $material->update([
                'stock' => $nuevo,
                'proveedor' => $entradaBloqueada->proveedor ?: $material->proveedor,
                'costo_unitario' => (float) $entradaBloqueada->costo_unitario > 0
                    ? $entradaBloqueada->costo_unitario
                    : $material->costo_unitario,
            ]);

            MaterialMovimiento::create([
                'material_id' => $material->id,
                'user_id' => $entradaBloqueada->user_id,
                'tipo' => 'entrada',
                'cantidad' => $entradaBloqueada->cantidad,
                'stock_anterior' => $anterior,
                'stock_nuevo' => $nuevo,
                'codigo_barras' => $entradaBloqueada->codigo_barras ?: $material->codigo_barras,
                'referencia' => $entradaBloqueada->referencia ?: 'Entrada aprobada',
                'motivo' => $entradaBloqueada->motivo ?: 'Aprobada por administrador',
                'evidencia_foto' => $entradaBloqueada->evidencia_foto,
                'proveedor' => $entradaBloqueada->proveedor ?: $material->proveedor,
                'costo_unitario' => (float) $entradaBloqueada->costo_unitario > 0
                    ? $entradaBloqueada->costo_unitario
                    : $material->costo_unitario,
            ]);

            $entradaBloqueada->update([
                'material_id' => $material->id,
                'estado' => 'aprobada',
                'approved_by' => $request->user()?->id,
                'approved_at' => now(),
                'comentario_admin' => trim((string) $request->input('comentario_admin', ''))
                    ?: $entradaBloqueada->comentario_admin,
            ]);
        });

        $entrada->refresh();

        AuditLogger::registrar('Entradas', 'Entrada aprobada', "Aprobo entrada de {$entrada->cantidad} piezas.", [
            'entrada_pendiente_id' => $entrada->id,
            'material_id' => $entrada->material_id,
            'cantidad' => $entrada->cantidad,
        ], $request);

        return back()->with(
            'success',
            $esMaterialNuevo
                ? 'Material creado y entrada aprobada. La pieza ya aparece en inventario con su stock actualizado.'
                : 'Entrada aprobada. El stock ya fue actualizado.'
        );
    }

    public function reject(Request $request, MaterialEntradaPendiente $entrada): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        if ($entrada->estado !== 'pendiente') {
            return back()->withErrors(['entrada' => 'Esta entrada ya fue revisada.']);
        }

        DB::transaction(function () use ($entrada, $request): void {
            $entradaBloqueada = MaterialEntradaPendiente::query()
                ->whereKey($entrada->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($entradaBloqueada->estado !== 'pendiente') {
                throw ValidationException::withMessages([
                    'entrada' => 'Esta entrada ya fue revisada por otro administrador.',
                ]);
            }

            $entradaBloqueada->update([
                'estado' => 'rechazada',
                'rejected_by' => $request->user()?->id,
                'rejected_at' => now(),
                'comentario_admin' => trim((string) $request->input('comentario_admin', ''))
                    ?: $entradaBloqueada->comentario_admin,
            ]);
        });

        AuditLogger::registrar('Entradas', 'Entrada rechazada', "Rechazo entrada de {$entrada->cantidad} piezas.", [
            'entrada_pendiente_id' => $entrada->id,
            'material_id' => $entrada->material_id,
            'cantidad' => $entrada->cantidad,
        ], $request);

        return back()->with('success', 'Entrada rechazada. El stock no se modifico.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validarCorreccion(Request $request, MaterialEntradaPendiente $entrada): array
    {
        $reglas = [
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'material_id' => [$entrada->es_material_nuevo ? 'nullable' : 'required', 'nullable', 'integer', 'exists:materials,id'],
            'proveedor' => ['nullable', 'string', 'max:255'],
            'costo_unitario' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'motivo' => ['nullable', 'string', 'max:255'],
            'comentario_admin' => ['nullable', 'string', 'max:2000'],
            'evidencia_foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:8192'],
            'fotografia' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];

        if ($entrada->es_material_nuevo) {
            $reglas = array_merge($reglas, [
                'descripcion' => ['required', 'string', 'max:2000'],
                'categoria' => ['nullable', 'string', 'max:255'],
                'almacen' => ['nullable', 'string', 'max:120'],
                'codigo_barras' => ['nullable', 'string', 'max:255'],
                'numero_parte' => ['nullable', 'string', 'max:255'],
                'clave_sat' => ['nullable', 'string', 'max:30'],
                'clave_unidad' => ['nullable', 'string', 'max:30'],
                'unidad' => ['nullable', 'string', 'max:80'],
                'apodo' => ['nullable', 'string', 'max:255'],
                'marca' => ['nullable', 'string', 'max:255'],
                'proveedor_rfc' => ['nullable', 'string', 'max:20'],
                'stock_minimo' => ['nullable', 'integer', 'min:0'],
                'stock_maximo' => ['nullable', 'integer', 'min:0'],
                'moneda' => ['nullable', 'string', 'max:10'],
            ]);
        }

        return $request->validate($reglas, [
            'cantidad.required' => 'Escribe cuantas piezas recibieron.',
            'cantidad.integer' => 'La cantidad debe ser un numero entero.',
            'cantidad.min' => 'La cantidad debe ser mayor a cero.',
            'material_id.required' => 'Selecciona la pieza correcta del inventario.',
            'material_id.exists' => 'La pieza seleccionada ya no existe.',
            'descripcion.required' => 'Escribe el nombre del material nuevo.',
            'costo_unitario.numeric' => 'El precio por unidad debe ser numerico.',
            'evidencia_foto.image' => 'La evidencia debe ser una imagen valida.',
            'evidencia_foto.max' => 'La evidencia no debe pesar mas de 8 MB.',
            'fotografia.image' => 'La foto del producto debe ser una imagen valida.',
            'fotografia.max' => 'La foto del producto no debe pesar mas de 5 MB.',
        ]);
    }

    private function textoOpcional(array $datos, string $campo): ?string
    {
        $valor = trim((string) ($datos[$campo] ?? ''));

        return $valor !== '' ? $valor : null;
    }

    private function categoriasDisponibles()
    {
        return MaterialCategory::query()
            ->where('activa', true)
            ->where('nombre', 'not like', 'EQUIPO%')
            ->orderBy('nombre')
            ->pluck('nombre')
            ->merge(Material::query()
                ->where('es_plantilla_equipo', false)
                ->whereNotNull('categoria')
                ->where('categoria', 'not like', 'EQUIPO%')
                ->distinct()
                ->pluck('categoria'))
            ->map(fn ($categoria) => trim((string) $categoria))
            ->filter()
            ->unique(fn ($categoria) => strtoupper($categoria))
            ->sort()
            ->values();
    }
}
