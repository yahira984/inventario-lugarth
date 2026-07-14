<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'approved_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable()->after('role');
            });
        }

        DB::table('users')
            ->whereNull('approved_at')
            ->update(['approved_at' => now()]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'approved_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('approved_at');
            });
        }
    }
};
