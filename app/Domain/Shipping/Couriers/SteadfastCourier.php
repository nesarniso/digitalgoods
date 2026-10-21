<?php

namespace App\Domain\Shipping\Couriers;

class SteadfastCourier extends ManualCourier
{
    public function createShipment(array $shipmentData): array
    {
        return [
            'tracking_id' => 'SF-'.strtoupper(bin2hex(random_bytes(8))),
            'tracking_url' => 'https://steadfast.com/track/'.strtoupper(bin2hex(random_bytes(8))),
            'status' => 'pending_dispatch',
        ];
    }

    public function trackShipment(string $trackingId): array
    {
        return [
            'tracking_id' => $trackingId,
            'status' => 'picked_up',
            'tracking_url' => 'https://steadfast.com/track/'.$trackingId,
        ];
    }
}
