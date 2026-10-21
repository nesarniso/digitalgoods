<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Services\AuthService;
use App\Domain\Commerce\Services\CartService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly CartService $cart,
    ) {}

    public function __invoke(LoginRequest $request)
    {
        $user = $this->auth->attemptPasswordLogin(
            $request->string('email'),
            $request->string('password'),
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
