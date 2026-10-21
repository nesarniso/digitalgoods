<?php

namespace App\Http\Requests\Admin\Orders;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('order.update');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,confirmed,processing,packed,shipped,out_for_delivery,delivered'],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }
}
