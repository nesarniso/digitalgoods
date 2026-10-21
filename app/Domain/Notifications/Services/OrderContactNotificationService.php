<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Orders\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderContactNotificationService
{
    public function statusChanged(Order $order, string $status): void
    {
        $message = "Order {$order->order_number} is now ".str_replace('_', ' ', $status).'.';
        $phone = $this->normalizePhone($order->customer_phone);
        $this->sendSms($phone, $message);
        $this->sendWhatsApp($phone, $message);
    }

    private function sendSms(?string $phone, string $message): void
    {
        if (! filled($phone)) {
            return;
        }

        $driver = config('services.sms.driver', 'log');
        if ($driver === 'disabled') {
            Log::info('Order SMS notification skipped because SMS_DRIVER is disabled.');
            return;
        }

        if ($driver === 'log') {
            Log::channel('payments')->info('order.sms_notification', ['phone' => $phone, 'message' => $message]);
            return;
        }

        if (! filled(config('services.sms.api_url')) || ! filled(config('services.sms.local_provider.api_key'))) {
            Log::warning('Order SMS notification skipped because the SMS provider is not configured.', [
                'driver' => $driver,
            ]);
            return;
        }

        $response = Http::timeout(10)->withToken((string) config('services.sms.local_provider.api_key'))
            ->post((string) config('services.sms.api_url'), [
                'to' => $phone,
                'sender_id' => config('services.sms.local_provider.sender_id'),
                'message' => $message,
            ]);

        if ($response->failed()) {
            Log::error('Order SMS provider rejected notification.', ['status' => $response->status()]);
        }
    }

    private function sendWhatsApp(?string $phone, string $message): void
    {
        if (! filled($phone)) {
            return;
        }

        if (! config('services.whatsapp.enabled')) {
            Log::info('Order WhatsApp notification skipped because WhatsApp notifications are disabled.');
            return;
        }

        if (! filled(config('services.whatsapp.api_url')) || ! filled(config('services.whatsapp.token'))) {
            Log::warning('Order WhatsApp notification skipped because the WhatsApp provider is not configured.');
            return;
        }

        $response = Http::timeout(10)->withToken((string) config('services.whatsapp.token'))
            ->post((string) config('services.whatsapp.api_url'), [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'text',
                'text' => ['body' => $message],
            ]);

        if ($response->failed()) {
            Log::error('WhatsApp provider rejected notification.', ['status' => $response->status()]);
        }
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (! filled($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00880')) {
            return substr($digits, 2);
        }

        if (str_starts_with($digits, '880')) {
            return $digits;
        }

        if (str_starts_with($digits, '01')) {
            return '880'.substr($digits, 1);
        }

        return $digits;
    }
}
