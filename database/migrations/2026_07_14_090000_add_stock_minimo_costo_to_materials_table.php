<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (! Schema::hasColumn('materials', 'stock_minimo')) {
                $table->unsignedInteger('stock_minimo')->default(0)->after('stock');
            }

            if (! Schema::hasColumn('materials', 'costo_unitario')) {
                $table->decimal('costo_unitario', 12, 2)->default(0)->after('stock_minimo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (Schema::hasColumn('materials', 'costo_unitario')) {
                $table->dropColumn('costo_unitario');
            }

            if (Schema::hasColumn('materials', 'stock_minimo')) {
                $table->dropColumn('stock_minimo');
            }
        });
    }
};
