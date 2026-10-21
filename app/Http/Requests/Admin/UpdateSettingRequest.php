<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.manage');
    }

    public function rules(): array
    {
        return [
            'value' => ['present'],
            'group' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'in:string,integer,boolean,json'],
        ];
    }
}
