<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_package_withdrawals', function (Blueprint $table): void {
            $table->string('tipo', 30)->default('venta')->after('cantidad_paquetes');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_package_withdrawals', function (Blueprint $table): void {
            $table->dropColumn('tipo');
        });
    }
};
