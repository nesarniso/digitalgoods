<?php

namespace App\Domain\Analytics\Services;

use App\Domain\Analytics\Models\AnalyticsEvent;
use App\Domain\Catalog\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnalyticsService
{
    public const COOKIE = 'eflix_visitor_id';

    public function track(Request $request, string $eventType, ?Product $product = null): string
    {
        $visitorId = (string) ($request->attributes->get(self::COOKIE)
            ?: $request->cookie(self::COOKIE)
            ?: Str::uuid());
        $request->attributes->set(self::COOKIE, $visitorId);

        AnalyticsEvent::create([
            'visitor_id' => $visitorId,
            'user_id' => $request->user()?->id,
            'product_id' => $product?->id,
            'event_type' => $eventType,
            'path' => Str::limit($request->path(), 500, ''),
            'occurred_at' => now(),
        ]);

        return $visitorId;
    }
}
