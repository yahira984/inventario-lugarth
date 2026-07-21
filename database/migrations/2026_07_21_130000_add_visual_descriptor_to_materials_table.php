<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table): void {
            $table->json('visual_descriptor')->nullable()->after('fotografia');
            $table->string('visual_descriptor_signature', 64)->nullable()->after('visual_descriptor');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table): void {
            $table->dropColumn(['visual_descriptor', 'visual_descriptor_signature']);
        });
    }
};
