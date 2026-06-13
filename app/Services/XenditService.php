<?php

namespace App\Services;

use App\Models\WeddingGift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class XenditService
{
    public function createInvoice(WeddingGift $gift): array
    {
        return $this->request()
            ->post($this->baseUrl().'/v2/invoices', [
                'external_id' => $gift->order_id,
                'amount' => $gift->total_amount,
                'description' => 'Wedding Gift '.$gift->invitation->groom_nickname.' & '.$gift->invitation->bride_nickname,
                'currency' => 'IDR',
                'customer' => array_filter([
                    'given_names' => $gift->guest_name,
                    'mobile_number' => $gift->guest_phone,
                ]),
                'success_redirect_url' => url('/u/'.$gift->invitation->slug.'?payment=success&order_id='.$gift->order_id),
                'failure_redirect_url' => url('/u/'.$gift->invitation->slug.'?payment=failed&order_id='.$gift->order_id),
                'metadata' => [
                    'invitation_id' => $gift->invitation_id,
                    'gift_amount' => $gift->gift_amount,
                    'service_fee' => $gift->service_fee,
                ],
            ])
            ->throw()
            ->json();
    }

    public function invoice(string $invoiceId): array
    {
        return $this->request()
            ->get($this->baseUrl().'/v2/invoices/'.rawurlencode($invoiceId))
            ->throw()
            ->json();
    }

    public function hasValidCallbackToken(?string $token): bool
    {
        $expected = (string) config('services.xendit.webhook_token');

        if ($expected === '') {
            throw new RuntimeException('XENDIT_WEBHOOK_TOKEN belum dikonfigurasi.');
        }

        return is_string($token) && hash_equals($expected, $token);
    }

    public function applyTrustedStatus(WeddingGift $gift, array $payload): WeddingGift
    {
        return DB::transaction(function () use ($gift, $payload) {
            $locked = WeddingGift::query()->lockForUpdate()->findOrFail($gift->id);
            $incoming = $this->normalizedStatus($payload['status'] ?? 'PENDING');
            $updates = [
                'midtrans_transaction_id' => $payload['id'] ?? $locked->midtrans_transaction_id,
                'payment_type' => 'xendit_invoice',
                'raw_response' => $payload,
            ];

            if ($incoming === 'paid') {
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
                if (in_array($incoming, ['expired', 'failure'], true)) {
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

    private function normalizedStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'PAID', 'SETTLED' => 'paid',
            'EXPIRED' => 'expired',
            'FAILED' => 'failure',
            default => 'pending',
        };
    }

    private function paidTime(array $payload): Carbon
    {
        $time = $payload['paid_at'] ?? $payload['updated'] ?? null;

        return $time ? Carbon::parse($time) : now();
    }

    private function request()
    {
        return Http::withBasicAuth($this->secretKey(), '')
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    private function secretKey(): string
    {
        $key = (string) config('services.xendit.secret_key');

        if ($key === '') {
            throw new RuntimeException('XENDIT_SECRET_KEY belum dikonfigurasi.');
        }

        return $key;
    }

    private function baseUrl(): string
    {
        return 'https://api.xendit.co';
    }
}
