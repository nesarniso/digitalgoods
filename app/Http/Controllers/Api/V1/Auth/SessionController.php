<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Device/session management (spec section 4). Sanctum personal-access
 * tokens double as "devices" here — each login issues one token, so
 * listing/revoking tokens is listing/revoking active sessions.
 */
class SessionController extends Controller
{
    public function index(Request $request)
    {
        $tokens = $request->user()->tokens()
            ->select('id', 'name', 'last_used_at', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Active sessions retrieved.',
            'data' => $tokens,
            'meta' => (object) [],
        ]);
    }

    public function destroy(Request $request, int $tokenId)
    {
        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found.',
                'errors' => (object) [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Session revoked.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }
}
