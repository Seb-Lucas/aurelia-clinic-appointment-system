<?php

return [
    'name' => env('APP_NAME', 'Clinical Healthcare Appointment System'),
    'env' => strtolower((string) env('APP_ENV', 'production')),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost:8000'),
    'session_lifetime' => (int) env('SESSION_LIFETIME', 120),
    'session_secure' => strtolower((string) env('APP_ENV', 'production')) === 'production'
        || (bool) env('SESSION_SECURE', false),
    'session_same_site' => env('SESSION_SAME_SITE', 'Lax'),
    'db' => [
        'default' => env('DB_CONNECTION', 'sqlite'),
        'seed_demo_data' => (bool) env('DB_SEED_DEMO_DATA', false),
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', base_path('storage/app.sqlite')),
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'clinic_app'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
];
