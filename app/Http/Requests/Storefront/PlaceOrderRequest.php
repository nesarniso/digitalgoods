<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isAuthenticated = $this->user('sanctum') !== null || $this->user() !== null;
        $existingAccount = ! $isAuthenticated
            && filled($this->input('guest_email'))
            && User::query()->where('email', strtolower(trim((string) $this->input('guest_email'))))->exists();

        return [
            'idempotency_key' => ['required', 'string', 'max:100'],

            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'division' => ['required_without:address_id', 'string', 'max:100'],
            'district' => ['required_without:address_id', 'string', 'max:100'],
            'upazila' => ['required_without:address_id', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address_line' => [$isAuthenticated ? 'nullable' : 'required_without:address_id', 'string'],

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
