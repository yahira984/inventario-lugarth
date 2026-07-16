<?php

namespace App\Http\Controllers;

use App\Models\EquipmentPackage;
use App\Models\EquipmentPackageItem;
use App\Models\EquipmentPackageWithdrawal;
use App\Models\Material;
use App\Models\MaterialMovimiento;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EquipmentPackageController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para consultar equipos.');

        $buscar = trim((string) $request->query('buscar', ''));

        $equipos = EquipmentPackage::query()
            ->withCount('items')
            ->when($buscar !== '', function ($query) use ($buscar): void {
                $query->where(function ($q) use ($buscar): void {
                    $q->where('nombre', 'LIKE', "%{$buscar}%")
                        ->orWhere('codigo', 'LIKE', "%{$buscar}%")
                        ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
                });
            })
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('equipos.index', [
            'equipos' => $equipos,
            'buscar' => $buscar,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para registrar equipos.');

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:80', 'unique:equipment_packages,codigo'],
            'descripcion' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'Escribe el nombre del equipo o paquete.',
            'codigo.unique' => 'Ese codigo de equipo ya existe.',
        ]);

        $datos['codigo'] = trim((string) ($datos['codigo'] ?? '')) ?: Str::slug($datos['nombre']);

        $equipo = EquipmentPackage::create($datos);

        AuditLogger::registrar('Equipos', 'Alta de equipo', "Registro el equipo {$equipo->nombre}.", [
            'equipment_package_id' => $equipo->id,
        ], $request);

        return redirect()
            ->route('equipos.show', $equipo)
            ->with('success', 'Equipo registrado. Ahora agrega las piezas que requiere.');
    }

    public function show(Request $request, EquipmentPackage $equipo): View
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para consultar equipos.');

        $equipo->load(['items.material', 'withdrawals.user']);

        $materiales = Material::query()
            ->where('es_plantilla_equipo', false)
            ->orderBy('descripcion')
            ->limit(500)
            ->get(['id', 'descripcion', 'apodo', 'numero_parte', 'marca', 'unidad', 'fotografia', 'stock']);

        return view('equipos.show', [
            'equipo' => $equipo,
            'materiales' => $materiales,
            'materialesEquipo' => $materiales->keyBy('id')->map(function (Material $material): array {
                return [
                    'descripcion' => $material->descripcion,
                    'numero_parte' => $material->numero_parte,
                    'apodo' => $material->apodo,
                    'marca' => $material->marca,
                    'unidad' => $material->unidad ?: 'pza',
                ];
            }),
        ]);
    }

    public function addItem(Request $request, EquipmentPackage $equipo): RedirectResponse
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para editar equipos.');

        $datos = $request->validate([
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'numero_parte' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'apodo' => ['nullable', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'cantidad_por_paquete' => ['required', 'numeric', 'min:0.01'],
            'unidad' => ['nullable', 'string', 'max:80'],
            'notas' => ['nullable', 'string'],
        ], [
            'descripcion.required' => 'Escribe que pieza requiere el equipo.',
            'cantidad_por_paquete.required' => 'Escribe cuantas piezas ocupa cada equipo.',
            'cantidad_por_paquete.min' => 'La cantidad por paquete debe ser mayor a cero.',
        ]);

        $this->validarMaterialReal($datos['material_id'] ?? null);

        if (! empty($datos['material_id'])) {
            $material = Material::query()
                ->whereKey($datos['material_id'])
                ->where('es_plantilla_equipo', false)
                ->firstOrFail();

            $datos['descripcion'] = $material->descripcion;
            $datos['numero_parte'] = $material->numero_parte;
            $datos['apodo'] = $material->apodo;
            $datos['marca'] = $material->marca;
            $datos['unidad'] = $material->unidad ?: ($datos['unidad'] ?? 'pza');
            $datos['fotografia'] = $material->fotografia;
        }

        if (blank($datos['descripcion'] ?? null)) {
            throw ValidationException::withMessages([
                'descripcion' => 'Selecciona una pieza del inventario o escribe la descripcion manualmente.',
            ]);
        }

        $equipo->items()->create($datos);

        return back()->with('success', 'Pieza agregada al equipo.');
    }

    public function updateItem(Request $request, EquipmentPackage $equipo, EquipmentPackageItem $item): RedirectResponse
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para editar equipos.');
        abort_unless($item->equipment_package_id === $equipo->id, 404);

        $datos = $request->validate([
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'cantidad_por_paquete' => ['required', 'numeric', 'min:0.01'],
            'notas' => ['nullable', 'string'],
        ]);

        $this->validarMaterialReal($datos['material_id'] ?? null);

        $item->update($datos);

        return back()->with('success', 'Pieza del equipo actualizada.');
    }

    public function deleteItem(Request $request, EquipmentPackage $equipo, EquipmentPackageItem $item): RedirectResponse
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para editar equipos.');
        abort_unless($item->equipment_package_id === $equipo->id, 404);

        $item->delete();

        return back()->with('success', 'Pieza quitada del equipo.');
    }

    public function withdraw(Request $request, EquipmentPackage $equipo): RedirectResponse
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para retirar equipos.');

        $datos = $request->validate([
            'cantidad_paquetes' => ['required', 'integer', 'min:1'],
            'tipo' => ['required', 'in:venta,retiro'],
            'referencia' => ['nullable', 'string', 'max:120'],
            'notas' => ['nullable', 'string', 'max:500'],
        ], [
            'cantidad_paquetes.required' => 'Escribe cuantos equipos se retiran o venden.',
            'cantidad_paquetes.min' => 'Debe retirarse al menos un equipo.',
            'tipo.required' => 'Selecciona si es venta o retiro interno.',
            'tipo.in' => 'Selecciona un tipo de movimiento valido.',
        ]);

        $equipo->load('items.material');

        if ($equipo->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cantidad_paquetes' => 'Este equipo no tiene piezas configuradas.',
            ]);
        }

        $sinVincular = $equipo->items->filter(fn (EquipmentPackageItem $item) => ! $item->material_id);

        if ($sinVincular->isNotEmpty()) {
            throw ValidationException::withMessages([
                'cantidad_paquetes' => 'Antes de retirar este equipo, vincula todas sus piezas con el inventario real.',
            ]);
        }

        $cantidadPaquetes = (int) $datos['cantidad_paquetes'];
        $tipoTexto = $datos['tipo'] === 'venta' ? 'Venta' : 'Retiro interno';

        DB::transaction(function () use ($equipo, $cantidadPaquetes, $datos, $request, $tipoTexto): void {
            foreach ($equipo->items as $item) {
                $necesario = (int) ceil(((float) $item->cantidad_por_paquete) * $cantidadPaquetes);
                $material = Material::query()
                    ->whereKey($item->material_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($material->stock < $necesario) {
                    throw ValidationException::withMessages([
                        'cantidad_paquetes' => "Stock insuficiente para {$material->descripcion}. Disponible: {$material->stock}, requerido: {$necesario}.",
                    ]);
                }

                $stockAnterior = $material->stock;
                $stockNuevo = $stockAnterior - $necesario;
                $material->update(['stock' => $stockNuevo]);

                MaterialMovimiento::create([
                    'material_id' => $material->id,
                    'user_id' => $request->user()?->id,
                    'tipo' => 'salida',
                    'cantidad' => $necesario,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'codigo_barras' => $material->codigo_barras,
                    'referencia' => $datos['referencia'] ?: "{$tipoTexto} de {$equipo->nombre}",
                    'motivo' => "{$tipoTexto}: consumo por {$cantidadPaquetes} equipo(s) de {$equipo->nombre}.",
                ]);
            }

            EquipmentPackageWithdrawal::create([
                'equipment_package_id' => $equipo->id,
                'user_id' => $request->user()?->id,
                'cantidad_paquetes' => $cantidadPaquetes,
                'tipo' => $datos['tipo'],
                'referencia' => $datos['referencia'] ?? null,
                'notas' => $datos['notas'] ?? null,
            ]);
        });

        AuditLogger::registrar('Equipos', $tipoTexto . ' de equipo', "{$tipoTexto} de {$cantidadPaquetes} equipo(s) {$equipo->nombre}.", [
            'equipment_package_id' => $equipo->id,
            'cantidad_paquetes' => $cantidadPaquetes,
            'tipo' => $datos['tipo'],
        ], $request);

        return back()->with('success', $tipoTexto . ' registrada. El stock de piezas vinculadas fue descontado.');
    }

    public function importFromMaterials(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403, 'Solo administrador puede importar equipos.');

        $categorias = Material::query()
            ->whereNotNull('categoria')
            ->where('categoria', '<>', '')
            ->where('categoria', '<>', 'IMPORTADO XML')
            ->distinct()
            ->pluck('categoria');

        $creados = 0;
        $items = 0;

        foreach ($categorias as $categoria) {
            $equipo = EquipmentPackage::firstOrCreate(
                ['codigo' => Str::slug($categoria)],
                [
                    'nombre' => $categoria,
                    'descripcion' => 'Equipo importado desde los renglones actuales del catalogo. La cantidad original se toma como cantidad por paquete.',
                    'activo' => true,
                ]
            );

            if ($equipo->wasRecentlyCreated) {
                $creados++;
            }

            $materiales = Material::query()
                ->where('categoria', $categoria)
                ->orderBy('descripcion')
                ->get();

            foreach ($materiales as $material) {
                $existe = $equipo->items()
                    ->where('numero_parte', $material->numero_parte)
                    ->where('descripcion', $material->descripcion)
                    ->exists();

                if ($existe) {
                    continue;
                }

                $equipo->items()->create([
                    'material_id' => null,
                    'numero_parte' => $material->numero_parte,
                    'descripcion' => $material->descripcion,
                    'apodo' => $material->apodo,
                    'marca' => $material->marca,
                    'fotografia' => $material->fotografia,
                    'cantidad_por_paquete' => max(1, (float) $material->stock),
                    'unidad' => $material->unidad ?: 'pza',
                    'notas' => 'Importado desde categoria de Excel. Vincular con pieza real de inventario antes de retirar.',
                ]);
                $items++;
            }

            Material::query()
                ->where('categoria', $categoria)
                ->update(['es_plantilla_equipo' => true]);
        }

        return back()->with('success', "Importacion lista: {$creados} equipos nuevos y {$items} piezas de paquete agregadas.");
    }

    private function validarMaterialReal(null|int|string $materialId): void
    {
        if (! $materialId) {
            return;
        }

        $esReal = Material::query()
            ->whereKey($materialId)
            ->where('es_plantilla_equipo', false)
            ->exists();

        if (! $esReal) {
            throw ValidationException::withMessages([
                'material_id' => 'Selecciona una pieza del inventario real, no una plantilla importada del Excel.',
            ]);
        }
    }
}
