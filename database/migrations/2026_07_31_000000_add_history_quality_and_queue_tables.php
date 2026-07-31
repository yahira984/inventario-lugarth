<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('inventory_snapshots', 'origen')) {
            Schema::table('inventory_snapshots', function (Blueprint $table): void {
                $table->string('origen', 30)->default('captura_diaria')->after('proveedor');
            });
        }

        if (! Schema::hasColumn('inventory_snapshots', 'exactitud')) {
            Schema::table('inventory_snapshots', function (Blueprint $table): void {
                $table->string('exactitud', 30)->default('confirmada')->after('origen');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inventory_snapshots', 'exactitud')) {
            Schema::table('inventory_snapshots', function (Blueprint $table): void {
                $table->dropColumn('exactitud');
            });
        }

        if (Schema::hasColumn('inventory_snapshots', 'origen')) {
            Schema::table('inventory_snapshots', function (Blueprint $table): void {
                $table->dropColumn('origen');
            });
        }
    }
};
