<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Notifications\Models\NotificationDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    public function send(NotificationDelivery $delivery): void
    {
        $token = $delivery->subscription?->token;
        $credentialsPath = config('services.fcm.credentials');
        if (! $token || ! $credentialsPath || ! is_file($credentialsPath)) {
            throw new \RuntimeException('Firebase credentials or device token is not configured.');
        }

        $credentials = json_decode((string) file_get_contents($credentialsPath), true, 512, JSON_THROW_ON_ERROR);
        $project = config('services.fcm.project_id') ?: ($credentials['project_id'] ?? null);
        if (! $project || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new \RuntimeException('Firebase service-account credentials are incomplete.');
        }

        $now = time();
        $claims = ['iss' => $credentials['client_email'], 'scope' => 'https://www.googleapis.com/auth/firebase.messaging', 'aud' => 'https://oauth2.googleapis.com/token', 'iat' => $now, 'exp' => $now + 3600];
        $jwt = $this->jwt($claims, $credentials['private_key']);
        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', ['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt])->throw()->json();
        $response = Http::withToken($tokenResponse['access_token'])->post("https://fcm.googleapis.com/v1/projects/{$project}/messages:send", [
            'message' => ['token' => $token, 'notification' => ['title' => $delivery->title, 'body' => $delivery->body], 'webpush' => ['fcm_options' => ['link' => (string) ($delivery->url ?: '/')]], 'data' => ['url' => (string) ($delivery->url ?: '/')]],
        ]);
        if ($response->failed()) {
            $error = (string) $response->body();
            if ($response->status() === 404 || str_contains($error, 'UNREGISTERED') || str_contains($error, 'registration-token-not-registered')) {
                $delivery->subscription?->update(['revoked_at' => now()]);
            }
            $response->throw();
        }
    }

    private function jwt(array $claims, string $privateKey): string
    {
        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->base64Url(json_encode($claims, JSON_THROW_ON_ERROR));
        openssl_sign("{$header}.{$payload}", $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return "{$header}.{$payload}.".$this->base64Url($signature);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
