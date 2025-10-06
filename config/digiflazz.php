<?php

return [
    'base_url' => env('DIGIFLAZZ_BASE_URL', 'https://api.digiflazz.com/v1'),
    'username' => env('DIGIFLAZZ_USERNAME', ''),
    'api_key'  => env('DIGIFLAZZ_API_KEY', ''),
    'webhook_secret' => env('DIGIFLAZZ_WEBHOOK_SECRET', ''),
    'timeout' => env('DIGIFLAZZ_HTTP_TIMEOUT', 15),
    'retry' => [
        'times' => env('DIGIFLAZZ_HTTP_RETRY_TIMES', 2),
        'sleep_ms' => env('DIGIFLAZZ_HTTP_RETRY_SLEEP_MS', 200),
    ],
    'ip_whitelist' => ['52.74.250.133'],
];
