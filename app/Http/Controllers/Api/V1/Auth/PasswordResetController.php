<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Domain\Auth\Services\AuthService;

class PasswordResetController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function sendResetLink(ForgotPasswordRequest $request)
    {
        $this->auth->issuePasswordResetCode($request->string('email')->toString());

        return response()->json([
            'success' => true,
            'message' => 'If an account exists for that email, a reset code has been sent.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $this->auth->resetPasswordWithCode(
            $request->string('email')->toString(),
            (string) ($request->input('code') ?: $request->input('token')),
            $request->string('password')->toString(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }
}
