<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Support\ChatRetention;
use Illuminate\Console\Command;

class PurgeDirectMessages extends Command
{
    protected $signature = 'chat:limpiar
        {--days= : Usa temporalmente una retención distinta en días}';

    protected $description = 'Elimina mensajes del chat interno que superaron el periodo de retención.';

    public function handle(ChatRetention $retention): int
    {
        $days = $this->option('days');

        if ($days !== null && (! ctype_digit((string) $days) || (int) $days < 1)) {
            $this->error('La opción --days debe ser un número entero mayor a cero.');

            return self::FAILURE;
        }

        $retentionDays = $days === null ? $retention->days() : (int) $days;

        if ($retentionDays === 0) {
            $this->info('La limpieza automática del chat está desactivada.');

            return self::SUCCESS;
        }

        $deleted = $retention->purgeExpired($retentionDays);

        if ($deleted > 0) {
            AuditLog::create([
                'user_id' => null,
                'modulo' => 'Chat interno',
                'accion' => 'Limpieza automática',
                'descripcion' => "El sistema eliminó {$deleted} mensajes con más de {$retentionDays} días.",
                'ruta' => 'artisan chat:limpiar',
                'ip' => null,
                'datos' => [
                    'mensajes_eliminados' => $deleted,
                    'retencion_dias' => $retentionDays,
                ],
            ]);
        }

        $this->info("Limpieza terminada: {$deleted} mensajes eliminados.");

        return self::SUCCESS;
    }
}
