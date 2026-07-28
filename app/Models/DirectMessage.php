<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectMessage extends Model
{
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'reply_to_id',
        'body',
        'message_type',
        'sticker_key',
        'delivered_at',
        'read_at',
        'pinned_at',
        'pinned_by_id',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'pinned_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    public function pinnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by_id');
    }

    public function scopeBetween(Builder $query, int $firstUserId, int $secondUserId): Builder
    {
        return $query->where(function (Builder $conversation) use ($firstUserId, $secondUserId): void {
            $conversation
                ->where(function (Builder $direction) use ($firstUserId, $secondUserId): void {
                    $direction->where('sender_id', $firstUserId)
                        ->where('recipient_id', $secondUserId);
                })
                ->orWhere(function (Builder $direction) use ($firstUserId, $secondUserId): void {
                    $direction->where('sender_id', $secondUserId)
                        ->where('recipient_id', $firstUserId);
                });
        });
    }
}
