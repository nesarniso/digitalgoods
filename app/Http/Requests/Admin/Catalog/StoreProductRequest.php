<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('product.create');
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['nullable', 'integer', 'exists:users,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'product_type' => ['nullable', 'in:simple,variable,digital'],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'regular_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'minimum_order_quantity' => ['nullable', 'integer', 'min:1'],
            'maximum_order_quantity' => ['nullable', 'integer', 'gte:minimum_order_quantity'],
            'status' => ['nullable', 'in:draft,pending_review,published,rejected'],
            'featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],

            'variants' => ['nullable', 'array'],
            'variants.*.sku' => ['required_with:variants', 'string', 'distinct', 'max:100', 'unique:product_variants,sku'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.weight' => ['nullable', 'numeric', 'min:0'],
            'variants.*.is_default' => ['nullable', 'boolean'],
            'variants.*.attribute_value_ids' => ['nullable', 'array'],
            'variants.*.attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
        ];
    }
}
