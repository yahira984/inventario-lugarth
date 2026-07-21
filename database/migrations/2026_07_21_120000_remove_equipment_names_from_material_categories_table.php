<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('material_categories')) {
            return;
        }

        DB::table('material_categories')
            ->where('nombre', 'like', 'EQUIPO%')
            ->delete();
    }

    public function down(): void
    {
        // Los equipos pertenecen a equipment_packages y no deben restaurarse como categorias.
    }
};
