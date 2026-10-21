<?php

namespace App\Domain\Entrepreneurs\Services;

use App\Domain\Catalog\Models\Product;
use App\Domain\Entrepreneurs\Models\EntrepreneurProduct;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderAddress;
use App\Domain\Orders\Models\OrderStatusHistory;
use App\Domain\Users\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Domain\Finance\Services\ProfitService;
use App\Domain\Settings\Services\SettingService;
use Illuminate\Validation\ValidationException;

class ManualOrderService
{
    public function __construct(
        private readonly ProfitService $profits,
        private readonly SettingService $settings,
    ) {}

    public function create(User $entrepreneur, array $data): Order
    {
        return DB::transaction(function () use ($entrepreneur, $data) {
            $entrepreneur->loadMissing('entrepreneurProfile');
            $product = Product::query()->where('status', 'published')->findOrFail($data['product_id']);
            if ($product->purchase_price === null) {
                throw ValidationException::withMessages([
                    'product_id' => ['This product is not ready for entrepreneur selling until its purchase cost is configured.'],
                ]);
            }
            if (! EntrepreneurProduct::query()
                ->where('entrepreneur_id', $entrepreneur->id)
                ->where('product_id', $product->id)
                ->where('status', 'active')
                ->exists()) {
                throw ValidationException::withMessages([
                    'product_id' => ['Select a product from your active marketing list.'],
                ]);
            }
            $quantity = (int) $data['quantity'];
            $sellingPrice = (string) $data['selling_price'];
            $customer = User::query()->where('phone', $data['phone'])->first();

            if (! $customer) {
                $customer = User::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $data['customer_name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make(Str::random(32)),
                ]);
                $customer->assignRole('customer');
                CustomerProfile::create(['user_id' => $customer->id]);
            } else {
                $customer->update(['name' => $data['customer_name']]);
            }

            $subtotal = bcmul($sellingPrice, (string) $quantity, 2);
            $order = Order::create([
                'uuid' => (string) Str::uuid(),
                'order_number' => 'PENDING',
                'idempotency_key' => 'manual-'.$entrepreneur->id.'-'.Str::uuid(),
                'user_id' => $customer->id,
                'entrepreneur_id' => $entrepreneur->id,
                'customer_phone' => $data['phone'],
                'status' => 'pending',
                'source' => 'manual',
                'attribution_code' => $entrepreneur->entrepreneurProfile->entrepreneur_code,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'shipping_amount' => 0,
                'grand_total' => $subtotal,
                'payment_method' => 'cod',
                'placed_at' => now(),
            ]);
            $order->update(['order_number' => 'ORD-'.now()->format('Ymd').'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)]);

            $returnCost = bcmul((string) $this->settings->get('entrepreneur.return_cost', 0), (string) $quantity, 2);
            $profitPercent = $this->profits->percentageForProduct($product);
            $deliveryCost = '0.00';
            $supplierCost = bcmul((string) $product->purchase_price, (string) $quantity, 2);
            $flatRate = bcmul($this->profits->flatRateForProduct($product), (string) $quantity, 2);
            $entrepreneurProfit = $this->profits->entrepreneurProfit($supplierCost, $subtotal, $returnCost, $profitPercent, $flatRate);

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'unit_price' => $sellingPrice,
                'quantity' => $quantity,
                'line_total' => $subtotal,
                'cost_price' => $product->purchase_price,
                'supplier_cost' => $supplierCost,
                'delivery_cost' => $deliveryCost,
                'return_cost' => $returnCost,
                'entrepreneur_profit' => $entrepreneurProfit,
                'entrepreneur_profit_percent' => $profitPercent,
                'entrepreneur_profit_flat_rate' => $flatRate,
            ]);

            OrderAddress::create([
                'order_id' => $order->id,
                'type' => 'shipping',
                'name' => $data['customer_name'],
                'phone' => $data['phone'],
                'country' => 'Bangladesh',
                'address_line' => $data['address'],
            ]);
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'message' => 'Manual order created by entrepreneur.',
                'actor_id' => $entrepreneur->id,
            ]);

            return $order->fresh(['items', 'shippingAddress']);
        });
    }
}
