<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('role.manage');
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'exists:roles,name'],
        ];
    }
}
