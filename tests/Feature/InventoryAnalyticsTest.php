<?php

namespace Tests\Feature;

use App\Models\InventorySnapshot;
use App\Models\Material;
use App\Support\InventoryAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_historical_inventory_prefers_the_exact_daily_snapshot(): void
    {
        $material = Material::create([
            'descripcion' => 'Material historico',
            'categoria' => 'Categoria actual',
            'almacen' => 'Almacen actual',
            'proveedor' => 'Proveedor actual',
            'stock' => 20,
            'costo_unitario' => 30,
            'es_plantilla_equipo' => false,
        ]);
        $date = today()->subDays(5);
        InventorySnapshot::create([
            'snapshot_date' => $date,
            'material_id' => $material->id,
            'stock' => 7,
            'costo_unitario' => 12.50,
            'valor_total' => 87.50,
            'almacen' => 'Almacen historico',
            'categoria' => 'Categoria historica',
            'proveedor' => 'Proveedor historico',
        ]);

        $service = app(InventoryAnalyticsService::class);
        $historical = $service->stockAt($date)->firstWhere('id', $material->id);
        $breakdowns = $service->valueBreakdowns(collect([$historical]));

        $this->assertSame(7, $historical->stock_historico);
        $this->assertSame(12.5, $historical->costo_historico);
        $this->assertSame(87.5, $historical->valor_historico);
        $this->assertSame('Almacen historico', $historical->almacen_historico);
        $this->assertSame('Captura diaria', $historical->origen_historico);
        $this->assertSame('Almacen historico', $breakdowns['almacenes']->first()['etiqueta']);
        $this->assertSame(87.5, $breakdowns['almacenes']->first()['valor']);
    }

    public function test_daily_snapshot_command_is_idempotent(): void
    {
        Material::create([
            'descripcion' => 'Material para captura',
            'stock' => 4,
            'costo_unitario' => 8.25,
            'es_plantilla_equipo' => false,
        ]);

        $this->artisan('inventario:captura-diaria')->assertSuccessful();
        $this->artisan('inventario:captura-diaria')->assertSuccessful();

        $this->assertDatabaseCount('inventory_snapshots', 1);
        $this->assertSame('33.0000', InventorySnapshot::firstOrFail()->valor_total);
    }
}
