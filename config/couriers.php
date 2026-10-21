<?php

use App\Domain\Shipping\Couriers\ManualCourier;
use App\Domain\Shipping\Couriers\PathaoCourier;
use App\Domain\Shipping\Couriers\RedXCourier;
use App\Domain\Shipping\Couriers\SteadfastCourier;

return [
    'default' => env('COURIER_DEFAULT', 'manual'),

    'drivers' => [
        'manual' => [
            'class' => ManualCourier::class,
            'enabled' => true,
            'webhook_secret' => env('COURIER_MANUAL_WEBHOOK_SECRET', ''),
        ],
        'pathao' => [
            'class' => PathaoCourier::class,
            'enabled' => false,
            'webhook_secret' => env('COURIER_PATHAO_WEBHOOK_SECRET', ''),
        ],
        'steadfast' => [
            'class' => SteadfastCourier::class,
            'enabled' => false,
            'webhook_secret' => env('COURIER_STEADFAST_WEBHOOK_SECRET', ''),
        ],
        'redx' => [
            'class' => RedXCourier::class,
            'enabled' => false,
            'webhook_secret' => env('COURIER_REDX_WEBHOOK_SECRET', ''),
        ],
    ],
];
