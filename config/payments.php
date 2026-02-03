<?php

return [
    'currency' => env('APP_CURRENCY', 'EGP'),

    'kashier' => [
        'base_url'          => env('KASHIER_BASE_URL', 'https://payments.kashier.io'),
        'merchant_id'       => env('KASHIER_MERCHANT_ID'),
        'api_key'           => env('KASHIER_API_KEY'),
        'secret'            => env('KASHIER_SECRET'),
        'mode'              => env('KASHIER_MODE', 'test'),
        'merchant_redirect' => env('KASHIER_MERCHANT_REDIRECT'),
    ],
];
