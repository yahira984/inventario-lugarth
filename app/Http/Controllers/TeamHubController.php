<?php

namespace App\Http\Controllers;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeamHubController extends Controller
{
    public function presence(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        DB::table('users')
            ->where('id', $currentUser->id)
            ->update(['last_seen_at' => now()]);

        $unreadBySender = DirectMessage::query()
            ->where('recipient_id', $currentUser->id)
            ->whereNull('read_at')
            ->selectRaw('sender_id, COUNT(*) as total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $latestMessages = DirectMessage::query()
            ->where('sender_id', $currentUser->id)
            ->orWhere('recipient_id', $currentUser->id)
            ->latest('id')
            ->get()
            ->unique(fn (DirectMessage $message): int => $message->sender_id === $currentUser->id
                ? $message->recipient_id
                : $message->sender_id)
            ->keyBy(fn (DirectMessage $message): int => $message->sender_id === $currentUser->id
                ? $message->recipient_id
                : $message->sender_id);

        $users = User::query()
            ->whereNotNull('approved_at')
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($currentUser, $latestMessages, $unreadBySender): array {
                $online = $user->isOnline();
                $latestMessage = $latestMessages->get($user->id);

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
                    'last_seen' => $online
                        ? 'Activo ahora'
                        : ($user->last_seen_at?->diffForHumans() ?? 'Sin actividad reciente'),
                    'unread_count' => (int) ($unreadBySender[$user->id] ?? 0),
                    'has_conversation' => $latestMessage !== null,
                    'last_message' => $latestMessage ? Str::limit($latestMessage->body, 72) : null,
                    'last_message_at' => $latestMessage?->created_at?->diffForHumans(),
                    'last_message_id' => $latestMessage?->id,
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

    public function messages(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        $this->ensureConversationAllowed($currentUser, $user);

        $messages = DirectMessage::query()
            ->where(function ($query) use ($currentUser, $user): void {
                $query->where('sender_id', $currentUser->id)
                    ->where('recipient_id', $user->id);
            })
            ->orWhere(function ($query) use ($currentUser, $user): void {
                $query->where('sender_id', $user->id)
                    ->where('recipient_id', $currentUser->id);
            })
            ->latest('id')
            ->limit(80)
            ->get()
            ->sortBy('id')
            ->values();

        DirectMessage::query()
            ->where('sender_id', $user->id)
            ->where('recipient_id', $currentUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role_label' => $this->roleLabel($user->role),
                'online' => $user->isOnline(),
                'avatar_url' => $user->avatar ? asset('storage/'.$user->avatar) : null,
                'initials' => $this->initials($user->name),
            ],
            'messages' => $messages->map(fn (DirectMessage $message): array => [
                'id' => $message->id,
                'body' => $message->body,
                'mine' => $message->sender_id === $currentUser->id,
                'read' => $message->read_at !== null,
                'created_at' => $message->created_at?->format('d/m/Y H:i'),
            ]),
        ]);
    }

    public function send(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        $this->ensureConversationAllowed($currentUser, $user);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ], [
            'body.required' => 'Escribe un mensaje antes de enviarlo.',
            'body.max' => 'El mensaje no debe superar 1000 caracteres.',
        ]);

        $body = trim($validated['body']);

        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => 'Escribe un mensaje antes de enviarlo.',
            ]);
        }

        $message = DirectMessage::create([
            'sender_id' => $currentUser->id,
            'recipient_id' => $user->id,
            'body' => $body,
        ]);

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'mine' => true,
                'read' => false,
                'created_at' => $message->created_at?->format('d/m/Y H:i'),
            ],
        ], 201);
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
