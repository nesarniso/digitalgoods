<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Services\AuthService;
use App\Domain\Commerce\Services\CartService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;

class RegisterController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly CartService $cart,
    ) {}

    public function __invoke(RegisterRequest $request)
    {
        $user = $this->auth->registerCustomer($request->validated());
        $token = $user->createToken('api')->plainTextToken;

        // A guest cart, if the client was carrying one (X-Guest-Cart-Token
        // header), gets merged into the new customer's cart right away —
        // otherwise the items they added before registering would
        // disappear.
        $this->cart->mergeGuestCartIntoUser($request->header('X-Guest-Cart-Token'), $user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            'meta' => (object) [],
        ], 201);
    }
}
