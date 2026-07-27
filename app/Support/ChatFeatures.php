<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ChatFeatures
{
    /**
     * @return array<int, array{key: string, label: string, emoji: string, image_url: ?string}>
     */
    public function stickers(): array
    {
        return collect(config('chat.stickers', []))
            ->map(function (array $sticker, string $key): array {
                $image = $sticker['image'] ?? null;

                return [
                    'key' => $key,
                    'label' => (string) ($sticker['label'] ?? $key),
                    'emoji' => (string) ($sticker['emoji'] ?? ''),
                    'image_url' => $image && is_file(public_path($image))
                        ? asset($image)
                        : null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{key: string, label: string, emoji: string, image_url: ?string}|null
     */
    public function sticker(?string $key): ?array
    {
        if (! $key) {
            return null;
        }

        return collect($this->stickers())->firstWhere('key', $key);
    }

    /**
     * @return array<int, string>
     */
    public function stickerKeys(): array
    {
        return array_keys(config('chat.stickers', []));
    }

    public function setTyping(User $sender, User $recipient, bool $typing): void
    {
        $key = $this->typingKey($sender->id, $recipient->id);
        $cache = Cache::store('file');

        if (! $typing) {
            $cache->forget($key);

            return;
        }

        $cache->put(
            $key,
            true,
            now()->addSeconds((int) config('chat.typing_ttl_seconds', 6)),
        );
    }

    public function isTyping(User $sender, User $recipient): bool
    {
        return (bool) Cache::store('file')->get(
            $this->typingKey($sender->id, $recipient->id),
            false,
        );
    }

    public function availabilityLabel(?string $status): string
    {
        return match ($status) {
            'busy' => 'Ocupado',
            'away' => 'Fuera',
            default => 'Disponible',
        };
    }

    private function typingKey(int $senderId, int $recipientId): string
    {
        return "chat:typing:{$senderId}:{$recipientId}";
    }
}
