<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Models\LoginHistory;
use App\Domain\Users\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\WelcomeNotification;
use App\Domain\Auth\Models\PasswordResetCode;
use App\Notifications\PasswordResetCodeNotification;
use Throwable;

/**
 * Sole entry point for authentication flows. Controllers stay thin —
 * validate the request, call this service, return a Resource — per the
 * project's "no business logic in controllers" rule.
 */
class AuthService
{
    /**
     * Registers a new customer: creates the User, assigns the `customer`
     * role, and creates the linked CustomerProfile with a unique referral
     * code — all inside one transaction so a failure never leaves an
     * orphaned User without a profile/role.
     */
    public function registerCustomer(array $data): User
    {
        $data['email'] = strtolower(trim((string) $data['email']));
        $user = DB::transaction(function () use ($data) {
            $phone = filled($data['phone'] ?? null)
                ? trim((string) $data['phone'])
                : null;
            if ($phone && User::query()->where('phone', $phone)->exists()) {
                $phone = null;
            }

            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'name' => filled($data['name'] ?? null)
                    ? trim((string) $data['name'])
                    : 'Customer',
                'email' => $data['email'] ?? null,
                'phone' => $phone,
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('customer');

            CustomerProfile::create([
                'user_id' => $user->id,
                'referral_code' => $this->generateUniqueReferralCode(),
            ]);

            return $user;
        });
        try {
            $user->notify(new WelcomeNotification);
        } catch (Throwable $exception) {
            Log::warning('welcome_email_dispatch_failed', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);
        }
        return $user;
    }

    public function issuePasswordResetCode(string $email): void
    {
        $normalizedEmail = strtolower(trim($email));
        $user = User::query()->where('email', $normalizedEmail)->first();
        if (! $user) {
            return;
        }

        $code = (string) random_int(1000, 9999);
        PasswordResetCode::query()->where('email', $normalizedEmail)->delete();
        PasswordResetCode::create([
            'email' => $normalizedEmail,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);
        try {
            $user->notify(new PasswordResetCodeNotification($code));
        } catch (Throwable $exception) {
            Log::warning('password_reset_code_email_dispatch_failed', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);
        }
    }

    public function resetPasswordWithCode(string $email, string $code, string $password): void
    {
        $normalizedEmail = strtolower(trim($email));
        DB::transaction(function () use ($normalizedEmail, $code, $password): void {
            $reset = PasswordResetCode::query()
                ->where('email', $normalizedEmail)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $reset || $reset->used_at || $reset->expires_at->isPast() || $reset->attempts >= 5 || ! Hash::check($code, $reset->code_hash)) {
                if ($reset && ! $reset->used_at && $reset->attempts < 5) {
                    $reset->increment('attempts');
                }
                throw ValidationException::withMessages(['code' => ['The reset code is invalid or expired.']]);
            }

            $user = User::query()->where('email', $normalizedEmail)->lockForUpdate()->firstOrFail();
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
            $user->tokens()->delete();
            $reset->update(['used_at' => now()]);
        });
    }

    /**
     * Authenticates via email + password. Throws a validation
     * exception (mirroring Laravel's default auth error shape) on failure
     * so the controller can just let it bubble to the API error envelope.
     */
    public function attemptPasswordLogin(string $email, string $password, Request $request): User
    {
        $normalizedEmail = strtolower(trim($email));
        $user = User::query()->where('email', $normalizedEmail)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->recordLogin($user, $request, successful: false, method: 'password');

            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $employeeProfile = $user->employeeProfile()->first();
        if ($employeeProfile && ! $employeeProfile->is_active) {
            $this->recordLogin($user, $request, successful: false, method: 'password');

            throw ValidationException::withMessages([
                'email' => ['This account is inactive and cannot log in.'],
            ]);
        }

        $this->recordLogin($user, $request, successful: true, method: 'password');

        return $user;
    }

    public function logout(User $user, ?string $currentTokenId = null): void
    {
        if ($currentTokenId) {
            $user->tokens()->where('id', $currentTokenId)->delete();

            return;
        }

        // No specific token identified (e.g. session-based logout) — revoke
        // everything for safety.
        $user->tokens()->delete();
    }

    private function recordLogin(?User $user, Request $request, bool $successful, string $method): void
    {
        if (! $user) {
            return;
        }

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'login_method' => $method,
            'successful' => $successful,
            'logged_in_at' => now(),
        ]);
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (CustomerProfile::query()->where('referral_code', $code)->exists());

        return $code;
    }
}
