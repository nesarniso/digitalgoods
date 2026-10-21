<?php

namespace App\Domain\Shipping\Services;

use App\Domain\Shipping\Models\Shipment;
use Illuminate\Support\Facades\DB;

class CourierWebhookService
{
    public function verifySignature(string $courier, string $payload, array $headers): bool
    {
        $secret = config("couriers.drivers.{$courier}.webhook_secret");

        if (! is_string($secret) || $secret === '') {
            return false;
        }

        $signature = $this->extractSignature($courier, $headers);
        if ($signature === null) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    public function process(string $courier, string $payload, array $headers): bool
    {
        if (! $this->verifySignature($courier, $payload, $headers)) {
            return false;
        }

        $json = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        $eventId = (string) ($json['event_id'] ?? $json['id'] ?? $json['tracking_id'] ?? hash('sha256', $payload));
        $inserted = DB::table('courier_webhook_events')->insertOrIgnore([
            'courier' => $courier,
            'event_id' => $eventId,
            'payload' => json_encode($json, JSON_THROW_ON_ERROR),
            'verified' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($inserted === 0) {
            return true;
        }

        $this->syncShipment($json);

        return true;
    }

    /**
     * Courier payloads differ, but all supported adapters can identify a
     * shipment with a tracking number and report a normalized status.
     */
    private function syncShipment(array $payload): void
    {
        $trackingNumber = $this->firstString($payload, [
            'tracking_number', 'tracking_id', 'tracking_code', 'consignment_id',
        ]);
        $status = $this->normalizeStatus($this->firstString($payload, [
            'status', 'shipment_status', 'delivery_status',
        ]));

        if ($trackingNumber === null || $status === null) {
            return;
        }

        $shipment = Shipment::query()->where('tracking_number', $trackingNumber)->first();
        if (! $shipment || $shipment->isTerminal()) {
            return;
        }

        app(ShipmentService::class)->updateStatus($shipment, $status, [
            'last_webhook_status' => $payload['status'] ?? $payload['shipment_status'] ?? null,
            'last_webhook_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * @param array<int, string> $keys
     */
    private function firstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($payload, $key);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function normalizeStatus(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        return match (strtolower(trim($status))) {
            'pending', 'pending_dispatch', 'created', 'booked' => 'pending_dispatch',
            'picked_up', 'picked-up', 'pickup', 'picked' => 'picked_up',
            'in_transit', 'in-transit', 'dispatched', 'transit' => 'in_transit',
            'out_for_delivery', 'out-for-delivery', 'out for delivery' => 'out_for_delivery',
            'delivered', 'completed' => 'delivered',
            'failed', 'delivery_failed', 'delivery-failed' => 'failed',
            'cancelled', 'canceled' => 'cancelled',
            default => null,
        };
    }

    protected function extractSignature(string $courier, array $headers): ?string
    {
        $keys = [
            'x-courier-signature',
            'x-signature',
            'x-pathao-signature',
            'x-steadfast-signature',
            'x-redx-signature',
            'signature',
        ];

        foreach ($keys as $key) {
            $value = $headers[$key] ?? $headers[strtoupper(str_replace('-', '_', $key))] ?? null;
            if (is_array($value)) {
                $value = $value[0] ?? null;
            }

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
