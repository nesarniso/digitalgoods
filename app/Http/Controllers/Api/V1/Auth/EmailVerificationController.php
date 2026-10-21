<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash)
    {
        return response()->json([
            'success' => false,
            'message' => 'Email confirmation is not required for customer accounts.',
            'data' => (object) [],
            'meta' => (object) [],
        ], 410);
    }

    public function resend(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Email confirmation is not required for customer accounts.',
            'data' => (object) [],
            'meta' => (object) [],
        ], 410);
    }
}
