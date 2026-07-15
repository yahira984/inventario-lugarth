<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (! Schema::hasColumn('materials', 'almacen')) {
                $table->string('almacen')->nullable()->after('categoria');
            }

            if (! Schema::hasColumn('materials', 'stock_maximo')) {
                $table->unsignedInteger('stock_maximo')->default(0)->after('stock_minimo');
            }

            if (! Schema::hasColumn('materials', 'clave_sat')) {
                $table->string('clave_sat', 30)->nullable()->after('codigo_barras');
            }

            if (! Schema::hasColumn('materials', 'clave_unidad')) {
                $table->string('clave_unidad', 30)->nullable()->after('clave_sat');
            }

            if (! Schema::hasColumn('materials', 'unidad')) {
                $table->string('unidad', 80)->nullable()->after('clave_unidad');
            }

            if (! Schema::hasColumn('materials', 'proveedor_rfc')) {
                $table->string('proveedor_rfc', 20)->nullable()->after('proveedor');
            }

            if (! Schema::hasColumn('materials', 'moneda')) {
                $table->string('moneda', 10)->nullable()->after('costo_unitario');
            }

            if (! Schema::hasColumn('materials', 'factura_uuid')) {
                $table->string('factura_uuid')->nullable()->after('moneda');
            }

            if (! Schema::hasColumn('materials', 'factura_folio')) {
                $table->string('factura_folio')->nullable()->after('factura_uuid');
            }

            if (! Schema::hasColumn('materials', 'factura_fecha')) {
                $table->dateTime('factura_fecha')->nullable()->after('factura_folio');
            }

            if (! Schema::hasColumn('materials', 'xml_importado_at')) {
                $table->timestamp('xml_importado_at')->nullable()->after('factura_fecha');
            }

            $table->index('almacen');
            $table->index('proveedor');
            $table->index('clave_sat');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            foreach (['almacen', 'stock_maximo', 'clave_sat', 'clave_unidad', 'unidad', 'proveedor_rfc', 'moneda', 'factura_uuid', 'factura_folio', 'factura_fecha', 'xml_importado_at'] as $column) {
                if (Schema::hasColumn('materials', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
