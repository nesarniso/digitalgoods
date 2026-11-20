<?php

namespace App\Domain\Digital\Services;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Digital\Models\DigitalLicenseKey;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DigitalLicenseService
{
    public function add(Product $product, string $secret, ?string $instructions, ?User $actor, ?ProductVariant $variant = null): DigitalLicenseKey
    {
        if ($product->product_type !== 'digital') {
            throw ValidationException::withMessages(['product_id' => ['Licenses can only be added to digital products.']]);
        }
        if ($variant && $variant->product_id !== $product->id) {
            throw ValidationException::withMessages(['product_variant_id' => ['The selected variant does not belong to this product.']]);
        }

        $hash = hash('sha256', mb_strtolower(trim($secret)));
        if (DigitalLicenseKey::query()->where('product_id', $product->id)->where('secret_hash', $hash)->exists()) {
            throw ValidationException::withMessages(['secret' => ['This license already exists for the product.']]);
        }

        return DigitalLicenseKey::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'reference' => 'LIC-'.strtoupper(Str::random(10)),
            'secret' => $secret,
            'instructions' => $instructions,
            'created_by' => $actor?->id,
        ]);
    }

    public function assign(Order $order, bool $strict = true): bool
    {
        $allAssigned = true;
        $licenses = DigitalLicenseKey::query()
            ->where('order_id', $order->id)
            ->where('status', 'assigned')
            ->get();

        foreach ($order->items()->with('product')->get() as $item) {
            if ($item->product->product_type !== 'digital') {
                continue;
            }
            if ($item->product->download_url || $item->product->downloadFiles()->exists()) {
                continue;
            }

            $alreadyAssigned = $licenses->where('order_item_id', $item->id)->count();
            $needed = $item->quantity - $alreadyAssigned;

            for ($i = 0; $i < $needed; $i++) {
                $license = DigitalLicenseKey::query()
                    ->where('product_id', $item->product_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->where('status', 'available')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if (! $license) {
                    $allAssigned = false;
                    if ($strict) {
                        throw ValidationException::withMessages([
                            'product' => ['This digital product is out of stock.'],
                        ]);
                    }
                    break;
                }

                $license->update([
                    'status' => 'assigned',
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'reserved_at' => null,
                    'assigned_at' => now(),
                ]);
                $licenses->push($license);
            }
        }
        return $allAssigned;
    }

    public function releaseExpiredReservations(int $minutes = 30): int
    {
        return DigitalLicenseKey::query()
            ->where('status', 'reserved')
            ->where('reserved_at', '<', now()->subMinutes($minutes))
            ->update([
                'status' => 'available',
                'order_id' => null,
                'order_item_id' => null,
                'reserved_at' => null,
            ]);
    }

    public function revoke(DigitalLicenseKey $license, User $actor): void
    {
        $license->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revoked_by' => $actor->id,
        ]);
    }

    public function reissue(DigitalLicenseKey $license, string $secret, ?string $instructions, User $actor): DigitalLicenseKey
    {
        return DB::transaction(function () use ($license, $secret, $instructions, $actor): DigitalLicenseKey {
            $license->update([
                'status' => 'revoked',
                'revoked_at' => now(),
                'revoked_by' => $actor->id,
            ]);

            $replacement = $this->add(
                $license->product,
                $secret,
                $instructions ?? $license->instructions,
                $actor,
                $license->variant,
            );

            if ($license->order_id) {
                $replacement->update([
                    'status' => 'assigned',
                    'order_id' => $license->order_id,
                    'order_item_id' => $license->order_item_id,
                    'assigned_at' => now(),
                ]);
            }

            return $replacement->fresh();
        });
    }

    public function release(Order $order): void
    {
        DigitalLicenseKey::query()->where('order_id', $order->id)->where('status', 'reserved')->update([
            'status' => 'available',
            'order_id' => null,
            'order_item_id' => null,
            'reserved_at' => null,
        ]);
    }

    public function access(DigitalLicenseKey $license): string
    {
        if ($license->status !== 'assigned' || $license->revoked_at !== null || ! $license->order || ! $license->order->isPaid()) {
            throw ValidationException::withMessages(['download' => ['This digital product is not available yet.']]);
        }
        $license->update(['viewed_at' => $license->viewed_at ?: now(), 'download_count' => $license->download_count + 1]);
        return $license->plain_secret;
    }

}
