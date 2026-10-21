<?php

namespace App\Domain\Shipping\Services;

use App\Domain\Orders\Models\Order;
use App\Domain\Shipping\Contracts\CourierGatewayInterface;
use App\Domain\Shipping\Contracts\CourierInterface;
use App\Domain\Shipping\Couriers\ManualCourier;
use App\Domain\Shipping\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentService
{
    public function __construct(
        private readonly CourierGatewayInterface $courier,
    ) {}

    public function createForOrder(Order $order, ?string $driver = null, array $attributes = []): Shipment
    {
        $existing = $order->shipments()->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($order, $driver, $attributes) {
            $gateway = $this->resolveCourier($driver);
            $shippingAddress = $order->shippingAddress()->first();
            $shipmentData = [
                'recipient_name' => $shippingAddress?->name ?? $order->customer_email ?? 'Customer',
                'recipient_phone' => $shippingAddress?->phone ?? $order->customer_phone ?? 'N/A',
                'address' => $shippingAddress ? trim($shippingAddress->address_line.' '.$shippingAddress->district.' '.$shippingAddress->division.' '.$shippingAddress->country) : 'Customer address pending',
                'cod_amount' => (string) $order->grand_total,
            ];

            $result = $gateway->createShipment($shipmentData);

            return Shipment::create([
                'uuid' => (string) Str::uuid(),
                'order_id' => $order->id,
                'shipment_number' => $this->generateShipmentNumber($order),
                'courier' => $driver ?? config('couriers.default', 'manual'),
                'tracking_number' => $result['tracking_id'] ?? $result['tracking_number'] ?? null,
                'tracking_url' => $result['tracking_url'] ?? null,
                'status' => $result['status'] ?? 'pending_dispatch',
                'delivery_fee' => $attributes['delivery_fee'] ?? '0.00',
                'notes' => $attributes['notes'] ?? null,
                'metadata' => $attributes['metadata'] ?? [],
            ]);
        });
    }

    public function markPickedUp(Shipment $shipment, ?array $payload = null): Shipment
    {
        $shipment->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
            'metadata' => array_merge($shipment->metadata ?? [], $payload ?? []),
        ]);

        return $shipment->fresh();
    }

    public function markDispatched(Shipment $shipment, ?array $payload = null): Shipment
    {
        $shipment->update([
            'status' => 'in_transit',
            'dispatched_at' => now(),
            'metadata' => array_merge($shipment->metadata ?? [], $payload ?? []),
        ]);

        return $shipment->fresh();
    }

    public function markDelivered(Shipment $shipment, ?array $payload = null): Shipment
    {
        $shipment->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'metadata' => array_merge($shipment->metadata ?? [], $payload ?? []),
        ]);

        return $shipment->fresh();
    }

    public function markFailed(Shipment $shipment, ?array $payload = null): Shipment
    {
        $shipment->update([
            'status' => 'failed',
            'failed_at' => now(),
            'metadata' => array_merge($shipment->metadata ?? [], $payload ?? []),
        ]);

        return $shipment->fresh();
    }

    public function updateStatus(Shipment $shipment, string $status, ?array $payload = null): Shipment
    {
        $allowed = ['pending_dispatch', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'failed', 'cancelled'];

        if (! in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException("Unsupported shipment status: {$status}");
        }

        $updates = [
            'status' => $status,
            'metadata' => array_merge($shipment->metadata ?? [], $payload ?? []),
        ];

        if ($status === 'picked_up') {
            $updates['picked_up_at'] = now();
        }

        if ($status === 'in_transit') {
            $updates['dispatched_at'] = now();
        }

        if ($status === 'delivered') {
            $updates['delivered_at'] = now();
        }

        if ($status === 'failed') {
            $updates['failed_at'] = now();
        }

        if ($status === 'cancelled') {
            $updates['cancelled_at'] = now();
        }

        $shipment->update($updates);

        return $shipment->fresh();
    }

    public function cancel(Shipment $shipment): Shipment
    {
        return $this->updateStatus($shipment, 'cancelled');
    }

    public function resolveCourier(?string $driver = null): CourierInterface
    {
        $driver = $driver ?? config('couriers.default', 'manual');
        $config = config('couriers.drivers.'.$driver, ['class' => ManualCourier::class]);
        $class = $config['class'] ?? ManualCourier::class;

        if (! is_string($class) || ! class_exists($class)) {
            return app(ManualCourier::class);
        }

        return app($class);
    }

    protected function generateShipmentNumber(Order $order): string
    {
        return 'SHIP-'.strtoupper(Str::random(8)).'-'.$order->id;
    }
}
