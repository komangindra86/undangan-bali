<?php

namespace App\Services;

use App\Models\WeddingGift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransService
{
    public function chargeQris(WeddingGift $gift): array
    {
        return $this->request()
            ->post($this->baseUrl().'/v2/charge', [
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $gift->order_id,
                    'gross_amount' => $gift->total_amount,
                ],
                'customer_details' => array_filter([
                    'first_name' => $gift->guest_name,
                    'phone' => $gift->guest_phone,
                ]),
                'custom_field1' => (string) $gift->invitation_id,
                'custom_field2' => (string) $gift->gift_amount,
                'custom_field3' => (string) $gift->service_fee,
            ])
            ->throw()
            ->json();
    }

    public function status(string $orderId): array
    {
        return $this->request()
            ->get($this->baseUrl().'/v2/'.rawurlencode($orderId).'/status')
            ->throw()
            ->json();
    }

    public function hasValidSignature(array $notification): bool
    {
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $field) {
            if (! isset($notification[$field])) {
                return false;
            }
        }

        $signature = hash(
            'sha512',
            $notification['order_id'].$notification['status_code'].$notification['gross_amount'].$this->serverKey()
        );

        return hash_equals($signature, $notification['signature_key']);
    }

    public function applyTrustedStatus(WeddingGift $gift, array $payload): WeddingGift
    {
        return DB::transaction(function () use ($gift, $payload) {
            $locked = WeddingGift::query()->lockForUpdate()->findOrFail($gift->id);
            $incoming = $this->normalizedStatus($payload);
            $updates = [
                'midtrans_transaction_id' => $payload['transaction_id'] ?? $locked->midtrans_transaction_id,
                'payment_type' => $payload['payment_type'] ?? $locked->payment_type,
                'fraud_status' => $payload['fraud_status'] ?? $locked->fraud_status,
                'raw_response' => $payload,
            ];

            if ($incoming === 'refunded') {
                $updates['transaction_status'] = 'refunded';
                $locked->fee()->updateOrCreate([], [
                    'amount' => $locked->service_fee,
                    'status' => 'refunded',
                ]);
            } elseif ($incoming === 'paid') {
                $updates['transaction_status'] = 'paid';
                $updates['paid_at'] = $locked->paid_at ?: $this->paidTime($payload);
                $locked->fee()->updateOrCreate([], [
                    'amount' => $locked->service_fee,
                    'status' => 'earned',
                ]);
            } elseif ($locked->transaction_status !== 'paid' && $locked->transaction_status !== 'refunded') {
                $updates['transaction_status'] = $incoming;
                if ($incoming === 'expired') {
                    $updates['expired_at'] = $locked->expired_at ?: now();
                }
                if (in_array($incoming, ['expired', 'cancelled', 'denied', 'failure'], true)) {
                    $locked->fee()->updateOrCreate([], [
                        'amount' => $locked->service_fee,
                        'status' => 'refunded',
                    ]);
                }
            }

            $locked->update($updates);

            return $locked->fresh(['fee']);
        });
    }

    private function normalizedStatus(array $payload): string
    {
        $transactionStatus = $payload['transaction_status'] ?? 'pending';
        $fraudStatus = $payload['fraud_status'] ?? null;

        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
            return 'paid';
        }

        return match ($transactionStatus) {
            'expire' => 'expired',
            'cancel' => 'cancelled',
            'deny' => 'denied',
            'failure' => 'failure',
            'refund', 'partial_refund', 'chargeback' => 'refunded',
            default => 'pending',
        };
    }

    private function paidTime(array $payload): Carbon
    {
        $time = $payload['settlement_time'] ?? $payload['transaction_time'] ?? null;

        return $time ? Carbon::parse($time) : now();
    }

    private function request()
    {
        return Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    private function serverKey(): string
    {
        $key = (string) config('services.midtrans.server_key');

        if ($key === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        return $key;
    }

    private function baseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }
}
