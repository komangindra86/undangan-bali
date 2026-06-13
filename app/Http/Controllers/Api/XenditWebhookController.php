<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeddingGift;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request, XenditService $xendit): JsonResponse
    {
        abort_unless($xendit->hasValidCallbackToken($request->header('x-callback-token')), 403, 'Token webhook Xendit tidak valid.');

        $payload = $request->all();
        $gift = WeddingGift::where('order_id', $payload['external_id'] ?? null)->first();

        if (! $gift) {
            Log::info('Xendit webhook ignored because external_id is not registered.', [
                'external_id' => $payload['external_id'] ?? null,
                'invoice_id' => $payload['id'] ?? null,
                'status' => $payload['status'] ?? null,
            ]);

            return response()->json([
                'message' => 'Notifikasi Xendit diterima, tetapi external_id tidak terdaftar.',
                'transaction_status' => 'ignored',
            ]);
        }

        abort_unless((int) ($payload['amount'] ?? -1) === $gift->total_amount, 422, 'Nominal transaksi tidak cocok.');

        $gift = $xendit->applyTrustedStatus($gift, $payload);

        return response()->json([
            'message' => 'Notifikasi Xendit diproses.',
            'transaction_status' => $gift->transaction_status,
        ]);
    }
}
