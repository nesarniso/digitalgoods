<?php

namespace App\Domain\Shipping\Couriers;

class RedXCourier extends ManualCourier
{
    public function createShipment(array $shipmentData): array
    {
        return [
            'tracking_id' => 'REDX-'.strtoupper(bin2hex(random_bytes(8))),
            'tracking_url' => 'https://redx.com/track/'.strtoupper(bin2hex(random_bytes(8))),
            'status' => 'pending_dispatch',
        ];
    }

    public function trackShipment(string $trackingId): array
    {
        return [
            'tracking_id' => $trackingId,
            'status' => 'out_for_delivery',
            'tracking_url' => 'https://redx.com/track/'.$trackingId,
        ];
    }
}
