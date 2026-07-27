<?php

namespace App\Http\Controllers;

use App\Models\DirectMessage;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\ChatConversationExport;
use App\Support\ChatRetention;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminChatController extends Controller
{
    public function index(Request $request, ChatRetention $retention): View
    {
        $this->ensureAdmin($request);

        $messageStats = DirectMessage::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread')
            ->selectRaw('SUM(CASE WHEN pinned_at IS NOT NULL THEN 1 ELSE 0 END) as pinned')
            ->selectRaw('COALESCE(SUM(LENGTH(body)), 0) as body_bytes')
            ->selectRaw('MIN(created_at) as oldest_at')
            ->first();

        $totalMessages = (int) ($messageStats?->total ?? 0);
        $estimatedBytes = (int) ($messageStats?->body_bytes ?? 0) + ($totalMessages * 220);

        return view('admin.chats.index', [
            'retentionDays' => $retention->days(),
            'retentionLabel' => $retention->label(),
            'totalMessages' => $totalMessages,
            'unreadMessages' => (int) ($messageStats?->unread ?? 0),
            'pinnedMessages' => (int) ($messageStats?->pinned ?? 0),
            'oldestMessageAt' => $messageStats?->oldest_at,
            'estimatedSize' => $this->formatBytes($estimatedBytes),
            'conversations' => $this->conversations(),
        ]);
    }

    public function updateRetention(
        Request $request,
        ChatRetention $retention,
    ): RedirectResponse {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'retention_days' => ['required', 'integer', 'in:0,7,30,90'],
        ], [
            'retention_days.in' => 'Selecciona un periodo de retención válido.',
        ]);

        $days = (int) $validated['retention_days'];
        $retention->setDays($days);

        AuditLogger::registrar(
            'Chat interno',
            'Cambio de retención',
            $days === 0
                ? 'Desactivó la eliminación automática de mensajes.'
                : "Configuró la conservación de mensajes durante {$days} días.",
            ['retencion_dias' => $days],
            $request,
        );

        return back()->with(
            'success',
            $days === 0
                ? 'Los mensajes se conservarán hasta que un administrador los elimine.'
                : "Los mensajes se conservarán durante {$days} días.",
        );
    }

    public function purgeExpired(
        Request $request,
        ChatRetention $retention,
    ): RedirectResponse {
        $this->ensureAdmin($request);

        $days = $retention->days();

        if ($days === 0) {
            return back()->with('warning', 'Activa un periodo de retención antes de limpiar mensajes antiguos.');
        }

        $deleted = $retention->purgeExpired();

        AuditLogger::registrar(
            'Chat interno',
            'Limpieza manual',
            "Ejecutó la limpieza de mensajes antiguos. Se eliminaron {$deleted} mensajes.",
            [
                'mensajes_eliminados' => $deleted,
                'retencion_dias' => $days,
            ],
            $request,
        );

        return back()->with(
            'success',
            $deleted === 1
                ? 'Se eliminó 1 mensaje antiguo.'
                : "Se eliminaron {$deleted} mensajes antiguos.",
        );
    }

    public function destroyConversation(
        Request $request,
        User $firstUser,
        User $secondUser,
    ): RedirectResponse {
        $this->ensureAdmin($request);
        abort_if($firstUser->is($secondUser), 422);

        $deleted = DirectMessage::query()
            ->where(function ($query) use ($firstUser, $secondUser): void {
                $query->where('sender_id', $firstUser->id)
                    ->where('recipient_id', $secondUser->id);
            })
            ->orWhere(function ($query) use ($firstUser, $secondUser): void {
                $query->where('sender_id', $secondUser->id)
                    ->where('recipient_id', $firstUser->id);
            })
            ->delete();

        AuditLogger::registrar(
            'Chat interno',
            'Conversación eliminada',
            "Eliminó la conversación entre {$firstUser->name} y {$secondUser->name}.",
            [
                'primer_usuario_id' => $firstUser->id,
                'segundo_usuario_id' => $secondUser->id,
                'mensajes_eliminados' => $deleted,
            ],
            $request,
        );

        return back()->with(
            'success',
            $deleted === 1
                ? 'Se eliminó 1 mensaje de la conversación.'
                : "Se eliminaron {$deleted} mensajes de la conversación.",
        );
    }

    public function exportConversation(
        Request $request,
        User $firstUser,
        User $secondUser,
        ChatConversationExport $exporter,
    ): StreamedResponse {
        $this->ensureAdmin($request);
        abort_if($firstUser->is($secondUser), 422);

        return $exporter->download($firstUser, $secondUser);
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $deleted = DirectMessage::query()
            ->whereNull('pinned_at')
            ->delete();
        $preserved = DirectMessage::query()
            ->whereNotNull('pinned_at')
            ->count();

        AuditLogger::registrar(
            'Chat interno',
            'Limpieza total',
            "Eliminó el historial no fijado del chat interno: {$deleted} mensajes.",
            [
                'mensajes_eliminados' => $deleted,
                'mensajes_fijados_conservados' => $preserved,
            ],
            $request,
        );

        return back()->with(
            'success',
            "Se eliminaron {$deleted} mensajes. Se conservaron {$preserved} mensajes fijados.",
        );
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function conversations(): Collection
    {
        $directions = DirectMessage::query()
            ->select(['sender_id', 'recipient_id'])
            ->selectRaw('COUNT(*) as total_messages')
            ->selectRaw('SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread_messages')
            ->selectRaw('SUM(CASE WHEN pinned_at IS NOT NULL THEN 1 ELSE 0 END) as pinned_messages')
            ->selectRaw('COALESCE(SUM(LENGTH(body)), 0) as body_bytes')
            ->selectRaw('MAX(created_at) as last_message_at')
            ->groupBy('sender_id', 'recipient_id')
            ->get();

        $grouped = $directions->reduce(function (Collection $result, DirectMessage $row): Collection {
            $firstId = min($row->sender_id, $row->recipient_id);
            $secondId = max($row->sender_id, $row->recipient_id);
            $key = "{$firstId}:{$secondId}";
            $conversation = $result->get($key, [
                'first_user_id' => $firstId,
                'second_user_id' => $secondId,
                'total_messages' => 0,
                'unread_messages' => 0,
                'pinned_messages' => 0,
                'body_bytes' => 0,
                'last_message_at' => null,
            ]);

            $conversation['total_messages'] += (int) $row->total_messages;
            $conversation['unread_messages'] += (int) $row->unread_messages;
            $conversation['pinned_messages'] += (int) $row->pinned_messages;
            $conversation['body_bytes'] += (int) $row->body_bytes;

            if ($conversation['last_message_at'] === null || $row->last_message_at > $conversation['last_message_at']) {
                $conversation['last_message_at'] = $row->last_message_at;
            }

            return $result->put($key, $conversation);
        }, collect());

        $users = User::query()
            ->whereIn('id', $grouped->flatMap(
                fn (array $conversation): array => [
                    $conversation['first_user_id'],
                    $conversation['second_user_id'],
                ],
            )->unique())
            ->get()
            ->keyBy('id');

        return $grouped
            ->map(function (array $conversation) use ($users): array {
                $firstUser = $users->get($conversation['first_user_id']);
                $secondUser = $users->get($conversation['second_user_id']);

                return [
                    ...$conversation,
                    'first_user' => $firstUser,
                    'second_user' => $secondUser,
                    'estimated_size' => $this->formatBytes(
                        $conversation['body_bytes'] + ($conversation['total_messages'] * 220),
                    ),
                ];
            })
            ->filter(fn (array $conversation): bool => $conversation['first_user'] && $conversation['second_user'])
            ->sortByDesc('last_message_at')
            ->values();
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return "{$bytes} B";
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / (1024 * 1024), 1).' MB';
    }
}
