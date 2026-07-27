<?php

namespace App\Support;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatConversationExport
{
    public function __construct(private readonly ChatFeatures $features) {}

    public function download(User $firstUser, User $secondUser): StreamedResponse
    {
        $filename = sprintf(
            'conversacion-%s-%s-%s.txt',
            Str::slug($firstUser->name),
            Str::slug($secondUser->name),
            now()->format('Ymd-His'),
        );

        return response()->streamDownload(function () use ($firstUser, $secondUser): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fwrite($output, "CONVERSACION INTERNA LUGARTH\r\n");
            fwrite($output, "Participantes: {$firstUser->name} y {$secondUser->name}\r\n");
            fwrite($output, 'Descargada: '.now()->format('d/m/Y H:i:s')."\r\n");
            fwrite($output, str_repeat('=', 72)."\r\n\r\n");

            DirectMessage::query()
                ->between($firstUser->id, $secondUser->id)
                ->with(['sender:id,name', 'replyTo:id,sender_id,body,message_type,sticker_key'])
                ->orderBy('id')
                ->lazyById(200)
                ->each(function (DirectMessage $message) use ($output): void {
                    $sender = $message->sender?->name ?? 'Usuario eliminado';
                    $status = $message->read_at
                        ? 'Leido'
                        : ($message->delivered_at ? 'Entregado' : 'Enviado');
                    $pinned = $message->pinned_at ? ' | FIJADO' : '';

                    fwrite(
                        $output,
                        sprintf(
                            "[%s] %s | %s%s\r\n",
                            $message->created_at?->format('d/m/Y H:i:s'),
                            $sender,
                            $status,
                            $pinned,
                        ),
                    );

                    if ($message->replyTo) {
                        fwrite(
                            $output,
                            '> Respuesta a: '.$this->summary($message->replyTo)."\r\n",
                        );
                    }

                    fwrite($output, $this->summary($message)."\r\n\r\n");
                });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function summary(DirectMessage $message): string
    {
        if ($message->message_type === 'sticker') {
            $sticker = $this->features->sticker($message->sticker_key);

            return '[Sticker: '.($sticker['label'] ?? 'No disponible').']';
        }

        return trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $message->body)));
    }
}
