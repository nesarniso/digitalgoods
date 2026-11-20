<?php

namespace App\Domain\Orders\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Finance\Services\ProfitService;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\Coupon;
use App\Domain\Commerce\Services\CheckoutService;
use App\Domain\Commerce\Services\CouponService;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Inventory\Services\InventoryService;
use App\Domain\Notifications\Services\NotificationService;
use App\Jobs\SendOrderContactNotification;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderStatusHistory;
use App\Domain\Digital\Services\DigitalLicenseService;
use App\Domain\Payments\Services\PaymentService;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Shipping\Services\ShipmentService;
use App\Domain\Settings\Services\SettingService;
use App\Domain\Users\Models\Address;
use App\Domain\Users\Services\AddressService;
use App\Models\User;
use App\Notifications\CommerceNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The orchestration point spec section 78 describes: Validate Cart → Lock
 * Stock → Calculate Price → Create Order → Create Order Items → Reserve
 * Stock → Create Payment → Commit. This class composes CheckoutService
 * (Phase 9), InventoryService (Phase 5), CouponService (Phase 7), and
 * PaymentService (Phase 10) — it owns none of their logic, only the
 * sequencing and the transaction boundary around all of it.
 *
 * One deliberate reordering from the spec's literal sequence: stock is
 * reserved AFTER the Order row is created (not before), so each stock
 * movement can reference the order it was reserved for. The row is
 * created and reserved within the same transaction either way, so this
 * doesn't change the atomicity guarantee — if reservation fails, the
 * whole transaction (including the Order row) rolls back.
 */
class OrderService
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly InventoryService $inventory,
        private readonly CouponService $coupons,
        private readonly PaymentService $payments,
        private readonly ShipmentService $shipments,
        private readonly AuditLogger $auditLogger,
        private readonly NotificationService $pushNotifications,
        private readonly ProfitService $profits,
        private readonly SettingService $settings,
        private readonly DigitalLicenseService $digitalLicenses,
        private readonly AddressService $addresses,
    ) {}

    public function placeOrder(
        Cart $cart,
        ?Address $shippingAddress,
        ?ShippingMethod $shippingMethod,
        string $paymentMethod,
        string $idempotencyKey,
        ?User $user,
        ?string $guestEmail,
        ?string $guestPhone,
        ?string $notes = null,
        ?array $attribution = null,
        ?string $payerMobile = null,
        ?string $transactionId = null,
        ?string $paidAt = null,
        ?string $submittedAmount = null,
    ): Order {
        $existing = Order::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        // Read-only, authoritative recalculation — the same method the
        // checkout preview screen called. If anything has changed since
        // the customer last saw the summary (stock, price, coupon), it's
        // caught here rather than silently placing a wrong order.
        $summary = $this->checkout->summarize($cart, $shippingAddress, $shippingMethod, $paymentMethod);

        if (! $summary['ready_to_place_order']) {
            throw ValidationException::withMessages([
                'checkout' => array_column($summary['issues'], 'message'),
            ]);
        }

        if (in_array($paymentMethod, ['bkash', 'nagad', 'rocket'], true)
            && $submittedAmount !== null
            && bccomp((string) $submittedAmount, (string) $summary['grand_total'], 2) !== 0) {
            throw ValidationException::withMessages([
                'submitted_amount' => ['The submitted payment amount must equal the order total.'],
            ]);
        }

        return DB::transaction(function () use ($cart, $shippingAddress, $shippingMethod, $paymentMethod, $idempotencyKey, $user, $guestEmail, $guestPhone, $notes, $attribution, $payerMobile, $transactionId, $paidAt, $submittedAmount) {
            // Recalculate inside the order transaction so a price, coupon,
            // shipping, or availability change after the preview cannot
            // leak stale totals into the persisted order.
            $summary = $this->checkout->summarize($cart, $shippingAddress, $shippingMethod, $paymentMethod);

            if (! $summary['ready_to_place_order']) {
                throw ValidationException::withMessages([
                    'checkout' => array_column($summary['issues'], 'message'),
                ]);
            }

            if (in_array($paymentMethod, ['bkash', 'nagad', 'rocket'], true)
                && $submittedAmount !== null
                && bccomp((string) $submittedAmount, (string) $summary['grand_total'], 2) !== 0) {
                throw ValidationException::withMessages([
                    'submitted_amount' => ['The submitted payment amount must equal the order total.'],
                ]);
            }

            if (! $user) {
                $guestEmail = strtolower(trim((string) $guestEmail));
            }

            if ($user && $shippingAddress && ! $shippingAddress->exists) {
                $shippingAddress = $this->addresses->create($user, [
                    'type' => 'shipping',
                    'name' => $shippingAddress->name,
                    'phone' => $shippingAddress->phone,
                    'country' => $shippingAddress->country,
                    'division' => $shippingAddress->division,
                    'district' => $shippingAddress->district,
                    'upazila' => $shippingAddress->upazila,
                    'area' => $shippingAddress->area,
                    'postal_code' => $shippingAddress->postal_code,
                    'address_line' => $shippingAddress->address_line,
                ]);
            }

            $order = Order::create([
                'uuid' => (string) Str::uuid(),
                'order_number' => 'PENDING', // replaced immediately below once the id exists
                'idempotency_key' => $idempotencyKey,
                'user_id' => $user?->id,
                'customer_email' => $guestEmail ?? $user?->email,
                'customer_phone' => $guestPhone ?? $shippingAddress?->phone,
                'status' => 'pending',
                'entrepreneur_id' => $attribution['entrepreneur_id'] ?? null,
                'source' => $attribution['source'] ?? 'direct',
                'attribution_code' => $attribution['code'] ?? null,
                'subtotal' => $summary['subtotal'],
                'discount_amount' => $summary['discount_amount'],
                'tax_amount' => $summary['tax_amount'],
                'shipping_amount' => $summary['shipping_amount'],
                'grand_total' => $summary['grand_total'],
                'coupon_code' => $summary['coupon']['valid'] ?? false ? $summary['coupon']['code'] : null,
                'shipping_method_name' => $shippingMethod?->name,
                'payment_method' => $paymentMethod,
                'notes' => $notes,
                'placed_at' => now(),
            ]);

            $order->update(['order_number' => $this->generateOrderNumber($order)]);

            $this->createOrderItems($order, $cart);
            $this->reserveStockForItems($order, $user);
            if ($shippingAddress) {
                $this->createShippingAddressSnapshot($order, $shippingAddress);
            }
            $this->redeemCouponIfApplicable($order, $summary, $user);

            $this->payments->initiate(
                $paymentMethod,
                (string) $order->grand_total,
                $idempotencyKey.'-payment',
                $user,
                'INR',
                $order,
                ['payer_mobile' => $payerMobile, 'manual_transaction_id' => $transactionId, 'paid_at' => $paidAt, 'submitted_amount' => $submittedAmount],
            );

            $this->recordStatusHistory($order, 'pending', 'Order placed.', $user);
            if ($order->user) {
                $order->user->notify(new CommerceNotification(
                    'Order received',
                    "We received order {$order->order_number}.",
                    route('account.orders.show', $order),
                    'order_placed',
                    [
                        'site_name' => app(\App\Support\SiteBrand::class)->name(),
                        'customer_name' => $order->user->name,
                        'order_number' => $order->order_number,
                        'grand_total' => '₹'.number_format((float) $order->grand_total, 2),
                    ],
                ));
                $this->pushNotifications->queueForUser($order->user, 'Order received', "We received order {$order->order_number}.", route('account.orders.show', $order));
            }

            $cart->items()->delete();
            $cart->update([
                'coupon_id' => null,
                'checkout_name' => null,
                'checkout_phone' => null,
                'checkout_email' => null,
                'checkout_contacted_at' => null,
            ]);

            $this->auditLogger->log(
                actor: $user, action: 'placed', module: 'Orders',
                entityType: 'Order', entityId: (string) $order->id,
                newValue: ['order_number' => $order->order_number, 'grand_total' => (string) $order->grand_total],
            );

            return $order->fresh(['items', 'addresses', 'statusHistory']);
        });
    }

    /**
     * Payment confirmed (manual gateway admin action, or eventually a
     * verified webhook) → available digital stock is assigned atomically,
     * order moves
     * from pending to confirmed. This is the "Convert Reserved Stock"
     * step from spec section 78's payment-success flow.
     */
    public function confirmPayment(Order $order, User $actor, ?PaymentTransaction $selectedTransaction = null): Order
    {
        if (in_array($order->status, ['cancelled', 'delivered'], true)) {
            throw ValidationException::withMessages([
                'order' => ['Cancelled or delivered orders cannot have their payment changed.'],
            ]);
        }

        try {
            return DB::transaction(function () use ($order, $actor, $selectedTransaction) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (in_array($order->status, ['cancelled', 'delivered'], true)) {
                throw ValidationException::withMessages([
                    'order' => ['Cancelled or delivered orders cannot have their payment changed.'],
                ]);
            }

            $transaction = $selectedTransaction
                ? PaymentTransaction::query()
                    ->whereKey($selectedTransaction->id)
                    ->where('order_id', $order->id)
                    ->lockForUpdate()
                    ->first()
                : $order->paymentTransactions()->latest('id')->lockForUpdate()->first();

            if (! $transaction) {
                throw ValidationException::withMessages([
                    'payment' => ['The selected payment transaction does not belong to this order or is unavailable.'],
                ]);
            }

            if (bccomp((string) $transaction->amount, (string) $order->grand_total, 2) !== 0) {
                throw ValidationException::withMessages([
                    'payment' => ['The payment amount does not match the order total.'],
                ]);
            }

            if ($transaction->submitted_amount !== null
                && bccomp((string) $transaction->submitted_amount, (string) $order->grand_total, 2) !== 0) {
                throw ValidationException::withMessages([
                    'payment' => ['The submitted payment amount does not match the order total.'],
                ]);
            }

            $this->payments->confirmManualPayment($transaction, $actor);
            $licensesAssigned = $this->digitalLicenses->assign($order, strict: false);
            $this->confirmReservedStock($order, $actor);

            $nextStatus = $order->status === 'pending' ? 'confirmed' : $order->status;
            $order->update([
                'status' => $nextStatus,
                'payment_review_required' => ! $licensesAssigned,
                'payment_review_reason' => $licensesAssigned
                    ? null
                    : 'Payment confirmed, but one or more digital product keys are awaiting stock.',
            ]);
            $this->recordStatusHistory($order, $nextStatus, 'Payment confirmed.', $actor);
            $customer = $order->relationLoaded('user')
                ? $order->getRelation('user')
                : ($order->user_id ? $order->user()->first() : null);

            if ($customer) {
                $customer->notify(new CommerceNotification(
                    'Payment confirmed',
                    "Payment for order {$order->order_number} has been confirmed.",
                    route('account.orders.show', $order),
                    'payment_confirmed',
                    [
                        'site_name' => app(\App\Support\SiteBrand::class)->name(),
                        'customer_name' => $customer->name,
                        'order_number' => $order->order_number,
                    ],
                ));
                $this->pushNotifications->queueForUser($customer, 'Payment confirmed', "Payment for order {$order->order_number} has been confirmed.", route('account.orders.show', $order));
            }

                return $order->fresh();
            });
        } catch (ValidationException $exception) {
            if (isset($exception->errors()['product'])) {
                $order->update([
                    'payment_review_required' => true,
                    'payment_review_reason' => 'Digital stock unavailable during payment confirmation.',
                ]);
            }

            throw $exception;
        }
    }

    public function retryDigitalLicenseAssignment(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! $order->isPaid()) {
                throw ValidationException::withMessages([
                    'payment' => ['Digital keys can only be assigned after payment is confirmed.'],
                ]);
            }
            if (in_array($order->status, ['cancelled', 'delivered'], true)) {
                throw ValidationException::withMessages([
                    'order' => ['Cancelled or delivered orders cannot receive digital product keys.'],
                ]);
            }

            $assigned = $this->digitalLicenses->assign($order, strict: false);
            $order->update([
                'payment_review_required' => ! $assigned,
                'payment_review_reason' => $assigned
                    ? null
                    : 'Payment confirmed, but one or more digital product keys are awaiting stock.',
            ]);
            $this->auditLogger->log(
                actor: $actor,
                action: 'digital_license_assignment_retried',
                module: 'Orders',
                entityType: 'Order',
                entityId: (string) $order->id,
                newValue: ['all_assigned' => $assigned],
            );

            return $order->fresh();
        });
    }

    public function rejectPayment(Order $order, User $actor, string $reason): Order
    {
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages(['order' => ['Only a pending order can be rejected.']]);
        }
        return DB::transaction(function () use ($order, $actor, $reason) {
            $transaction = $order->paymentTransactions()->latest('id')->first();
            if ($transaction) $this->payments->rejectManualPayment($transaction, $actor, $reason);
            $this->digitalLicenses->release($order);
            $this->releaseReservedStock($order, $actor);
            $order->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => 'Payment rejected: '.$reason]);
            $this->recordStatusHistory($order, 'cancelled', 'Payment rejected: '.$reason, $actor);
            return $order->fresh();
        });
    }

    /**
     * Cancellable only pre-fulfillment (pending/confirmed/processing —
     * spec section 28's "Before shipping" rule). Releases exactly the
     * stock this order actually reserved (tracked per-item via
     * stock_reserved, not re-derived), and cancels the payment transaction
     * if it hasn't succeeded yet. A cancellation against an
     * already-*paid* order is deliberately NOT auto-refunded here — that's
     * Phase 12's formal return/refund workflow; an admin can already
     * issue a manual refund via Phase 10's existing endpoint in the
     * meantime.
     */
    public function cancel(Order $order, string $reason, ?User $actor): Order
    {
        if (! $order->isCancellable()) {
            throw ValidationException::withMessages([
                'order' => ["Order cannot be cancelled from its current status ({$order->status})."],
            ]);
        }

        return DB::transaction(function () use ($order, $reason, $actor) {
            $defaultWarehouse = Warehouse::query()->where('is_default', true)->first();

            foreach ($order->items()->get() as $item) {
                if (! $item->stock_reserved || ! $defaultWarehouse) {
                    continue;
                }

                $inventory = Inventory::query()
                    ->where('product_id', $item->product_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->where('warehouse_id', $defaultWarehouse->id)
                    ->first();

                if ($inventory) {
                    $this->inventory->release($inventory, $item->quantity, $actor, 'Order', (string) $order->id);
                }
            }

            $transaction = $order->paymentTransactions()->latest('id')->first();
            $paymentAlreadyCaptured = false;

            if ($transaction) {
                if ($transaction->isSuccessful()) {
                    $paymentAlreadyCaptured = true;
                } else {
                    $this->payments->cancelPending($transaction);
                }
            }
            $this->digitalLicenses->release($order);

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            $message = 'Order cancelled: '.$reason;
            if ($paymentAlreadyCaptured) {
                $message .= ' (payment was already captured — refund requires a separate manual action)';
            }
            $this->recordStatusHistory($order, 'cancelled', $message, $actor);

            $this->auditLogger->log(
                actor: $actor, action: 'cancelled', module: 'Orders',
                entityType: 'Order', entityId: (string) $order->id,
                newValue: ['reason' => $reason, 'payment_already_captured' => $paymentAlreadyCaptured],
            );
            if ($order->user) {
                $order->user->notify(new CommerceNotification(
                    'Order status updated',
                    "Order {$order->order_number} is now cancelled.",
                    route('account.orders.show', $order),
                    'order_status_updated',
                    [
                        'customer_name' => $order->user->name,
                        'order_number' => $order->order_number,
                        'order_status' => 'cancelled',
                        'order_url' => route('account.orders.show', $order),
                    ],
                ));
                $this->pushNotifications->queueForUser($order->user, 'Order status updated', "Order {$order->order_number} is now cancelled.", route('account.orders.show', $order));
            }

            return $order->fresh();
        });
    }

    /**
     * Admin progression through fulfillment states. Payment confirmation is
     * deliberately separate: changing an order status must never capture or
     * fabricate payment.
     */
    public function updateStatus(Order $order, string $newStatus, ?string $message, User $actor): Order
    {
        $statuses = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered'];

        if (! in_array($newStatus, $statuses, true)) {
            throw ValidationException::withMessages([
                'status' => ['The selected order status is invalid.'],
            ]);
        }

        return DB::transaction(function () use ($order, $newStatus, $message, $actor) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->status === 'delivered' && $newStatus !== 'delivered') {
                throw ValidationException::withMessages([
                    'status' => ['A delivered order cannot be moved to another status.'],
                ]);
            }

            $order->update(['status' => $newStatus]);

            if ($newStatus === 'delivered' && $order->entrepreneur_id) {
                $this->profits->createPending($order);
                $this->profits->release($order);
            }

            if (in_array($newStatus, ['packed', 'shipped', 'out_for_delivery', 'delivered'], true) && Schema::hasTable('shipments') && ! $order->shipments()->exists()) {
                $this->shipments->createForOrder($order);
            }

            $this->recordStatusHistory($order, $newStatus, $message, $actor);

            $customer = $order->relationLoaded('user')
                ? $order->getRelation('user')
                : ($order->user_id ? $order->user()->first() : null);

            if ($customer) {
                $customer->notify(new CommerceNotification(
                    'Order status updated',
                    "Order {$order->order_number} is now ".str_replace('_', ' ', $newStatus).'.',
                    route('account.orders.show', $order),
                    $newStatus === 'delivered' ? 'order_delivered' : 'order_status_updated',
                    [
                        'customer_name' => $customer->name,
                        'order_number' => $order->order_number,
                        'order_status' => str_replace('_', ' ', $newStatus),
                        'order_url' => route('account.orders.show', $order),
                    ],
                ));
                $this->pushNotifications->queueForUser($customer, 'Order status updated', "Order {$order->order_number} is now ".str_replace('_', ' ', $newStatus).'.', route('account.orders.show', $order));
            }

            $this->auditLogger->log(
                actor: $actor, action: 'status_updated', module: 'Orders',
                entityType: 'Order', entityId: (string) $order->id,
                newValue: ['status' => $newStatus],
            );

            return $order->fresh();
        });
    }

    private function confirmReservedStock(Order $order, User $actor): void
    {
        $defaultWarehouse = Warehouse::query()->where('is_default', true)->first();

        foreach ($order->items()->get() as $item) {
            if (! $item->stock_reserved || ! $defaultWarehouse) {
                continue;
            }

            $inventory = Inventory::query()
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->where('warehouse_id', $defaultWarehouse->id)
                ->first();

            if ($inventory) {
                $this->inventory->confirmSale($inventory, $item->quantity, $actor, 'Order', (string) $order->id);
                $item->update(['stock_reserved' => false]);
            }
        }
    }

    private function createOrderItems(Order $order, Cart $cart): void
    {
        $items = $cart->items()->with(['product', 'variant.attributeValues.attribute'])->get();

        $totalLineValue = $items->reduce(
            fn (string $carry, $item) => bcadd($carry, (string) $item->line_total, 2),
            '0.00'
        );
        $returnCost = (string) $this->settings->get('entrepreneur.return_cost', 0);
        foreach ($items as $item) {
            $variantLabel = null;
            if ($item->variant) {
                $variantLabel = $item->variant->attributeValues
                    ->map(fn ($v) => "{$v->attribute->name}: {$v->value}")
                    ->implode(', ');
            }

            $order->items()->create([
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product->name,
                'sku' => $item->variant->sku ?? $item->product->sku,
                'variant_label' => $variantLabel ?: null,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'line_total' => $item->line_total,
                'stock_reserved' => false, // set true below, only if reservation actually succeeds
                'supplier_cost' => ($item->variant?->purchase_price ?? $item->product->purchase_price) === null
                    ? null
                    : bcmul((string) ($item->variant?->purchase_price ?? $item->product->purchase_price), (string) $item->quantity, 2),
                'delivery_cost' => $totalLineValue > 0
                    ? bcmul((string) $order->shipping_amount, bcdiv((string) $item->line_total, $totalLineValue, 6), 2)
                    : '0.00',
                'return_cost' => bcmul($returnCost, (string) $item->quantity, 2),
                'entrepreneur_profit_percent' => $order->entrepreneur_id ? $this->profits->percentageForProduct($item->product) : null,
                'entrepreneur_profit_flat_rate' => $order->entrepreneur_id
                    ? bcmul($this->profits->flatRateForProduct($item->product), (string) $item->quantity, 2)
                    : null,
                'entrepreneur_profit' => $order->entrepreneur_id && $item->product->purchase_price !== null
                    ? $this->profits->entrepreneurProfit(
                        bcmul((string) $item->product->purchase_price, (string) $item->quantity, 2),
                        (string) $item->line_total,
                        bcadd(
                            $totalLineValue > 0
                                ? bcmul((string) $order->shipping_amount, bcdiv((string) $item->line_total, $totalLineValue, 6), 2)
                                : '0.00',
                            bcmul($returnCost, (string) $item->quantity, 2),
                            2
                        ),
                        $this->profits->percentageForProduct($item->product),
                        bcmul($this->profits->flatRateForProduct($item->product), (string) $item->quantity, 2)
                    )
                    : null,
            ]);
        }
    }

    private function reserveStockForItems(Order $order, ?User $user): void
    {
        $defaultWarehouse = Warehouse::query()->where('is_default', true)->first();

        if (! $defaultWarehouse) {
            return; // no warehouse configured — nothing to reserve against
        }

        foreach ($order->items()->get() as $item) {
            $inventory = Inventory::query()
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->where('warehouse_id', $defaultWarehouse->id)
                ->first();

            if (! $inventory) {
                continue; // untracked product — nothing to reserve, matches CheckoutService's own check
            }

            $this->inventory->reserve($inventory, $item->quantity, $user, 'Order', (string) $order->id);
            $item->update(['stock_reserved' => true]);
        }
    }

    private function releaseReservedStock(Order $order, ?User $actor): void
    {
        $warehouse = Warehouse::query()->where('is_default', true)->first();
        if (! $warehouse) return;
        foreach ($order->items()->get() as $item) {
            if (! $item->stock_reserved) continue;
            $inventory = Inventory::query()
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->where('warehouse_id', $warehouse->id)
                ->first();
            if ($inventory) $this->inventory->release($inventory, $item->quantity, $actor, 'Order', (string) $order->id);
            $item->update(['stock_reserved' => false]);
        }
    }

    private function createShippingAddressSnapshot(Order $order, Address $address): void
    {
        $order->addresses()->create([
            'type' => 'shipping',
            'name' => $address->name,
            'phone' => $address->phone,
            'country' => $address->country,
            'division' => $address->division,
            'district' => $address->district,
            'upazila' => $address->upazila,
            'area' => $address->area,
            'postal_code' => $address->postal_code,
            'address_line' => $address->address_line,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
        ]);
    }

    private function redeemCouponIfApplicable(Order $order, array $summary, ?User $user): void
    {
        if (empty($summary['coupon']['valid'])) {
            return;
        }

        $coupon = Coupon::query()->where('code', $summary['coupon']['code'])->first();

        if ($coupon) {
            $this->coupons->redeem($coupon, $user, $summary['discount_amount']);
        }
    }

    private function recordStatusHistory(Order $order, string $status, ?string $message, ?User $actor): OrderStatusHistory
    {
        $history = OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $status,
            'message' => $message,
            'actor_id' => $actor?->id,
            'created_at' => now(),
        ]);

        SendOrderContactNotification::dispatch($order->id, $status)->afterCommit();

        return $history;
    }

    private function generateOrderNumber(Order $order): string
    {
        // Global sequential ID, not a per-year-reset counter — a true
        // "resets to 000001 every January" scheme needs its own
        // concurrency-safe counter table (same class of problem
        // WarehouseService's single-default-invariant solves, just for a
        // counter instead of a flag), which is unnecessary complexity
        // when global uniqueness and sequential ordering is all that
        // actually matters functionally.
        return sprintf('ORD-%s-%06d', now()->format('Y'), $order->id);
    }
}
