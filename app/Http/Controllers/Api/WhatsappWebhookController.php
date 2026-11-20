<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    /**
     * Handle incoming status callbacks / webhooks from OneSender.
     */
    public function handleOneSender(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('[WA Webhook Received] Raw payload: ', $payload);

        if (empty($payload)) {
            return response()->json(['status' => 'ignored', 'message' => 'Empty payload'], 200);
        }

        $processed = WhatsappService::processWebhook($payload);

        return response()->json([
            'status'    => $processed ? 'success' : 'unmatched',
            'processed' => $processed,
        ], 200);
    }
}
