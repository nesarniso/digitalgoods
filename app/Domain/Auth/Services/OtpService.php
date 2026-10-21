<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Models\OtpCode;
use Illuminate\Support\Facades\Hash;

/**
 * Generates and verifies OTP codes for phone login / verification. Codes
 * are always stored hashed — never in plaintext — and are single-use with
 * a bounded attempt count to prevent brute-forcing a 6-digit code within
 * its TTL.
 */
class OtpService
{
    private const CODE_LENGTH = 6;

    private const TTL_MINUTES = 5;

    private const MAX_ATTEMPTS = 5;

    /**
     * Generates a new OTP, invalidating any previous unconsumed OTP for the
     * same identifier+purpose. Returns the plaintext code so the caller can
     * dispatch it via SMS/email — the code is never persisted in plaintext.
     */
    public function generate(string $identifier, string $purpose = 'login'): string
    {
        OtpCode::query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->delete();

        $code = (string) random_int(100000, 999999);

        OtpCode::create([
            'identifier' => $identifier,
            'code_hash' => Hash::make($code),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        // Phase 17 (Notifications) wires this into the SMS/email adapter.
        // For now the caller is responsible for delivery in dev/testing.
        return $code;
    }

    public function verify(string $identifier, string $code, string $purpose = 'login'): bool
    {
        $otp = OtpCode::query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (! $otp || $otp->isExpired() || $otp->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            return false;
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }
}
