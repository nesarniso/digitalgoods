<?php

namespace App\Http\Requests\Admin\Shipping;

use Illuminate\Foundation\Http\FormRequest;

class SetShippingRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.manage');
    }

    public function rules(): array
    {
        return [
            'shipping_zone_id' => ['nullable', 'integer', 'exists:shipping_zones,id'],
            'base_rate' => ['required', 'numeric', 'min:0'],
            'per_kg_rate' => ['nullable', 'numeric', 'min:0'],
            'free_above_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
