<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'in:damaged,wrong_item,not_as_described,changed_mind,other'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
