<?php

namespace App\Domain\Shipping\Couriers;

use App\Domain\Shipping\Contracts\CourierGatewayInterface;
use Illuminate\Support\Str;

/**
 * No external call — appropriate when a client has no third-party courier
 * account yet, or fulfills orders themselves. Generates a local tracking
 * reference so the rest of the system (order status, notifications) has
 * something to display, without pretending an external shipment exists.
 */
class ManualCourier implements CourierGatewayInterface
{
    public function createShipment(array $shipmentData): array
    {
        return [
            'tracking_id' => 'MANUAL-'.strtoupper(Str::random(10)),
            'status' => 'pending_dispatch',
        ];
    }

    public function trackShipment(string $trackingId): array
    {
        return [
            'tracking_id' => $trackingId,
            'status' => 'unknown', // manual courier has no external status to query
        ];
    }

    public function cancelShipment(string $trackingId): bool
    {
        return true;
    }
}
