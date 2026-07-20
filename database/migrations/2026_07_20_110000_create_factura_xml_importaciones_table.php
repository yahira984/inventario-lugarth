<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_xml_importaciones', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('version', 10)->nullable();
            $table->string('serie')->nullable();
            $table->string('folio')->nullable();
            $table->dateTime('fecha')->nullable();
            $table->string('moneda', 10)->nullable();
            $table->decimal('tipo_cambio', 14, 6)->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('impuestos_trasladados', 14, 2)->default(0);
            $table->decimal('impuestos_retenidos', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('tipo_comprobante', 10)->nullable();
            $table->string('metodo_pago', 10)->nullable();
            $table->string('forma_pago', 10)->nullable();
            $table->string('emisor_rfc', 20)->nullable();
            $table->string('emisor_nombre')->nullable();
            $table->string('receptor_rfc', 20)->nullable();
            $table->string('receptor_nombre')->nullable();
            $table->unsignedInteger('conceptos_count')->default(0);
            $table->json('datos');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['emisor_rfc', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_xml_importaciones');
    }
};
