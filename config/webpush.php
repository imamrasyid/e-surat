<?php

use NotificationChannels\WebPush\PushSubscription;

return [

    /*
    |--------------------------------------------------------------------------
    | VAPID Authentication
    |--------------------------------------------------------------------------
    |
    | Kunci VAPID harus stabil: mengganti kunci membatalkan seluruh subscription
    | browser yang sudah ada. Web Push hanya aktif bila feature `webpush`
    | dinyalakan dan ketiga nilai ini terisi (lihat CompanyFeature).
    |
    */

    'vapid' => [
        'subject' => env('VAPID_SUBJECT'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'pem_file' => env('VAPID_PEM_FILE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Model & Table
    |--------------------------------------------------------------------------
    */

    'model' => PushSubscription::class,

    'table_name' => env('WEBPUSH_DB_TABLE', 'push_subscriptions'),

    'database_connection' => env('WEBPUSH_DB_CONNECTION', env('DB_CONNECTION', 'mysql')),

    'client_options' => [],

    /*
    |--------------------------------------------------------------------------
    | Padding
    |--------------------------------------------------------------------------
    |
    | Padding otomatis dari Minishlink\WebPush. Matikan hanya bila perlu
    | mendukung Firefox Android dengan endpoint v1.
    |
    */

    'automatic_padding' => env('WEBPUSH_AUTOMATIC_PADDING', true),

];
