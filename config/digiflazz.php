<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    | Default Digiflazz Buyer API endpoint.
    | Change only if Digiflazz provides a different base URL.
    */
    'base_url' => env('DIGIFLAZZ_BASE_URL', 'https://api.digiflazz.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Digiflazz Username
    |--------------------------------------------------------------------------
    | Your Buyer account username from the Digiflazz dashboard:
    | Menu: "Atur Koneksi" > "API".
    | Never expose this on the frontend.
    */
    'username' => env('DIGIFLAZZ_USERNAME', ''),

    /*
    |--------------------------------------------------------------------------
    | Digiflazz API Key
    |--------------------------------------------------------------------------
    | Your Buyer API Key from the Digiflazz dashboard:
    | Menu: "Atur Koneksi" > "API".
    | Keep this secret and rotate periodically.
    */
    'api_key'  => env('DIGIFLAZZ_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    | When set, incoming webhooks must include the "X-Hub-Signature" header.
    | The package will verify it using HMAC-SHA1 over the raw request body.
    | Configure at your app level; do not share this publicly.
    */
    'webhook_secret' => env('DIGIFLAZZ_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout (seconds)
    |--------------------------------------------------------------------------
    | Global timeout for outgoing HTTP requests to Digiflazz.
    | Keep low to avoid hanging processes; adjust if your infrastructure needs it.
    */
    'timeout' => (int) env('DIGIFLAZZ_HTTP_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | HTTP Retry Policy
    |--------------------------------------------------------------------------
    | Minimal retry/backoff configuration for network resilience.
    | times     : how many retries after the first attempt
    | sleep_ms  : sleep/backoff in milliseconds between retries
    */
    'retry' => [
        'times' => (int) env('DIGIFLAZZ_HTTP_RETRY_TIMES', 2),
        'sleep_ms' => (int) env('DIGIFLAZZ_HTTP_RETRY_SLEEP_MS', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist for Webhooks
    |--------------------------------------------------------------------------
    | Comma-separated list of IPs allowed to call your webhook endpoint.
    | By default includes Digiflazz’s public Webhook IP.
    | Example (in .env):
    |   DIGIFLAZZ_IP_WHITELIST=52.74.250.133,203.0.113.10
    |
    | Notes:
    | - Leave this empty to disable IP checks (NOT recommended).
    | - The package middleware "digiflazz.webhook" will enforce this list.
    */
    'ip_whitelist' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('DIGIFLAZZ_IP_WHITELIST', '52.74.250.133'))
    ))),

];
