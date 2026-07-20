<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeddingGift;
use App\Services\MidtransService;
use App\Services\SocialNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, MidtransService $midtrans, SocialNotificationService $notifications): JsonResponse
    {
        $payload = $request->all();

        abort_unless($midtrans->hasValidSignature($payload), 403, 'Signature Midtrans tidak valid.');

        $gift = WeddingGift::where('order_id', $payload['order_id'])->firstOrFail();
        abort_unless(
            (int) round((float) ($payload['gross_amount'] ?? -1)) === $gift->total_amount,
            422,
            'Nominal transaksi tidak cocok.'
        );

        $wasPaid = $gift->transaction_status === 'paid';
        $gift = $midtrans->applyTrustedStatus($gift, $payload);

        if (! $wasPaid && $gift->transaction_status === 'paid') {
            $notifications->send($gift->invitation, 'wedding_gift_paid', [
                'gift_id' => $gift->id,
                'guest_name' => $gift->guest_name,
                'gift_amount' => $gift->gift_amount,
                'message' => 'Wedding Gift dari '.$gift->guest_name.' berhasil diterima.',
            ]);
        }

        return response()->json([
            'message' => 'Notifikasi Midtrans diproses.',
            'transaction_status' => $gift->transaction_status,
        ]);
    }
}
