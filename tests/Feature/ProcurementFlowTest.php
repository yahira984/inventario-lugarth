<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialMovimiento;
use App\Models\MaterialSupplierPrice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_authorization_order_reception_and_invoice_flow_is_traceable(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);
        $warehouseUser = User::factory()->create([
            'role' => 'almacenista',
            'approved_at' => now(),
        ]);
        $material = Material::create([
            'descripcion' => 'Conector para flujo de compras',
            'numero_parte' => 'COMPRA-001',
            'stock' => 0,
            'stock_minimo' => 2,
            'stock_maximo' => 5,
            'es_plantilla_equipo' => false,
        ]);

        $this->actingAs($warehouseUser)
            ->post(route('admin.compras.requests.store'), [
                'material_id' => [$material->id],
                'cantidad' => [$material->id => 5],
                'prioridad' => 'urgente',
                'motivo' => 'Sin existencias',
                'origen' => 'stock_minimo',
            ])
            ->assertRedirect(route('admin.compras.index'))
            ->assertSessionHasNoErrors();

        $purchaseRequest = PurchaseRequest::query()->with('items')->firstOrFail();
        $requestItem = $purchaseRequest->items->first();
        $this->assertSame('solicitada', $purchaseRequest->estado);
        $this->assertSame('5.00', $requestItem->cantidad_solicitada);

        $this->actingAs($admin)
            ->patch(route('admin.compras.requests.authorize', $purchaseRequest), [
                'cantidad_autorizada' => [$requestItem->id => 5],
                'comentario_revision' => 'Compra autorizada',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.compras.requests.order', $purchaseRequest), [
                'proveedor' => 'Proveedor de prueba',
                'referencia' => 'OC-FLUJO-001',
                'costo_unitario' => [$requestItem->id => 12.50],
            ])
            ->assertRedirect(route('admin.ordenes.index', ['buscar' => 'OC-FLUJO-001']))
            ->assertSessionHasNoErrors();

        $order = PurchaseOrder::query()->with('items')->firstOrFail();
        $orderItem = $order->items->first();
        $this->assertSame('ordenada', $purchaseRequest->fresh()->estado);
        $this->assertSame('62.50', $order->total);

        $this->actingAs($admin)
            ->patch(route('admin.ordenes.status', $order), ['estado' => 'autorizada'])
            ->assertSessionHasNoErrors();
        $this->actingAs($admin)
            ->patch(route('admin.ordenes.status', $order), ['estado' => 'enviada'])
            ->assertSessionHasNoErrors();

        $this->actingAs($warehouseUser)
            ->post(route('admin.ordenes.receive', $order), [
                'cantidad_recibida' => [$orderItem->id => 2],
                'referencia_recepcion' => 'REM-001',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $material->fresh()->stock);
        $this->assertSame('recepcion_parcial', $order->fresh()->estado);

        $this->actingAs($warehouseUser)
            ->post(route('admin.ordenes.receive', $order), [
                'cantidad_recibida' => [$orderItem->id => 3],
                'referencia_recepcion' => 'REM-002',
            ])
            ->assertSessionHasNoErrors();

        $material->refresh();
        $order->refresh();
        $this->assertSame(5, $material->stock);
        $this->assertSame('recibida', $order->estado);
        $this->assertSame('recibida', $purchaseRequest->fresh()->estado);
        $this->assertSame(2, MaterialMovimiento::query()->where('material_id', $material->id)->count());
        $this->assertSame(1, MaterialSupplierPrice::query()->where('material_id', $material->id)->count());

        $this->actingAs($admin)
            ->patch(route('admin.ordenes.invoice', $order), [
                'invoice_uuid' => 'ABCDEF12-3456-7890-ABCD-EF1234567890',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('facturada', $order->fresh()->estado);
        $this->assertSame('facturada', $purchaseRequest->fresh()->estado);
        $this->assertSame(5, $material->fresh()->stock);
    }
}
