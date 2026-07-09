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
    Schema::create('materials', function (Blueprint $table) {
        $table->id();
        $table->string('numero_parte')->nullable();
        $table->text('descripcion');
        $table->string('marca')->nullable();
        $table->string('proveedor')->nullable();
        $table->string('categoria')->nullable(); 
        $table->integer('stock')->default(0);
        $table->string('fotografia')->nullable();
        $table->timestamps();
        $table->string('codigo_barras')->nullable()->unique(); // El código único del material
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
