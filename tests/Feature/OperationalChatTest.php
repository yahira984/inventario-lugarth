<?php

namespace Tests\Feature;

use App\Models\DirectMessage;
use App\Models\User;
use App\Support\ChatRetention;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OperationalChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::store('file')->flush();
    }

    public function test_replies_stickers_delivery_and_read_status_use_compact_fields(): void
    {
        $sender = $this->approvedUser('Ana Administradora', 'administrador');
        $recipient = $this->approvedUser('Luis Almacen', 'almacenista');

        $firstResponse = $this->actingAs($sender)
            ->postJson(route('team.messages.send', $recipient), [
                'body' => 'Revisa la entrada pendiente.',
            ])
            ->assertCreated()
            ->assertJsonPath('message.status_label', 'Enviado');

        $firstMessageId = $firstResponse->json('message.id');

        $this->actingAs($sender)
            ->postJson(route('team.messages.send', $recipient), [
                'sticker_key' => 'listo',
                'reply_to_id' => $firstMessageId,
            ])
            ->assertCreated()
            ->assertJsonPath('message.message_type', 'sticker')
            ->assertJsonPath('message.sticker.key', 'listo')
            ->assertJsonPath('message.reply.id', $firstMessageId);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'message_type' => 'sticker',
            'sticker_key' => 'listo',
            'body' => '',
        ]);

        $this->actingAs($recipient)
            ->getJson(route('team.presence'))
            ->assertOk();

        $this->assertDatabaseMissing('direct_messages', [
            'recipient_id' => $recipient->id,
            'delivered_at' => null,
        ]);

        $this->actingAs($sender)
            ->getJson(route('team.messages', $recipient))
            ->assertOk()
            ->assertJsonPath('messages.0.delivered', true)
            ->assertJsonPath('messages.0.read', false);

        $this->actingAs($recipient)
            ->getJson(route('team.messages', $sender))
            ->assertOk();

        $this->actingAs($sender)
            ->getJson(route('team.messages', $recipient))
            ->assertOk()
            ->assertJsonPath('messages.0.read', true)
            ->assertJsonPath('messages.0.status_label', 'Leído');
    }

    public function test_typing_indicator_uses_file_cache_and_presence_exposes_availability(): void
    {
        $sender = $this->approvedUser('Persona Ocupada', 'almacenista');
        $recipient = $this->approvedUser('Persona Receptora', 'consultor');

        $this->actingAs($sender)
            ->patchJson(route('team.availability'), ['status' => 'busy'])
            ->assertOk()
            ->assertJsonPath('label', 'Ocupado');

        $this->actingAs($recipient)
            ->getJson(route('team.presence'))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Persona Ocupada',
                'availability_status' => 'busy',
                'availability_label' => 'Ocupado',
            ]);

        $this->actingAs($sender)
            ->postJson(route('team.typing', $recipient), ['typing' => true])
            ->assertOk();

        $this->actingAs($recipient)
            ->getJson(route('team.messages', $sender))
            ->assertOk()
            ->assertJsonPath('typing', true);

        $this->actingAs($sender)
            ->postJson(route('team.typing', $recipient), ['typing' => false])
            ->assertOk();

        $this->actingAs($recipient)
            ->getJson(route('team.messages', $sender))
            ->assertOk()
            ->assertJsonPath('typing', false);
    }

    public function test_pinned_messages_are_protected_from_automatic_and_total_cleanup(): void
    {
        $admin = $this->approvedUser('Administrador', 'administrador');
        $recipient = $this->approvedUser('Almacenista', 'almacenista');
        $pinned = $this->message($admin, $recipient, 'Aviso importante', now()->subDays(40));
        $expired = $this->message($admin, $recipient, 'Mensaje temporal', now()->subDays(40));

        $this->actingAs($admin)
            ->patchJson(route('team.messages.pin', $pinned), ['pinned' => true])
            ->assertOk()
            ->assertJsonPath('message.pinned', true);

        $deleted = app(ChatRetention::class)->purgeExpired(30);

        $this->assertSame(1, $deleted);
        $this->assertDatabaseHas('direct_messages', ['id' => $pinned->id]);
        $this->assertDatabaseMissing('direct_messages', ['id' => $expired->id]);

        $this->actingAs($admin)
            ->delete(route('admin.chats.clear'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('direct_messages', ['id' => $pinned->id]);
    }

    public function test_participants_can_download_a_conversation_without_creating_a_server_file(): void
    {
        $sender = $this->approvedUser('Usuario Uno', 'administrador');
        $recipient = $this->approvedUser('Usuario Dos', 'almacenista');
        $this->message($sender, $recipient, 'Contenido importante para conservar', now());

        $response = $this->actingAs($sender)
            ->get(route('team.conversations.export', $recipient))
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('CONVERSACION INTERNA LUGARTH', $content);
        $this->assertStringContainsString('Contenido importante para conservar', $content);
        $this->assertStringContainsString('Usuario Uno', $content);
        $this->assertStringContainsString('Usuario Dos', $content);
    }

    public function test_a_user_cannot_pin_a_message_from_another_conversation(): void
    {
        $first = $this->approvedUser('Primera persona', 'almacenista');
        $second = $this->approvedUser('Segunda persona', 'consultor');
        $outsider = $this->approvedUser('Persona externa', 'consultor');
        $message = $this->message($first, $second, 'Mensaje privado', now());

        $this->actingAs($outsider)
            ->patchJson(route('team.messages.pin', $message), ['pinned' => true])
            ->assertForbidden();
    }

    private function approvedUser(string $name, string $role): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => $role,
            'approved_at' => now(),
            'last_seen_at' => now(),
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
