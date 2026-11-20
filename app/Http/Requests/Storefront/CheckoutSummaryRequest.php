<?php

namespace App\Http\Requests\Storefront;

use App\Domain\Commerce\Services\CartService;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(CartService $cartService): array
    {
        $digitalOnly = $cartService->isDigitalOnlyFor(
            $this->user('sanctum') ?: $this->user(),
            $this->header('X-Guest-Cart-Token'),
        );
        $addressRequired = $digitalOnly ? 'nullable' : 'required_without:address_id';
        $contactRequired = $digitalOnly ? 'required' : 'required_without:address_id';

        return [
            // Either address_id (a saved address — authenticated only, checked
            // for ownership in the controller) or the inline fields below
            // (works for guests and for "ship to a different address").
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],

            'name' => [$contactRequired, 'string', 'max:255'],
            'phone' => [$contactRequired, 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'division' => [$addressRequired, 'string', 'max:100'],
            'district' => [$addressRequired, 'string', 'max:100'],
            'upazila' => [$addressRequired, 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address_line' => [$addressRequired, 'string'],

            'shipping_method_id' => ['nullable', 'integer', 'exists:shipping_methods,id'],
            'payment_method' => ['required', 'string'],
        ];
    }
}
