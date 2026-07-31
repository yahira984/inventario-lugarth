<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use App\Notifications\InventoryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkspaceNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_refresh_and_mark_only_their_notification_as_read(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $other = User::factory()->create(['approved_at' => now()]);
        $user->notify(new InventoryNotification(
            'Stock critico',
            'La pieza requiere reabasto.',
            route('materiales.index', ['stock' => 'critico']),
            'red'
        ));
        $other->notify(new InventoryNotification(
            'Privada',
            'No pertenece al usuario.',
            route('materiales.index')
        ));
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread', 1)
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.title', 'Stock critico');

        $this->actingAs($user)
            ->patchJson(route('notifications.read', $notification->id))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_table_preferences_are_saved_per_user(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);

        $this->actingAs($user)
            ->putJson(route('preferences.store'), [
                'key' => 'table.materiales.index.0.columns',
                'value' => [2, 5],
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'key' => 'table.materiales.index.0.columns',
        ]);
        $this->assertSame([2, 5], $user->preferences()->firstOrFail()->value);
    }

    public function test_named_inventory_filters_are_saved_per_user(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $filters = [
            'Criticos de conexiones' => [
                'filtrar_categoria' => 'CONEXIONES',
                'stock' => 'critico',
            ],
        ];

        $this->actingAs($user)
            ->putJson(route('preferences.store'), [
                'key' => 'inventory.saved_filters',
                'value' => $filters,
            ])
            ->assertOk();

        $this->assertSame(
            $filters,
            $user->preferences()->where('key', 'inventory.saved_filters')->firstOrFail()->value
        );
    }

    public function test_daily_inventory_command_notifies_admin_about_minimum_excess_and_inactivity(): void
    {
        Mail::fake();
        $admin = User::factory()->create([
            'role' => 'administrador',
            'approved_at' => now(),
        ]);
        Material::create([
            'descripcion' => 'Pieza critica',
            'stock' => 0,
            'stock_minimo' => 2,
            'es_plantilla_equipo' => false,
        ]);
        Material::create([
            'descripcion' => 'Pieza excedida',
            'stock' => 20,
            'stock_maximo' => 10,
            'es_plantilla_equipo' => false,
        ]);
        Material::create([
            'descripcion' => 'Pieza inmovil',
            'stock' => 5,
            'es_plantilla_equipo' => false,
        ]);

        $this->artisan('inventario:alertas-stock')->assertSuccessful();

        $titles = $admin->notifications()->get()->pluck('data.title');
        $this->assertContains('Alerta diaria de reabastecimiento', $titles);
        $this->assertContains('Exceso de inventario', $titles);
        $this->assertContains('Materiales sin movimiento', $titles);
    }
}
