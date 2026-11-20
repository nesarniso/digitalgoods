<?php

namespace App\Http\Requests\Storefront;

use App\Domain\Commerce\Services\CartService;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(CartService $cartService): array
    {
        $isAuthenticated = $this->user('sanctum') !== null || $this->user() !== null;
        $existingAccount = ! $isAuthenticated
            && filled($this->input('guest_email'))
            && User::query()->where('email', strtolower(trim((string) $this->input('guest_email'))))->exists();
        $digitalOnly = $cartService->isDigitalOnlyFor(
            $this->user('sanctum') ?: $this->user(),
            $this->header('X-Guest-Cart-Token'),
        );
        $addressRequired = $digitalOnly ? 'nullable' : 'required_without:address_id';

        return [
            'idempotency_key' => ['required', 'string', 'max:100'],

            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'name' => [$digitalOnly ? 'required' : 'nullable', 'string', 'max:255'],
            'phone' => [$digitalOnly ? 'required' : 'nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'division' => [$addressRequired, 'string', 'max:100'],
            'district' => [$addressRequired, 'string', 'max:100'],
            'upazila' => [$addressRequired, 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address_line' => [$digitalOnly || $isAuthenticated ? 'nullable' : 'required_without:address_id', 'string'],

            'shipping_method_id' => ['nullable', 'integer', 'exists:shipping_methods,id'],
            'payment_method' => ['required', 'string'],
            'payer_mobile' => ['nullable', 'string', 'max:30', 'required_if:payment_method,bkash,nagad,rocket'],
            'transaction_id' => ['nullable', 'string', 'max:100', 'required_if:payment_method,bkash,nagad,rocket'],

            'guest_email' => [$isAuthenticated ? 'nullable' : 'required', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:20'],
            'password' => [$isAuthenticated ? 'nullable' : 'required', 'string', 'min:4'],
            'password_confirmation' => [$existingAccount ? 'nullable' : ($isAuthenticated ? 'nullable' : 'required'), 'string', 'min:4', ...(! $existingAccount && ! $isAuthenticated ? ['same:password'] : [])],

            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
