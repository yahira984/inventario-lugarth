<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /** @param array<string, mixed> $notification */
    public function __construct(
        private readonly int $userId,
        private readonly array $notification,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.'.$this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'inventory.notification';
    }

    /** @return array{notification: array<string, mixed>} */
    public function broadcastWith(): array
    {
        return ['notification' => $this->notification];
    }
}
