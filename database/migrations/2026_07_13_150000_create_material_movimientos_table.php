<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo', 20);
            $table->unsignedInteger('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            $table->string('codigo_barras')->nullable();
            $table->string('referencia')->nullable();
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'created_at']);
            $table->index('codigo_barras');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_movimientos');
    }
};
