<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Proksi tepercaya (trusted proxies)
    |--------------------------------------------------------------------------
    |
    | Daftar IP/CIDR proksi yang berhak menyetel header X-Forwarded-For dan
    | X-Forwarded-Proto, dipisah koma. Bila kosong, hanya loopback yang
    | dipercaya — perilaku yang tepat untuk compose yang mem-publish port
    | aplikasi ke 127.0.0.1 dan proxy di host yang sama.
    |
    | Docker publish dari proxy di HOST (Caddy/Nginx → 127.0.0.1:<port>) masuk
    | ke container lewat docker-proxy/gateway bridge (172.16.0.0/12), sehingga
    | IP asli klien dan skema HTTPS hilang bila rentang itu tidak dipercaya.
    |
    | Contoh untuk topologi proxy-di-host:
    |
    |     TRUSTED_PROXIES=127.0.0.1,::1,172.16.0.0/12
    |
    | Jangan memakai "*" pada aplikasi yang menerima trafik langsung dari
    | internet: header palsu dari klien mana pun akan diterima.
    */

    'proxies' => env('TRUSTED_PROXIES', '127.0.0.1,::1'),
];
