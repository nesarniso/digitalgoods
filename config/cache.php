<?php

return [

    // File cache works on shared hosting without requiring a Redis service.
    'default' => env('CACHE_STORE', 'file'),

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],

    ],

    // Prefix so cache keys don't collide across client deployments sharing
    // infrastructure (each client env should still set its own APP_NAME).
    'prefix' => env('CACHE_PREFIX', \Illuminate\Support\Str::slug(env('APP_NAME', 'ecommerce'), '_').'_cache_'),

];
