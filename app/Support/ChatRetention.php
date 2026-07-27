<?php

namespace App\Support;

use App\Models\DirectMessage;
use App\Models\SystemSetting;
use InvalidArgumentException;

class ChatRetention
{
    public const DEFAULT_DAYS = 30;

    public const ALLOWED_DAYS = [0, 7, 30, 90];

    private const SETTING_KEY = 'chat_retention_days';

    public function days(): int
    {
        $days = (int) SystemSetting::valueFor(
            self::SETTING_KEY,
            (string) self::DEFAULT_DAYS,
        );

        return in_array($days, self::ALLOWED_DAYS, true)
            ? $days
            : self::DEFAULT_DAYS;
    }

    public function setDays(int $days): void
    {
        if (! in_array($days, self::ALLOWED_DAYS, true)) {
            throw new InvalidArgumentException('La retención seleccionada no es válida.');
        }

        SystemSetting::put(self::SETTING_KEY, (string) $days);
    }

    public function purgeExpired(?int $days = null): int
    {
        $days ??= $this->days();

        if ($days === 0) {
            return 0;
        }

        return DirectMessage::query()
            ->where('created_at', '<', now()->subDays($days))
            ->whereNull('pinned_at')
            ->delete();
    }

    public function label(?int $days = null): string
    {
        $days ??= $this->days();

        return $days === 0
            ? 'Sin eliminación automática'
            : "{$days} días";
    }
}
