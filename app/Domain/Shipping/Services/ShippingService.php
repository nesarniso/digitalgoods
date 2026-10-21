<?php

namespace App\Domain\Shipping\Services;

use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Shipping\Models\ShippingRate;
use App\Domain\Shipping\Models\ShippingZone;
use App\Domain\Users\Models\Address;
use Illuminate\Support\Collection;

/**
 * Sole entry point for shipping-cost calculation. Given a destination
 * address, cart subtotal, and total weight, resolves the applicable rate
 * per active shipping method. Phase 9 (Checkout) calls this for the
 * authoritative shipping cost — never a client-submitted one (Rule 6).
 */
class ShippingService
{
    /**
     * @return Collection<int, array{method: ShippingMethod, rate: string, free: bool}>
     */
    public function availableRates(Address $address, string $cartSubtotal, string $totalWeightKg): Collection
    {
        return ShippingMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (ShippingMethod $method) use ($address, $cartSubtotal, $totalWeightKg) {
                $rate = $this->calculate($method, $address, $cartSubtotal, $totalWeightKg);

                return $rate === null ? null : [
                    'method' => $method,
                    'rate' => $rate,
                    'free' => bccomp($rate, '0.00', 2) === 0,
                ];
            })
            ->filter() // methods with no matching rate at all are excluded, not shown as free
            ->values();
    }

    /**
     * Returns null if no rate row exists for this method at this
     * destination (neither a zone-specific nor a catch-all rate) — that
     * means the method genuinely doesn't serve this address, which is
     * different from serving it for free.
     */
    public function calculate(ShippingMethod $method, Address $address, string $cartSubtotal, string $totalWeightKg): ?string
    {
        $rate = $this->resolveRate($method, $address);

        if ($rate === null) {
            return null;
        }

        return match ($method->type) {
            'free' => '0.00',
            'flat' => (string) $rate->base_rate,
            'weight_based' => $this->weightBasedRate($rate, $totalWeightKg),
            'price_based' => $this->priceBasedRate($rate, $cartSubtotal),
            default => (string) $rate->base_rate,
        };
    }

    /**
     * Picks the most specific matching zone's rate for this method,
     * falling back to the method's catch-all rate (shipping_zone_id IS
     * NULL) if no zone matches. Ties in specificity are broken by lowest
     * zone ID (oldest/first-created wins) — arbitrary but deterministic.
     */
    private function resolveRate(ShippingMethod $method, Address $address): ?ShippingRate
    {
        $zones = ShippingZone::query()->where('is_active', true)->get();

        $matching = $zones->filter(fn (ShippingZone $zone) => $zone->matchesAddress(
            $address->country, $address->division, $address->district, $address->area,
        ));

        $bestZone = $matching->sortByDesc(fn (ShippingZone $z) => $z->specificity())->first();

        if ($bestZone) {
            $rate = ShippingRate::query()
                ->where('shipping_method_id', $method->id)
                ->where('shipping_zone_id', $bestZone->id)
                ->first();

            if ($rate) {
                return $rate;
            }
        }

        // Catch-all rate for this method (no zone attached).
        return ShippingRate::query()
            ->where('shipping_method_id', $method->id)
            ->whereNull('shipping_zone_id')
            ->first();
    }

    private function weightBasedRate(ShippingRate $rate, string $totalWeightKg): string
    {
        $perKg = (string) ($rate->per_kg_rate ?? '0.00');

        return bcadd((string) $rate->base_rate, bcmul($perKg, $totalWeightKg, 2), 2);
    }

    private function priceBasedRate(ShippingRate $rate, string $cartSubtotal): string
    {
        if ($rate->free_above_amount !== null && bccomp($cartSubtotal, (string) $rate->free_above_amount, 2) >= 0) {
            return '0.00';
        }

        return (string) $rate->base_rate;
    }
}
