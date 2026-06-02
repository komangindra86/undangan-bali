<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWeddingGiftRequest;
use App\Models\Invitation;
use App\Models\WeddingGift;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PublicWeddingGiftController extends Controller
{
    public function store(StoreWeddingGiftRequest $request, string $slug, MidtransService $midtrans): JsonResponse
    {
        $invitation = Invitation::with('giftSetting')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
        $setting = $invitation->giftSetting;

        abort_unless($setting?->is_active, 404);

        if ($request->integer('gift_amount') < $setting->minimum_amount) {
            throw ValidationException::withMessages([
                'gift_amount' => 'Nominal gift minimal Rp'.number_format($setting->minimum_amount, 0, ',', '.').'.',
            ]);
        }

        $serviceFee = $setting->serviceFeeFor($request->integer('gift_amount'));
        $gift = $invitation->weddingGifts()->create([
            'guest_name' => $request->string('guest_name')->toString(),
            'guest_phone' => $request->string('guest_phone')->toString() ?: null,
            'message' => $setting->allow_message ? ($request->string('message')->toString() ?: null) : null,
            'gift_amount' => $request->integer('gift_amount'),
            'service_fee' => $serviceFee,
            'total_amount' => $request->integer('gift_amount') + $serviceFee,
            'order_id' => $this->uniqueOrderId($invitation),
            'payment_type' => 'qris',
            'transaction_status' => 'pending',
        ]);
        $gift->fee()->create(['amount' => $serviceFee, 'status' => 'pending']);

        try {
            $response = $midtrans->chargeQris($gift);
            $qrAction = collect($response['actions'] ?? [])->firstWhere('name', 'generate-qr-code');
            $gift->update([
                'midtrans_transaction_id' => $response['transaction_id'] ?? null,
                'qr_string' => $response['qr_string'] ?? null,
                'qr_image_url' => $qrAction['url'] ?? null,
                'raw_response' => $response,
            ]);
        } catch (Throwable $exception) {
            Log::error('Midtrans QRIS charge failed.', ['order_id' => $gift->order_id, 'error' => $exception->getMessage()]);
            $gift->update(['transaction_status' => 'failure']);
            $gift->fee()->update(['status' => 'refunded']);

            return response()->json([
                'message' => 'QRIS belum berhasil dibuat. Silakan coba kembali.',
            ], 502);
        }

        return response()->json([
            'message' => 'QRIS siap dipindai.',
            'data' => $this->publicGiftData($gift->fresh()),
        ], 201);
    }

    public function status(string $orderId, MidtransService $midtrans): JsonResponse
    {
        $gift = WeddingGift::where('order_id', $orderId)->firstOrFail();

        if ($gift->transaction_status === 'pending') {
            try {
                $payload = $midtrans->status($gift->order_id);
                if (($payload['order_id'] ?? null) !== $gift->order_id
                    || (int) round((float) ($payload['gross_amount'] ?? -1)) !== $gift->total_amount) {
                    throw new \RuntimeException('Status Midtrans tidak cocok dengan transaksi.');
                }
                $gift = $midtrans->applyTrustedStatus($gift, $payload);
            } catch (Throwable $exception) {
                Log::warning('Midtrans status check failed.', ['order_id' => $gift->order_id, 'error' => $exception->getMessage()]);
            }
        }

        return response()->json(['data' => $this->publicGiftData($gift)]);
    }

    private function uniqueOrderId(Invitation $invitation): string
    {
        do {
            $orderId = 'WGIFT-'.$invitation->id.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        } while (WeddingGift::where('order_id', $orderId)->exists());

        return $orderId;
    }

    private function publicGiftData(WeddingGift $gift): array
    {
        return [
            'order_id' => $gift->order_id,
            'gift_amount' => $gift->gift_amount,
            'service_fee' => $gift->service_fee,
            'total_amount' => $gift->total_amount,
            'payment_type' => $gift->payment_type,
            'qr_string' => $gift->qr_string,
            'qr_image_url' => $gift->qr_image_url,
            'transaction_status' => $gift->transaction_status,
            'paid_at' => $gift->paid_at,
        ];
    }
}
