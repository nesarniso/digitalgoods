<?php

namespace App\Domain\Shipping\Contracts;

/**
 * Fulfillment adapter contract per spec section 21 (Steadfast, Pathao,
 * RedX, eCourier, Custom Courier). Deliberately not wired to real courier
 * APIs yet — there's no `shipments` table to persist tracking IDs against
 * until Phase 11 (Orders) exists, and integrating live courier
 * credentials without something real to test against would just be
 * unverifiable code. This interface is the seam: Phase 11 will add a
 * `shipments` table and concrete adapters (SteadfastCourier,
 * PathaoCourier, etc.) implementing this contract, selected via config
 * exactly like the payment gateway pattern from Phase 10.
 *
 * @see \App\Domain\Shipping\Couriers\ManualCourier for the only
 *      implementation that exists right now — appropriate for
 *      single-warehouse/self-delivery setups where no third-party
 *      courier is involved at all.
 */
interface CourierInterface
{
    /**
     * @param  array{recipient_name: string, recipient_phone: string, address: string, cod_amount?: string}  $shipmentData
     * @return array{tracking_id: string, status: string} tracking_id is
     *         provider-specific; ManualCourier generates a local reference
     *         since there's no external system to assign one.
     */
    public function createShipment(array $shipmentData): array;

    public function trackShipment(string $trackingId): array;

    public function cancelShipment(string $trackingId): bool;
}
