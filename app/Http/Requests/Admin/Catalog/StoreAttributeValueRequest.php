<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttributeValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('product.create');
    }

    public function rules(): array
    {
        return [
            'value' => ['required', 'string', 'max:100'],
        ];
    }
}
