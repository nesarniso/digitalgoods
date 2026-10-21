<?php

namespace App\Http\Controllers;

use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Settings\Services\SettingService;

class StorefrontCommerceController extends Controller
{
    public function cart()
    {
        return view('storefront.cart');
    }

    public function checkout(SettingService $settings)
    {
        return view('storefront.checkout', [
            'savedAddresses' => auth()->check()
                ? auth()->user()->addresses()->latest('is_default')->latest('id')->get()
                : collect(),
            'districts' => config('shipping.bangladesh_districts', []),
            'divisions' => config('shipping.bangladesh_divisions', []),
            'upazilas' => config('shipping.bangladesh_upazilas', []),
            'shippingMethods' => ShippingMethod::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'paymentMethods' => collect(config('payments.methods', []))
                ->filter(fn (array $method, string $key) => ($method['enabled'] ?? false)
                    && $settings->boolean("payments.{$key}.enabled", $method['enabled'] ?? false))
                ->map(function (array $method, string $key) use ($settings) {
                    $method['merchant_number'] = ! empty($method['merchant_setting']) ? $settings->get($method['merchant_setting'], '') : '';
                    $method['qr_code'] = ! empty($method['qr_setting'])
                        ? $settings->get($method['qr_setting'], '')
                        : '';
                    if ($key === 'bank_transfer') {
                        $prefix = 'payments.bank_transfer';
                        $method['bank_details'] = [
                            'bank_name' => $settings->get("{$prefix}.bank_name", ''),
                            'account_name' => $settings->get("{$prefix}.account_name", ''),
                            'account_number' => $settings->get("{$prefix}.account_number", ''),
                            'branch' => $settings->get("{$prefix}.branch", ''),
                            'routing_number' => $settings->get("{$prefix}.routing_number", ''),
                        ];
                    }
                    $accountType = ! empty($method['account_type_setting'])
                        ? $settings->get($method['account_type_setting'], 'merchant')
                        : 'merchant';
                    $method['account_type'] = config("payments.manual_account_types.{$accountType}", 'Merchant');
                    $method['instructions'] = $key === 'bank_transfer'
                        ? $settings->get('payments.bank_transfer.instructions', '')
                        : (! empty($method['instructions_setting'])
                        ? $settings->get($method['instructions_setting'], '')
                        : '');
                    $method['instructions'] = $method['instructions'] ?: str_replace(
                        ':account_type',
                        $method['account_type'],
                        config('payments.manual_default_instruction')
                    );
                    return $method;
                })
                ->sortBy(fn (array $method, string $key) => $key === 'bank_transfer' ? 1 : 0),
        ]);
    }

    public function orderSuccess()
    {
        return view('storefront.order-success');
    }
}
