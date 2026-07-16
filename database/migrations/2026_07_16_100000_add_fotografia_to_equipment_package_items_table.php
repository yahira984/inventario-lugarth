<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_package_items', function (Blueprint $table): void {
            $table->string('fotografia')->nullable()->after('marca');
        });

        $items = DB::table('equipment_package_items')
            ->join('equipment_packages', 'equipment_packages.id', '=', 'equipment_package_items.equipment_package_id')
            ->whereNull('equipment_package_items.fotografia')
            ->select([
                'equipment_package_items.id',
                'equipment_package_items.numero_parte',
                'equipment_package_items.descripcion',
                'equipment_packages.nombre as categoria',
            ])
            ->get();

        foreach ($items as $item) {
            $material = DB::table('materials')
                ->where('es_plantilla_equipo', true)
                ->where('categoria', $item->categoria)
                ->where('descripcion', $item->descripcion)
                ->when($item->numero_parte, fn ($query) => $query->where('numero_parte', $item->numero_parte))
                ->whereNotNull('fotografia')
                ->first(['fotografia']);

            if ($material?->fotografia) {
                DB::table('equipment_package_items')
                    ->where('id', $item->id)
                    ->update(['fotografia' => $material->fotografia]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('equipment_package_items', function (Blueprint $table): void {
            $table->dropColumn('fotografia');
        });
    }
};
