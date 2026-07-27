<?php

namespace App\Http\Controllers;

use App\Models\DirectMessage;
use App\Models\User;
use App\Support\ChatConversationExport;
use App\Support\ChatFeatures;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeamHubController extends Controller
{
    public function presence(Request $request, ChatFeatures $features): JsonResponse
    {
        $currentUser = $request->user();

        DB::table('users')
            ->where('id', $currentUser->id)
            ->update(['last_seen_at' => now()]);

        DirectMessage::query()
            ->where('recipient_id', $currentUser->id)
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);

        $unreadBySender = DirectMessage::query()
            ->where('recipient_id', $currentUser->id)
            ->whereNull('read_at')
            ->selectRaw('sender_id, COUNT(*) as total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $latestMessageIds = DirectMessage::query()
            ->where('sender_id', $currentUser->id)
            ->orWhere('recipient_id', $currentUser->id)
            ->selectRaw('MAX(id) as latest_id')
            ->groupByRaw(
                'CASE WHEN sender_id = ? THEN recipient_id ELSE sender_id END',
                [$currentUser->id],
            )
            ->pluck('latest_id');

        $latestMessages = DirectMessage::query()
            ->whereKey($latestMessageIds)
            ->get()
            ->keyBy(fn (DirectMessage $message): int => $message->sender_id === $currentUser->id
                ? $message->recipient_id
                : $message->sender_id);

        $users = User::query()
            ->whereNotNull('approved_at')
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($currentUser, $features, $latestMessages, $unreadBySender): array {
                $online = $user->isOnline();
                $latestMessage = $latestMessages->get($user->id);
                $availability = $user->availability_status ?: 'available';

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'role_label' => $this->roleLabel($user->role),
                    'avatar_url' => $user->avatar
                        ? asset('storage/'.$user->avatar)
                        : null,
                    'initials' => $this->initials($user->name),
                    'online' => $online,
                    'is_self' => $user->is($currentUser),
                    'availability_status' => $availability,
                    'availability_label' => $features->availabilityLabel($availability),
                    'last_seen' => $online
                        ? 'Activo ahora'
                        : ($user->last_seen_at?->diffForHumans() ?? 'Sin actividad reciente'),
                    'unread_count' => (int) ($unreadBySender[$user->id] ?? 0),
                    'has_conversation' => $latestMessage !== null,
                    'last_message' => $latestMessage
                        ? Str::limit($this->messageSummary($latestMessage, $features), 72)
                        : null,
                    'last_message_at' => $latestMessage?->created_at?->diffForHumans(),
                    'last_message_id' => $latestMessage?->id,
                    'last_message_mine' => $latestMessage?->sender_id === $currentUser->id,
                ];
            })
            ->sortBy(fn (array $user): string => sprintf(
                '%d-%s',
                $user['online'] ? 0 : 1,
                mb_strtolower($user['name'])
            ))
            ->values();

        return response()->json([
            'online_count' => $users->where('online', true)->count(),
            'unread_total' => $users->sum('unread_count'),
            'users' => $users,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function messages(Request $request, User $user, ChatFeatures $features): JsonResponse
    {
        $currentUser = $request->user();
        $this->ensureConversationAllowed($currentUser, $user);

        DB::table('users')
            ->where('id', $currentUser->id)
            ->update(['last_seen_at' => now()]);

        DirectMessage::query()
            ->where('sender_id', $user->id)
            ->where('recipient_id', $currentUser->id)
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);

        DirectMessage::query()
            ->where('sender_id', $user->id)
            ->where('recipient_id', $currentUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = DirectMessage::query()
            ->between($currentUser->id, $user->id)
            ->with([
                'replyTo:id,sender_id,body,message_type,sticker_key',
                'pinnedBy:id,name',
            ])
            ->latest('id')
            ->limit((int) config('chat.visible_messages', 80))
            ->get()
            ->sortBy('id')
            ->values();

        $pinnedMessages = DirectMessage::query()
            ->between($currentUser->id, $user->id)
            ->whereNotNull('pinned_at')
            ->latest('pinned_at')
            ->limit(5)
            ->get()
            ->map(fn (DirectMessage $message): array => [
                'id' => $message->id,
                'preview' => Str::limit($this->messageSummary($message, $features), 90),
                'sender' => $message->sender_id === $currentUser->id ? 'Tu' : $user->name,
            ])
            ->values();

        return response()->json([
            'user' => $this->userPayload($user, $features),
            'messages' => $messages->map(
                fn (DirectMessage $message): array => $this->messagePayload(
                    $message,
                    $currentUser,
                    $user,
                    $features,
                ),
            ),
            'pinned_messages' => $pinnedMessages,
            'typing' => $features->isTyping($user, $currentUser),
            'stickers' => $features->stickers(),
        ]);
    }

    public function send(Request $request, User $user, ChatFeatures $features): JsonResponse
    {
        $currentUser = $request->user();
        $this->ensureConversationAllowed($currentUser, $user);

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:1000', 'required_without:sticker_key'],
            'sticker_key' => [
                'nullable',
                'string',
                Rule::in($features->stickerKeys()),
                'required_without:body',
            ],
            'reply_to_id' => ['nullable', 'integer', 'exists:direct_messages,id'],
        ], [
            'body.required_without' => 'Escribe un mensaje o selecciona un sticker.',
            'body.max' => 'El mensaje no debe superar 1000 caracteres.',
            'sticker_key.required_without' => 'Escribe un mensaje o selecciona un sticker.',
            'sticker_key.in' => 'El sticker seleccionado no está disponible.',
        ]);

        $body = trim((string) ($validated['body'] ?? ''));
        $stickerKey = $validated['sticker_key'] ?? null;

        if ($body === '' && ! $stickerKey) {
            throw ValidationException::withMessages([
                'body' => 'Escribe un mensaje o selecciona un sticker.',
            ]);
        }

        if ($body !== '' && $stickerKey) {
            throw ValidationException::withMessages([
                'body' => 'Envía el texto o el sticker por separado.',
            ]);
        }

        $replyTo = null;
        if (! empty($validated['reply_to_id'])) {
            $replyTo = DirectMessage::query()->findOrFail($validated['reply_to_id']);
            $this->ensureMessageBelongsToConversation($replyTo, $currentUser, $user);
        }

        $message = DirectMessage::create([
            'sender_id' => $currentUser->id,
            'recipient_id' => $user->id,
            'reply_to_id' => $replyTo?->id,
            'body' => $stickerKey ? '' : $body,
            'message_type' => $stickerKey ? 'sticker' : 'text',
            'sticker_key' => $stickerKey,
        ]);

        $features->setTyping($currentUser, $user, false);
        $message->load('replyTo:id,sender_id,body,message_type,sticker_key');

        return response()->json([
            'message' => $this->messagePayload($message, $currentUser, $user, $features),
        ], 201);
    }

    public function typing(Request $request, User $user, ChatFeatures $features): JsonResponse
    {
        $currentUser = $request->user();
        $this->ensureConversationAllowed($currentUser, $user);

        $validated = $request->validate([
            'typing' => ['required', 'boolean'],
        ]);

        $features->setTyping($currentUser, $user, (bool) $validated['typing']);

        return response()->json(['ok' => true]);
    }

    public function updateAvailability(Request $request, ChatFeatures $features): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['available', 'busy', 'away'])],
        ], [
            'status.in' => 'Selecciona un estado válido.',
        ]);

        $request->user()->forceFill([
            'availability_status' => $validated['status'],
            'last_seen_at' => now(),
        ])->save();

        return response()->json([
            'status' => $validated['status'],
            'label' => $features->availabilityLabel($validated['status']),
        ]);
    }

    public function pin(
        Request $request,
        DirectMessage $message,
        ChatFeatures $features,
    ): JsonResponse {
        $currentUser = $request->user();
        $otherUser = $this->otherParticipant($message, $currentUser);
        $this->ensureConversationAllowed($currentUser, $otherUser);

        $validated = $request->validate([
            'pinned' => ['required', 'boolean'],
        ]);
        $shouldPin = (bool) $validated['pinned'];

        if ($shouldPin && $message->pinned_at === null) {
            $pinnedCount = DirectMessage::query()
                ->between($currentUser->id, $otherUser->id)
                ->whereNotNull('pinned_at')
                ->count();

            if ($pinnedCount >= (int) config('chat.max_pinned_per_conversation', 25)) {
                throw ValidationException::withMessages([
                    'pinned' => 'Esta conversación ya tiene el máximo de mensajes fijados.',
                ]);
            }
        }

        $message->forceFill([
            'pinned_at' => $shouldPin ? now() : null,
            'pinned_by_id' => $shouldPin ? $currentUser->id : null,
        ])->save();
        $message->load([
            'replyTo:id,sender_id,body,message_type,sticker_key',
            'pinnedBy:id,name',
        ]);

        return response()->json([
            'message' => $this->messagePayload(
                $message,
                $currentUser,
                $otherUser,
                $features,
            ),
        ]);
    }

    public function export(
        Request $request,
        User $user,
        ChatConversationExport $exporter,
    ): StreamedResponse {
        $currentUser = $request->user();
        $this->ensureConversationAllowed($currentUser, $user);

        return $exporter->download($currentUser, $user);
    }

    private function messagePayload(
        DirectMessage $message,
        User $currentUser,
        User $otherUser,
        ChatFeatures $features,
    ): array {
        $mine = $message->sender_id === $currentUser->id;

        return [
            'id' => $message->id,
            'body' => $message->body,
            'message_type' => $message->message_type ?: 'text',
            'sticker' => $features->sticker($message->sticker_key),
            'mine' => $mine,
            'delivered' => $message->delivered_at !== null,
            'read' => $message->read_at !== null,
            'status_label' => $mine
                ? ($message->read_at ? 'Leído' : ($message->delivered_at ? 'Entregado' : 'Enviado'))
                : null,
            'pinned' => $message->pinned_at !== null,
            'pinned_by' => $message->pinnedBy?->name,
            'reply' => $message->replyTo ? [
                'id' => $message->replyTo->id,
                'sender' => $message->replyTo->sender_id === $currentUser->id
                    ? 'Tú'
                    : $otherUser->name,
                'preview' => Str::limit($this->messageSummary($message->replyTo, $features), 100),
            ] : null,
            'created_at' => $message->created_at?->format('d/m/Y H:i'),
        ];
    }

    private function userPayload(User $user, ChatFeatures $features): array
    {
        $availability = $user->availability_status ?: 'available';

        return [
            'id' => $user->id,
            'name' => $user->name,
            'role_label' => $this->roleLabel($user->role),
            'online' => $user->isOnline(),
            'availability_status' => $availability,
            'availability_label' => $features->availabilityLabel($availability),
            'last_seen' => $user->isOnline()
                ? 'Activo ahora'
                : ($user->last_seen_at?->diffForHumans() ?? 'Sin actividad reciente'),
            'avatar_url' => $user->avatar ? asset('storage/'.$user->avatar) : null,
            'initials' => $this->initials($user->name),
        ];
    }

    private function messageSummary(DirectMessage $message, ChatFeatures $features): string
    {
        if ($message->message_type === 'sticker') {
            $sticker = $features->sticker($message->sticker_key);

            return '[Sticker: '.($sticker['label'] ?? 'No disponible').']';
        }

        return trim((string) $message->body);
    }

    private function ensureMessageBelongsToConversation(
        DirectMessage $message,
        User $currentUser,
        User $otherUser,
    ): void {
        $participantIds = [$message->sender_id, $message->recipient_id];

        abort_unless(
            in_array($currentUser->id, $participantIds, true)
            && in_array($otherUser->id, $participantIds, true),
            422,
            'El mensaje respondido no pertenece a esta conversación.',
        );
    }

    private function otherParticipant(DirectMessage $message, User $currentUser): User
    {
        abort_unless(
            in_array($currentUser->id, [$message->sender_id, $message->recipient_id], true),
            403,
        );

        $otherUserId = $message->sender_id === $currentUser->id
            ? $message->recipient_id
            : $message->sender_id;

        return User::query()->findOrFail($otherUserId);
    }

    private function ensureConversationAllowed(User $currentUser, User $otherUser): void
    {
        abort_if($currentUser->is($otherUser), 422, 'No puedes abrir una conversación contigo mismo.');
        abort_unless($otherUser->aprobado(), 404);
    }

    private function roleLabel(?string $role): string
    {
        return match ($role) {
            'administrador' => 'Administrador',
            'almacenista' => 'Almacenista',
            'consultor' => 'Consultor',
            default => 'Usuario',
        };
    }

    private function initials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'U';
    }
}
