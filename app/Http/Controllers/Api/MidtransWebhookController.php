<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeddingGift;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, MidtransService $midtrans): JsonResponse
    {
        $payload = $request->all();

        abort_unless($midtrans->hasValidSignature($payload), 403, 'Signature Midtrans tidak valid.');

        $gift = WeddingGift::where('order_id', $payload['order_id'])->firstOrFail();
        abort_unless(
            (int) round((float) ($payload['gross_amount'] ?? -1)) === $gift->total_amount,
            422,
            'Nominal transaksi tidak cocok.'
        );

        $gift = $midtrans->applyTrustedStatus($gift, $payload);

        return response()->json([
            'message' => 'Notifikasi Midtrans diproses.',
            'transaction_status' => $gift->transaction_status,
        ]);
    }
}
