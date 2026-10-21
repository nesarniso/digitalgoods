<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Shipping\Services\CourierWebhookService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JsonException;

class CourierWebhookController extends Controller
{
    public function __invoke(string $courier, Request $request, CourierWebhookService $webhooks): \Illuminate\Http\JsonResponse
    {
        $payload = $request->getContent();

        if ($payload === '') {
            return response()->json(['success' => false, 'message' => 'Empty payload.'], 400);
        }

        try {
            $accepted = $webhooks->process($courier, $payload, $request->headers->all());
        } catch (JsonException) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON payload.'], 400);
        }

        if (! $accepted) {
            return response()->json(['success' => false, 'message' => 'Invalid courier webhook signature.'], 401);
        }

        return response()->json(['success' => true, 'message' => 'Courier webhook accepted.']);
    }
}
