<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_entradas_pendientes', function (Blueprint $table): void {
            $table->foreignId('material_id')->nullable()->change();
            $table->boolean('es_material_nuevo')->default(false)->after('material_id');
            $table->json('datos_material')->nullable()->after('es_material_nuevo');
            $table->string('fotografia')->nullable()->after('evidencia_foto');
        });
    }

    public function down(): void
    {
        DB::table('material_entradas_pendientes')->whereNull('material_id')->delete();

        Schema::table('material_entradas_pendientes', function (Blueprint $table): void {
            $table->dropColumn(['es_material_nuevo', 'datos_material', 'fotografia']);
            $table->foreignId('material_id')->nullable(false)->change();
        });
    }
};
