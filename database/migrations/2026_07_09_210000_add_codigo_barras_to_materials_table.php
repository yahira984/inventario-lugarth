<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('materials', 'codigo_barras')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->string('codigo_barras')->nullable()->unique();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('materials', 'codigo_barras')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropColumn('codigo_barras');
            });
        }
    }
};
