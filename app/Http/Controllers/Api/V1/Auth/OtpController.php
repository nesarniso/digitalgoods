<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Services\AuthService;
use App\Domain\Commerce\Services\CartService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpLoginRequest;
use App\Http\Requests\Auth\OtpRequestRequest;
use App\Http\Resources\UserResource;

class OtpController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly CartService $cart,
    ) {}

    public function request(OtpRequestRequest $request)
    {
        $code = $this->auth->requestOtpLogin($request->string('phone'));

        return response()->json([
            'success' => true,
            'message' => 'OTP sent.',
            // debug_code is only ever included outside production — real
            // delivery goes through the SMS adapter wired in Phase 17.
            'data' => app()->isProduction() ? (object) [] : ['debug_code' => $code],
            'meta' => (object) [],
        ]);
    }

    public function login(OtpLoginRequest $request)
    {
        $user = $this->auth->attemptOtpLogin(
            $request->string('phone'),
            $request->string('code'),
            $request,
        );

        $token = $user->createToken('api')->plainTextToken;

        $this->cart->mergeGuestCartIntoUser($request->header('X-Guest-Cart-Token'), $user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($user->load('roles')),
                'token' => $token,
            ],
            'meta' => (object) [],
        ]);
    }
}
