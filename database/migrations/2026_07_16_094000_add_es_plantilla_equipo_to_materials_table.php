<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table): void {
            $table->boolean('es_plantilla_equipo')->default(false)->after('apodo');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table): void {
            $table->dropColumn('es_plantilla_equipo');
        });
    }
};
