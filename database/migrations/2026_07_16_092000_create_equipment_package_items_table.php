<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_package_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->string('numero_parte')->nullable();
            $table->string('descripcion');
            $table->string('apodo')->nullable();
            $table->string('marca')->nullable();
            $table->decimal('cantidad_por_paquete', 10, 2)->default(1);
            $table->string('unidad')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['equipment_package_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_package_items');
    }
};
