<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado', 30)->default('solicitada');
            $table->string('prioridad', 20)->default('normal');
            $table->string('origen', 30)->default('manual');
            $table->text('motivo')->nullable();
            $table->text('comentario_revision')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
        });

        Schema::create('purchase_request_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('cantidad_solicitada', 12, 2);
            $table->decimal('cantidad_autorizada', 12, 2)->nullable();
            $table->decimal('consumo_diario_estimado', 12, 4)->default(0);
            $table->unsignedInteger('dias_cobertura')->default(30);
            $table->string('razon')->nullable();
            $table->timestamps();

            $table->unique(['purchase_request_id', 'material_id'], 'purchase_request_material_unique');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->foreignId('purchase_request_id')->nullable()->after('id')
                ->constrained('purchase_requests')->nullOnDelete();
            $table->foreignId('authorized_by')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->after('authorized_by')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('invoiced_by')->nullable()->after('received_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('authorized_at')->nullable()->after('fecha_esperada');
            $table->timestamp('ordered_at')->nullable()->after('authorized_at');
            $table->timestamp('received_at')->nullable()->after('ordered_at');
            $table->timestamp('invoiced_at')->nullable()->after('received_at');
            $table->string('invoice_uuid', 40)->nullable()->after('invoiced_at');
            $table->string('invoice_folio', 120)->nullable()->after('invoice_uuid');
            $table->string('moneda', 10)->default('MXN')->after('invoice_folio');
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->decimal('cantidad_recibida', 12, 2)->default(0)->after('cantidad');
        });

        Schema::create('material_supplier_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->string('proveedor');
            $table->string('proveedor_rfc', 20)->nullable();
            $table->decimal('precio_unitario', 14, 4);
            $table->decimal('precio_anterior', 14, 4)->nullable();
            $table->decimal('variacion_porcentaje', 9, 3)->nullable();
            $table->boolean('aumento_significativo')->default(false);
            $table->string('moneda', 10)->default('MXN');
            $table->string('origen', 30)->default('manual');
            $table->string('referencia')->nullable();
            $table->timestamp('registrado_en');
            $table->timestamps();

            $table->index(['material_id', 'proveedor', 'registrado_en'], 'material_supplier_price_lookup');
            $table->index(['aumento_significativo', 'registrado_en'], 'material_supplier_price_alerts');
        });

        Schema::create('material_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('angulo', 80)->nullable();
            $table->boolean('es_principal')->default(false);
            $table->json('visual_descriptor')->nullable();
            $table->string('visual_descriptor_signature', 64)->nullable();
            $table->timestamps();

            $table->index(['material_id', 'es_principal']);
        });

        Schema::create('visual_search_feedback', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('suggested_material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->foreignId('selected_material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->string('query_signature', 64)->nullable();
            $table->boolean('was_correct');
            $table->decimal('confidence', 6, 3)->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['suggested_material_id', 'was_correct']);
        });

        Schema::create('user_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key', 120);
            $table->json('value');
            $table->timestamps();

            $table->unique(['user_id', 'key']);
        });

        Schema::create('inventory_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->date('snapshot_date');
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->integer('stock');
            $table->decimal('costo_unitario', 14, 4)->default(0);
            $table->decimal('valor_total', 16, 4)->default(0);
            $table->string('almacen')->nullable();
            $table->string('categoria')->nullable();
            $table->string('proveedor')->nullable();
            $table->timestamps();

            $table->unique(['snapshot_date', 'material_id']);
            $table->index(['snapshot_date', 'almacen']);
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::table('factura_xml_importaciones', function (Blueprint $table): void {
            $table->foreignId('purchase_order_id')->nullable()->after('id')
                ->constrained('purchase_orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('factura_xml_importaciones', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('purchase_order_id');
        });

        Schema::dropIfExists('notifications');
        Schema::dropIfExists('inventory_snapshots');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('visual_search_feedback');
        Schema::dropIfExists('material_photos');
        Schema::dropIfExists('material_supplier_prices');

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->dropColumn('cantidad_recibida');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('purchase_request_id');
            $table->dropConstrainedForeignId('authorized_by');
            $table->dropConstrainedForeignId('received_by');
            $table->dropConstrainedForeignId('invoiced_by');
            $table->dropColumn([
                'authorized_at',
                'ordered_at',
                'received_at',
                'invoiced_at',
                'invoice_uuid',
                'invoice_folio',
                'moneda',
            ]);
        });

        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
    }
};
