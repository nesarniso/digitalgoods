<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Notifications\Models\NotificationDelivery;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('storefront.account.notifications', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
        ]);
    }

    public function read(Request $request, string $notification)
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return $this->safeRedirect($request->input('url'), back());
    }

    public function click(NotificationDelivery $delivery)
    {
        $delivery->update(['clicked_at' => $delivery->clicked_at ?: now()]);

        return $this->safeRedirect($delivery->destination_url, redirect('/'));
    }

    public function entrepreneurIndex(Request $request)
    {
        return view('entrepreneur.notifications', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
        ]);
    }

    public function entrepreneurRead(Request $request, string $notification)
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return $this->safeRedirect($request->input('url'), redirect()->route('entrepreneur.notifications'));
    }

    private function safeRedirect(?string $url, \Symfony\Component\HttpFoundation\Response $fallback): \Symfony\Component\HttpFoundation\Response
    {
        if (! filled($url)) {
            return $fallback;
        }

        $target = parse_url($url);
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $targetHost = $target['host'] ?? null;

        if (($targetHost === null && str_starts_with($url, '/'))
            || ($targetHost !== null && $targetHost === $appHost)) {
            return redirect()->to($url);
        }

        return $fallback;
    }
}
