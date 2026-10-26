<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'CBT School'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'key' => env('APP_KEY', ''),
    'csrf_secret' => env('CSRF_SECRET', ''),
    'bcrypt_rounds' => (int) env('BCRYPT_ROUNDS', 12),
    'providers' => [
        // Service providers would go here
    ],
];