<?php

namespace Tests\Feature;

use App\Models\DirectMessage;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_presence_lists_approved_users_with_role_and_online_state(): void
    {
        $admin = $this->approvedUser('Administrador', 'administrador', now());
        $warehouseUser = $this->approvedUser('Almacenista', 'almacenista', now()->subMinutes(5));
        User::factory()->create([
            'name' => 'Pendiente',
            'role' => 'consultor',
            'approved_at' => null,
        ]);

        $response = $this
            ->actingAs($admin)
            ->getJson(route('team.presence'));

        $response
            ->assertOk()
            ->assertJsonPath('online_count', 1)
            ->assertJsonFragment([
                'name' => 'Administrador',
                'role_label' => 'Administrador',
                'online' => true,
                'is_self' => true,
            ])
            ->assertJsonFragment([
                'name' => 'Almacenista',
                'role_label' => 'Almacenista',
                'online' => false,
                'is_self' => false,
            ])
            ->assertJsonMissing(['name' => 'Pendiente']);

        $this->assertNotNull($warehouseUser->last_seen_at);
    }

    public function test_users_can_send_and_read_direct_messages(): void
    {
        $sender = $this->approvedUser('Ana Admin', 'administrador', now());
        $recipient = $this->approvedUser('Luis Almacén', 'almacenista', now());

        $this
            ->actingAs($sender)
            ->postJson(route('team.messages.send', $recipient), [
                'body' => 'La entrada 25 ya quedó revisada.',
            ])
            ->assertCreated()
            ->assertJsonPath('message.mine', true);

        $message = DirectMessage::query()->sole();
        $this->assertNull($message->read_at);

        $this
            ->actingAs($sender)
            ->getJson(route('team.presence'))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Luis Almacén',
                'has_conversation' => true,
                'last_message' => 'La entrada 25 ya quedó revisada.',
            ]);

        $this
            ->actingAs($recipient)
            ->getJson(route('team.messages', $sender))
            ->assertOk()
            ->assertJsonPath('user.name', 'Ana Admin')
            ->assertJsonPath('messages.0.body', 'La entrada 25 ya quedó revisada.')
            ->assertJsonPath('messages.0.mine', false);

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_users_cannot_message_themselves_or_unapproved_accounts(): void
    {
        $user = $this->approvedUser('Usuario Activo', 'consultor', now());
        $pending = User::factory()->create([
            'role' => 'consultor',
            'approved_at' => null,
        ]);

        $this
            ->actingAs($user)
            ->postJson(route('team.messages.send', $user), ['body' => 'Hola'])
            ->assertUnprocessable();

        $this
            ->actingAs($user)
            ->postJson(route('team.messages.send', $pending), ['body' => 'Hola'])
            ->assertNotFound();
    }

    public function test_logout_marks_the_user_as_offline(): void
    {
        $user = $this->approvedUser('Usuario Activo', 'almacenista', now());

        $this
            ->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertNull($user->fresh()->last_seen_at);
    }

    public function test_admin_can_configure_retention_and_delete_only_expired_messages(): void
    {
        $admin = $this->approvedUser('Administrador', 'administrador', now());
        $recipient = $this->approvedUser('Almacén', 'almacenista', now());
        $expired = $this->message($admin, $recipient, 'Mensaje antiguo', now()->subDays(31));
        $recent = $this->message($admin, $recipient, 'Mensaje reciente', now()->subDays(3));

        $this
            ->actingAs($admin)
            ->patch(route('admin.chats.retention'), ['retention_days' => 30])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'chat_retention_days',
            'value' => '30',
        ]);

        $this
            ->actingAs($admin)
            ->delete(route('admin.chats.purge'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('direct_messages', ['id' => $expired->id]);
        $this->assertDatabaseHas('direct_messages', ['id' => $recent->id]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'modulo' => 'Chat interno',
            'accion' => 'Limpieza manual',
        ]);
    }

    public function test_only_admin_can_manage_chat_history(): void
    {
        $admin = $this->approvedUser('Administrador', 'administrador', now());
        $warehouseUser = $this->approvedUser('Almacenista', 'almacenista', now());

        $this->actingAs($admin)
            ->get(route('admin.chats.index'))
            ->assertOk()
            ->assertSee('Administración de chats')
            ->assertSee('30 días (recomendado)');

        $this->actingAs($warehouseUser)
            ->get(route('admin.chats.index'))
            ->assertForbidden();

        $this->actingAs($warehouseUser)
            ->patch(route('admin.chats.retention'), ['retention_days' => 7])
            ->assertForbidden();
    }

    public function test_admin_can_delete_one_conversation_without_affecting_another(): void
    {
        $admin = $this->approvedUser('Administrador', 'administrador', now());
        $first = $this->approvedUser('Primera persona', 'almacenista', now());
        $second = $this->approvedUser('Segunda persona', 'consultor', now());
        $selectedMessage = $this->message($admin, $first, 'Conversación por eliminar', now());
        $preservedMessage = $this->message($admin, $second, 'Conversación conservada', now());

        $this
            ->actingAs($admin)
            ->delete(route('admin.chats.conversations.destroy', [$admin, $first]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('direct_messages', ['id' => $selectedMessage->id]);
        $this->assertDatabaseHas('direct_messages', ['id' => $preservedMessage->id]);
    }

    public function test_scheduled_chat_command_uses_configured_retention(): void
    {
        $sender = $this->approvedUser('Emisor', 'administrador', now());
        $recipient = $this->approvedUser('Receptor', 'consultor', now());
        $expired = $this->message($sender, $recipient, 'Caducado', now()->subDays(8));
        $recent = $this->message($sender, $recipient, 'Vigente', now()->subDays(2));
        SystemSetting::put('chat_retention_days', '7');

        $this->artisan('chat:limpiar')
            ->expectsOutput('Limpieza terminada: 1 mensajes eliminados.')
            ->assertSuccessful();

        $this->assertDatabaseMissing('direct_messages', ['id' => $expired->id]);
        $this->assertDatabaseHas('direct_messages', ['id' => $recent->id]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'modulo' => 'Chat interno',
            'accion' => 'Limpieza automática',
        ]);
    }

    private function approvedUser(string $name, string $role, mixed $lastSeen): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => $role,
            'approved_at' => now(),
            'last_seen_at' => $lastSeen,
        ]);
    }

    private function message(User $sender, User $recipient, string $body, mixed $createdAt): DirectMessage
    {
        $message = DirectMessage::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'body' => $body,
        ]);

        $message->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();

        return $message;
    }
}
