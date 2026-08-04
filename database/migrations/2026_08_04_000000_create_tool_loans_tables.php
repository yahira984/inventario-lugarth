<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loaned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_name');
            $table->string('employee_code', 80)->nullable();
            $table->string('employee_area', 120)->nullable();
            $table->dateTime('taken_at');
            $table->dateTime('expected_return_at')->nullable();
            $table->string('status', 30)->default('activo');
            $table->string('evidence_out')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->string('evidence_return')->nullable();
            $table->text('return_notes')->nullable();
            $table->text('repair_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'taken_at']);
            $table->index('employee_name');
        });

        Schema::create('tool_loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_loan_id')->constrained('tool_loans')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->unsignedInteger('quantity_loaned');
            $table->unsignedInteger('quantity_returned')->default(0);
            $table->unsignedInteger('quantity_repair')->default(0);
            $table->unsignedInteger('quantity_repaired')->default(0);
            $table->unsignedInteger('quantity_lost')->default(0);
            $table->string('last_return_condition', 40)->nullable();
            $table->timestamps();

            $table->unique(['tool_loan_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_loan_items');
        Schema::dropIfExists('tool_loans');
    }
};
