<?php

namespace App\Support;

use App\Events\InventoryNotificationCreated;
use App\Notifications\InventoryNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class InventoryNotifier
{
    public function admins(
        string $title,
        string $message,
        string $url,
        string $tone = 'blue',
        array $metadata = [],
        ?int $exceptUserId = null,
    ): void {
        $admins = User::query()
            ->where('role', 'administrador')
            ->whereNotNull('approved_at')
            ->when($exceptUserId, fn ($query) => $query->whereKeyNot($exceptUserId))
            ->get();

        $this->send($admins, $title, $message, $url, $tone, $metadata);
    }

    public function user(
        ?User $user,
        string $title,
        string $message,
        string $url,
        string $tone = 'blue',
        array $metadata = [],
    ): void {
        if (! $user) {
            return;
        }

        $this->send(collect([$user]), $title, $message, $url, $tone, $metadata);
    }

    private function send(
        Collection $users,
        string $title,
        string $message,
        string $url,
        string $tone,
        array $metadata,
    ): void {
        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            $user->notify(new InventoryNotification($title, $message, $url, $tone, $metadata));

            try {
                InventoryNotificationCreated::dispatch($user->id, [
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                    'tone' => $tone,
                    'read' => false,
                    'created_at' => 'Ahora',
                ]);
            } catch (Throwable $exception) {
                // La notificacion ya quedo guardada; Reverb no debe bloquear la operacion.
                Log::notice('No se pudo emitir una notificacion en tiempo real.', [
                    'user_id' => $user->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }
    }
}
