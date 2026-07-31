<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\InventoryNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

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

        Notification::send(
            $users,
            new InventoryNotification($title, $message, $url, $tone, $metadata)
        );
    }
}
