<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Either address_id (a saved address — authenticated only, checked
            // for ownership in the controller) or the inline fields below
            // (works for guests and for "ship to a different address").
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],

            'name' => ['required_without:address_id', 'string', 'max:255'],
            'phone' => ['required_without:address_id', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'division' => ['required_without:address_id', 'string', 'max:100'],
            'district' => ['required_without:address_id', 'string', 'max:100'],
            'upazila' => ['required_without:address_id', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address_line' => ['required_without:address_id', 'string'],

            'shipping_method_id' => ['nullable', 'integer', 'exists:shipping_methods,id'],
            'payment_method' => ['required', 'string'],
        ];
    }
}
