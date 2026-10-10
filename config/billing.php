<?php

return [
    'provider' => env('BILLING_PROVIDER', 'paddle'),
    'default_currency' => env('BILLING_CURRENCY', 'USD'),
    'paddle' => [
        'base_url' => env('PADDLE_API_URL', 'https://sandbox-api.paddle.com'),
        'api_key' => env('PADDLE_API_KEY'),
        'client_token' => env('PADDLE_CLIENT_TOKEN'),
        'webhook_secret' => env('PADDLE_WEBHOOK_SECRET'),
        'environment' => env('PADDLE_ENV', 'sandbox'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Free storage quota
    |--------------------------------------------------------------------------
    |
    | Publishing storage (MB) granted to every company owner at
    | registration through the free plan. Paid plans override this with
    | their own `limits.storage_mb` (-1 means unlimited).
    |
    */
    'free_storage_mb' => (int) env('FREE_STORAGE_MB', 30),
];
