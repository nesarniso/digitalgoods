<?php

return [

    // Payment gateway adapters (implemented in Phase 10). Kept here now so
    // env-driven credentials are wired from day one and no controller ever
    // hardcodes a gateway's config.
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'sslcommerz' => [
        'store_id' => env('SSLCOMMERZ_STORE_ID'),
        'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
        'sandbox' => env('SSLCOMMERZ_SANDBOX', true),
    ],

    'bkash' => [
        'app_key' => env('BKASH_APP_KEY'),
        'app_secret' => env('BKASH_APP_SECRET'),
        'username' => env('BKASH_USERNAME'),
        'password' => env('BKASH_PASSWORD'),
        'sandbox' => env('BKASH_SANDBOX', true),
    ],

    'nagad' => [
        'merchant_id' => env('NAGAD_MERCHANT_ID'),
        'merchant_private_key' => env('NAGAD_MERCHANT_PRIVATE_KEY'),
        'pg_public_key' => env('NAGAD_PG_PUBLIC_KEY'),
        'sandbox' => env('NAGAD_SANDBOX', true),
    ],

    // Courier adapters (Phase 8).
    'steadfast' => [
        'api_key' => env('STEADFAST_API_KEY'),
        'secret_key' => env('STEADFAST_SECRET_KEY'),
    ],

    'pathao' => [
        'client_id' => env('PATHAO_CLIENT_ID'),
        'client_secret' => env('PATHAO_CLIENT_SECRET'),
        'username' => env('PATHAO_USERNAME'),
        'password' => env('PATHAO_PASSWORD'),
    ],

    // SMS adapter (Phase 17).
    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'api_url' => env('SMS_API_URL'),
        'local_provider' => [
            'api_key' => env('LOCAL_SMS_API_KEY'),
            'sender_id' => env('LOCAL_SMS_SENDER_ID'),
        ],
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_NOTIFICATIONS_ENABLED', false),
        'api_url' => env('WHATSAPP_API_URL'),
        'token' => env('WHATSAPP_API_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ],

    // Push notifications (Phase 17).
    'fcm' => [
        'credentials' => env('FCM_CREDENTIALS_PATH'),
        'project_id' => env('FCM_PROJECT_ID'),
        'enabled' => env('FCM_ENABLED', false),
    ],

];
