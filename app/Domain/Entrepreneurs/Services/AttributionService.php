<?php

namespace App\Domain\Entrepreneurs\Services;

use App\Domain\Entrepreneurs\Models\EntrepreneurProfile;
use App\Domain\Orders\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttributionService
{
    public const SESSION_KEY = 'entrepreneur_attribution';

    public function remember(Request $request, string $code): EntrepreneurProfile
    {
        $profile = $this->resolve($code);

        $request->session()->put(self::SESSION_KEY, [
            'entrepreneur_id' => $profile->user_id,
            'code' => $profile->entrepreneur_code,
            'captured_at' => now()->toIso8601String(),
        ]);

        return $profile;
    }

    public function resolve(?string $code): EntrepreneurProfile
    {
        $profile = EntrepreneurProfile::query()
            ->where('entrepreneur_code', strtoupper(trim((string) $code)))
            ->where('status', 'active')
            ->first();

        if (! $profile) {
            throw ValidationException::withMessages([
                'attribution' => ['The entrepreneur link is invalid or inactive.'],
            ]);
        }

        return $profile;
    }

    /** @return array{entrepreneur_id:int|null, code:string|null, source:string} */
    public function forOrder(Request $request, ?string $explicitCode = null): array
    {
        if ($explicitCode !== null) {
            $profile = $this->resolve($explicitCode);

            return [
                'entrepreneur_id' => $profile->user_id,
                'code' => $profile->entrepreneur_code,
                'source' => 'entrepreneur_link',
            ];
        }

        $attribution = $request->hasSession()
            ? $request->session()->get(self::SESSION_KEY)
            : null;
        if (! is_array($attribution) || empty($attribution['entrepreneur_id'])) {
            return ['entrepreneur_id' => null, 'code' => null, 'source' => 'direct'];
        }

        $profile = EntrepreneurProfile::query()
            ->where('user_id', (int) $attribution['entrepreneur_id'])
            ->where('status', 'active')
            ->first();

        return $profile
            ? ['entrepreneur_id' => $profile->user_id, 'code' => $profile->entrepreneur_code, 'source' => 'entrepreneur_link']
            : ['entrepreneur_id' => null, 'code' => null, 'source' => 'direct'];
    }

    public function assign(Order $order, array $attribution): Order
    {
        $order->forceFill([
            'entrepreneur_id' => $attribution['entrepreneur_id'],
            'attribution_code' => $attribution['code'],
            'source' => $attribution['source'],
        ])->save();

        return $order;
    }
}
