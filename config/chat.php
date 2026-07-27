<?php

return [
    'visible_messages' => 80,
    'typing_ttl_seconds' => 6,
    'max_pinned_per_conversation' => 25,

    /*
    |--------------------------------------------------------------------------
    | Lightweight stickers
    |--------------------------------------------------------------------------
    |
    | Messages store only the key. To replace an emoji with a custom sticker,
    | add the matching WebP file under public/images/stickers.
    |
    */
    'stickers' => [
        'bien_hecho' => [
            'label' => 'Bien hecho',
            'emoji' => "\u{1F44D}",
            'image' => 'images/stickers/bien-hecho.webp',
        ],
        'listo' => [
            'label' => 'Listo',
            'emoji' => "\u{2705}",
            'image' => 'images/stickers/listo.webp',
        ],
        'trabajando' => [
            'label' => 'Trabajando',
            'emoji' => "\u{1F6E0}\u{FE0F}",
            'image' => 'images/stickers/trabajando.webp',
        ],
        'recibido' => [
            'label' => 'Recibido',
            'emoji' => "\u{1F4E6}",
            'image' => 'images/stickers/recibido.webp',
        ],
        'urgente' => [
            'label' => 'Urgente',
            'emoji' => "\u{1F6A8}",
            'image' => 'images/stickers/urgente.webp',
        ],
    ],
];
