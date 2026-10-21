<?php

namespace App\Domain\Shipping\Couriers;

class PathaoCourier extends ManualCourier
{
    public function createShipment(array $shipmentData): array
    {
        return [
            'tracking_id' => 'PATHAO-'.strtoupper(bin2hex(random_bytes(6))),
            'tracking_url' => 'https://pathao.com/tracking/'.strtoupper(bin2hex(random_bytes(6))),
            'status' => 'pending_dispatch',
        ];
    }

    public function trackShipment(string $trackingId): array
    {
        return [
            'tracking_id' => $trackingId,
            'status' => 'in_transit',
            'tracking_url' => 'https://pathao.com/tracking/'.$trackingId,
        ];
    }
}
