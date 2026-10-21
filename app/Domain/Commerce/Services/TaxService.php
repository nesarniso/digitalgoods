<?php

namespace App\Domain\Commerce\Services;

use App\Domain\Commerce\Models\CartItem;
use Illuminate\Support\Collection;

/**
 * Deliberately modest for Phase 7: tax is computed per line item using the
 * `tax_rate` percentage already on `products` (added Phase 4) — no
 * jurisdiction/zone resolution. The spec's full tax engine (section 68:
 * country/state/division/category/vendor-scoped rates, stacking rules) is
 * a genuinely separate feature — building a partial version of it now
 * would be worse than clearly deferring it. This class is the seam where
 * that engine would plug in later (a TaxZoneResolver feeding rate lookups
 * here) without changing anything that calls TaxService.
 */
class TaxService
{
    /**
     * @param  Collection<int, CartItem>  $items
     */
    public function calculate(Collection $items): string
    {
        return $items->reduce(function (string $carry, CartItem $item) {
            $lineTax = bcmul($item->line_total, bcdiv((string) $item->product->tax_rate, '100', 6), 2);

            return bcadd($carry, $lineTax, 2);
        }, '0.00');
    }
}
