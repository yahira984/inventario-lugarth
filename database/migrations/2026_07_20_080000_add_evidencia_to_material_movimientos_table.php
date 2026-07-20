<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_movimientos', function (Blueprint $table): void {
            $table->string('evidencia_foto')->nullable()->after('motivo');
        });
    }

    public function down(): void
    {
        Schema::table('material_movimientos', function (Blueprint $table): void {
            $table->dropColumn('evidencia_foto');
        });
    }
};
