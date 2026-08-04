<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('tool_loan_items'));
        $foreignKeys = collect(Schema::getForeignKeys('tool_loan_items'));

        // The original composite unique key was also the index used by the loan FK.
        if (! $indexes->contains(fn (array $index): bool => ($index['name'] ?? '') === 'tool_loan_items_tool_loan_id_index')) {
            Schema::table('tool_loan_items', function (Blueprint $table) {
                $table->index('tool_loan_id');
            });
        }

        $materialForeignKey = $foreignKeys->first(fn (array $foreignKey): bool => in_array('material_id', $foreignKey['columns'] ?? [], true));
        if ($materialForeignKey) {
            Schema::table('tool_loan_items', function (Blueprint $table) use ($materialForeignKey) {
                $table->dropForeign($materialForeignKey['name']);
            });
        }

        Schema::table('tool_loan_items', function (Blueprint $table) {
            $table->dropUnique(['tool_loan_id', 'material_id']);
            $table->dropColumn('material_id');
            $table->string('tool_name')->after('tool_loan_id');
            $table->string('tool_detail', 255)->nullable()->after('tool_name');
        });
    }

    public function down(): void
    {
        Schema::table('tool_loan_items', function (Blueprint $table) {
            $table->foreignId('material_id')->nullable()->after('tool_loan_id')->constrained('materials')->nullOnDelete();
            $table->dropColumn(['tool_name', 'tool_detail']);
            $table->unique(['tool_loan_id', 'material_id']);
        });

        Schema::table('tool_loan_items', function (Blueprint $table) {
            $table->dropIndex(['tool_loan_id']);
        });
    }
};
