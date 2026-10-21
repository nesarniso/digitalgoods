<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Inventory\Models\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sole entry point for stock mutation. Every method that changes
 * physical_stock or reserved_stock runs inside a DB transaction with
 * `lockForUpdate()` on the inventory row, so two concurrent requests
 * touching the same (product/variant, warehouse) row serialize instead of
 * racing — this is what makes "two customers buying the last unit
 * simultaneously" safe (spec section 86's explicit test scenario).
 *
 * available_stock is never written directly; it's always physical_stock -
 * reserved_stock, computed by Inventory::getAvailableStockAttribute().
 */
class InventoryService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function firstOrCreateInventory(Product $product, ?ProductVariant $variant, Warehouse $warehouse): Inventory
    {
        if ($variant && $variant->product_id !== $product->id) {
            throw ValidationException::withMessages([
                'product_variant_id' => ['The selected variant does not belong to the selected product.'],
            ]);
        }

        return DB::transaction(function () use ($product, $variant, $warehouse) {
            $existing = Inventory::query()
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->where('product_variant_id', $variant?->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            return Inventory::create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'warehouse_id' => $warehouse->id,
                'physical_stock' => 0,
                'reserved_stock' => 0,
                'damaged_stock' => 0,
            ]);
        });
    }

    /**
     * Manual stock adjustment (spec section 10) — e.g. a stock count
     * correction. Positive or negative delta. Cannot push physical_stock
     * negative.
     */
    public function adjust(Inventory $inventory, int $delta, string $reason, User $actor): Inventory
    {
        return $this->mutate($inventory, function (Inventory $locked) use ($delta) {
            $newPhysical = $locked->physical_stock + $delta;

            if ($newPhysical < 0) {
                throw ValidationException::withMessages([
                    'quantity' => ['Adjustment would result in negative physical stock.'],
                ]);
            }

            $locked->physical_stock = $newPhysical;

            return $locked;
        }, type: 'adjustment', quantity: $delta, reason: $reason, actor: $actor);
    }

    /**
     * Reserves stock at checkout start (spec section 78: "Lock Stock" step
     * of order creation). Fails if available_stock (physical - reserved)
     * is insufficient — this is the check that makes the "last unit, two
     * buyers" race safe, because it runs inside the same locked
     * transaction as the reserved_stock increment. Actor is nullable
     * because a guest checkout (Phase 11) has no authenticated User to
     * attribute the movement to — the stock_movements.actor_id column has
     * always been nullable for exactly this reason.
     */
    public function reserve(Inventory $inventory, int $quantity, ?User $actor, ?string $referenceType = null, ?string $referenceId = null): Inventory
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => ['Reservation quantity must be positive.']]);
        }

        return $this->mutate($inventory, function (Inventory $locked) use ($quantity) {
            $available = $locked->physical_stock - $locked->reserved_stock;

            if ($available < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ["Insufficient stock: {$available} available, {$quantity} requested."],
                ]);
            }

            $locked->reserved_stock += $quantity;

            return $locked;
        }, type: 'reservation', quantity: $quantity, reason: null, actor: $actor,
            referenceType: $referenceType, referenceId: $referenceId);
    }

    /**
     * Releases a reservation without a sale — order cancelled/expired
     * before payment confirmed (spec section: "order cancelled/expired →
     * reserved stock must be released").
     */
    public function release(Inventory $inventory, int $quantity, ?User $actor, ?string $referenceType = null, ?string $referenceId = null): Inventory
    {
        return $this->mutate($inventory, function (Inventory $locked) use ($quantity) {
            $locked->reserved_stock = max(0, $locked->reserved_stock - $quantity);

            return $locked;
        }, type: 'release', quantity: -$quantity, reason: null, actor: $actor,
            referenceType: $referenceType, referenceId: $referenceId);
    }

    /**
     * Finalizes a sale: payment confirmed, reservation converts to an
     * actual stock decrement (spec section 78: "Convert Reserved Stock").
     * Decrements both physical_stock and reserved_stock together so
     * available_stock is unaffected by this step — the unit was already
     * unavailable while reserved.
     */
    public function confirmSale(Inventory $inventory, int $quantity, ?User $actor, ?string $referenceType = null, ?string $referenceId = null): Inventory
    {
        return $this->mutate($inventory, function (Inventory $locked) use ($quantity) {
            if ($locked->reserved_stock < $quantity || $locked->physical_stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Cannot confirm sale: reserved/physical stock is insufficient.'],
                ]);
            }

            $locked->physical_stock -= $quantity;
            $locked->reserved_stock -= $quantity;

            return $locked;
        }, type: 'sale', quantity: -$quantity, reason: null, actor: $actor,
            referenceType: $referenceType, referenceId: $referenceId);
    }

    public function markDamaged(Inventory $inventory, int $quantity, string $reason, User $actor): Inventory
    {
        return $this->mutate($inventory, function (Inventory $locked) use ($quantity) {
            if ($locked->physical_stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Cannot mark more units damaged than physically in stock.'],
                ]);
            }

            $locked->physical_stock -= $quantity;
            $locked->damaged_stock += $quantity;

            return $locked;
        }, type: 'damaged', quantity: -$quantity, reason: $reason, actor: $actor);
    }

    /**
     * A returned unit goes back into physical stock (spec section 10:
     * "returned inventory"). Callers decide separately whether a returned
     * unit is sellable (this method) or damaged (use markDamaged instead).
     */
    public function markReturned(Inventory $inventory, int $quantity, string $reason, User $actor, ?string $referenceType = null, ?string $referenceId = null): Inventory
    {
        return $this->mutate($inventory, function (Inventory $locked) use ($quantity) {
            $locked->physical_stock += $quantity;

            return $locked;
        }, type: 'returned', quantity: $quantity, reason: $reason, actor: $actor,
            referenceType: $referenceType, referenceId: $referenceId);
    }

    /**
     * Moves physical stock between warehouses (spec section 10: "stock
     * transfer"). Both legs happen in one transaction — either both
     * warehouses' rows update, or neither does.
     */
    public function transfer(Inventory $from, Inventory $to, int $quantity, User $actor): void
    {
        if ($from->id === $to->id) {
            throw ValidationException::withMessages(['warehouse' => ['Source and destination inventory cannot be the same.']]);
        }

        DB::transaction(function () use ($from, $to, $quantity, $actor) {
            $lockedFrom = Inventory::query()->whereKey($from->id)->lockForUpdate()->firstOrFail();

            if (($lockedFrom->physical_stock - $lockedFrom->reserved_stock) < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Insufficient available stock at source warehouse to transfer.'],
                ]);
            }

            $lockedFrom->decrement('physical_stock', $quantity);
            $this->recordMovement($lockedFrom->fresh(), 'transfer_out', -$quantity, null, $actor);

            $lockedTo = Inventory::query()->whereKey($to->id)->lockForUpdate()->firstOrFail();
            $lockedTo->increment('physical_stock', $quantity);
            $this->recordMovement($lockedTo->fresh(), 'transfer_in', $quantity, null, $actor);
        });
    }

    /**
     * Every mutating method funnels through here: lock the row, let the
     * caller compute the new physical_stock/reserved_stock, save, write
     * the movement ledger row, all inside one transaction.
     */
    private function mutate(
        Inventory $inventory,
        \Closure $apply,
        string $type,
        int $quantity,
        ?string $reason,
        ?User $actor,
        ?string $referenceType = null,
        ?string $referenceId = null,
    ): Inventory {
        return DB::transaction(function () use ($inventory, $apply, $type, $quantity, $reason, $actor, $referenceType, $referenceId) {
            $locked = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            $locked = $apply($locked);
            $locked->save();

            $this->recordMovement($locked, $type, $quantity, $reason, $actor, $referenceType, $referenceId);

            return $locked;
        });
    }

    private function recordMovement(
        Inventory $inventory,
        string $type,
        int $quantity,
        ?string $reason,
        ?User $actor,
        ?string $referenceType = null,
        ?string $referenceId = null,
    ): StockMovement {
        return StockMovement::create([
            'inventory_id' => $inventory->id,
            'type' => $type,
            'quantity' => $quantity,
            'physical_stock_after' => $inventory->physical_stock,
            'reserved_stock_after' => $inventory->reserved_stock,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'actor_id' => $actor?->id,
            'created_at' => now(),
        ]);
    }
}
