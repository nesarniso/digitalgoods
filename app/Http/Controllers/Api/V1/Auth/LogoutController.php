<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function __invoke(Request $request)
    {
        $currentToken = $request->user()->currentAccessToken();

        $this->auth->logout($request->user(), $currentToken?->id);

        return response()->json([
            'success' => true,
            'message' => 'Logged out.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }
}
