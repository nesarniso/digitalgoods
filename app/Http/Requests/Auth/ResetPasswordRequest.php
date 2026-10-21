<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['nullable', 'digits:4', 'required_without:token'],
            'token' => ['nullable', 'digits:4', 'required_without:code'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ];
    }
}
