<?php

namespace App\Console\Commands;

use App\Models\EquipmentPackage;
use App\Models\Material;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportEquipmentPackagesFromMaterials extends Command
{
    protected $signature = 'lugarth:import-equipment-packages';

    protected $description = 'Convierte las categorias actuales de materiales en equipos/paquetes con cantidades por paquete.';

    public function handle(): int
    {
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

        $this->info("Importacion lista: {$creados} equipos nuevos y {$items} piezas de paquete agregadas.");

        return self::SUCCESS;
    }
}
