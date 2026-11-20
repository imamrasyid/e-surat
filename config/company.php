<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company Identity
    |--------------------------------------------------------------------------
    |
    | Setiap environment produksi mewakili tepat satu perusahaan. Nilai ini
    | dipakai untuk audit dan observability, bukan untuk memilih data: isolasi
    | data dilakukan oleh server, database, dan storage terpisah.
    |
    */

    'key' => env('COMPANY_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Feature Gates
    |--------------------------------------------------------------------------
    |
    | Feature opsional dipilih per server lewat COMPANY_FEATURES (dipisah
    | koma). Feature yang tidak tercantum di sini mati secara default
    | (fail closed); route, Action, dan notification memakai gate yang sama.
    | Feature aktif wajib memenuhi seluruh aturan feature_requirements.
    |
    */

    'features' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('COMPANY_FEATURES', ''))
    ))),

    'known_features' => [
        'email',
        'telegram',
        'webpush',
    ],

    'feature_requirements' => [
        // Notifikasi email hanya aktif bila mailer benar-benar mengirim
        // (bukan `log`/`array`) dan alamat pengirim sudah diisi.
        'email' => [
            'required' => [
                'mail.from.address',
            ],
            'not_in' => [
                'mail.default' => ['log', 'array'],
            ],
        ],

        'telegram' => [
            'required' => [
                'services.telegram.bot_token',
                'services.telegram.bot_username',
                'services.telegram.webhook_secret',
            ],
        ],

        // Web Push juga mensyaratkan APP_URL HTTPS pada hostname DNS; syarat
        // runtime itu diperiksa CompanyFeature::webPushEligible() agar tenant
        // HTTP/IP tetap memakai notifikasi database.
        'webpush' => [
            'required' => [
                'webpush.vapid.subject',
                'webpush.vapid.public_key',
                'webpush.vapid.private_key',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Ruleset
    |--------------------------------------------------------------------------
    |
    | Perbedaan aturan bisnis dipilih lewat ruleset, bukan branch perusahaan.
    | Ruleset yang tidak terdaftar menggagalkan `company:validate-config`
    | sehingga deploy berhenti sebelum artifact dipromosikan.
    |
    */

    'ruleset' => env('COMPANY_RULESET', 'default'),

    'rulesets' => [
        'default',
    ],

];
