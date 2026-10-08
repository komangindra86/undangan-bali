<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeddingGift;
use App\Services\IpaymuService;
use App\Services\SocialNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class IpaymuNotificationController extends Controller
{
    /**
     * iPaymu notifyUrl. The payload only locates the gift; the paid status is
     * always read back from iPaymu's API, never taken from the request.
     */
    public function handle(Request $request, IpaymuService $ipaymu, SocialNotificationService $notifications): Response
    {
        $transactionId = (string) $request->input('trx_id');
        $gift = WeddingGift::where('order_id', (string) $request->input('reference_id'))
            ->where('payment_type', 'ipaymu_qris')
            ->first();

        if (! $gift || $transactionId === '') {
            return response('IGNORED', 200);
        }

        try {
            $transaction = $ipaymu->transaction($transactionId);

            if (! $ipaymu->matchesGift($gift, $transaction)) {
                Log::warning('iPaymu transaction does not match the gift.', [
                    'order_id' => $gift->order_id,
                    'transaction' => $transactionId,
                ]);

                return response('IGNORED', 200);
            }

            $gift = $ipaymu->applyTrustedStatus($gift, $transaction);
            $notifications->sendGiftPaidIfNeeded($gift);
        } catch (Throwable $exception) {
            report($exception);

            // A non-2xx answer makes iPaymu send the notification again later.
            return response('RETRY', 500);
        }

        return response('OK', 200);
    }
}
