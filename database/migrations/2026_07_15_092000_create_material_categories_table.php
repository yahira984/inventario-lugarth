<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        $categorias = collect([
            'IMPORTADO XML',
        ])
            ->merge(DB::table('materials')
                ->whereNotNull('categoria')
                ->where('categoria', 'not like', 'EQUIPO%')
                ->pluck('categoria'))
            ->map(fn ($categoria) => trim((string) $categoria))
            ->filter()
            ->unique(fn ($categoria) => strtoupper($categoria))
            ->values();

        foreach ($categorias as $categoria) {
            DB::table('material_categories')->insertOrIgnore([
                'nombre' => $categoria,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('material_categories');
    }
};
