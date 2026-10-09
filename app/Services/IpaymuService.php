<?php

namespace App\Services;

use App\Models\WeddingGift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * iPaymu API v2 client for gift payments: direct QRIS charge and transaction check.
 *
 * Signature: HMAC-SHA256 of "POST:{va}:{sha256(body)}:{apiKey}" keyed with the API key.
 */
class IpaymuService
{
    /** iPaymu transaction status codes that mean the money has been paid. */
    public const PAID_STATUSES = [1, 6];

    public function isConfigured(): bool
    {
        return filled(config('services.ipaymu.va')) && filled(config('services.ipaymu.api_key'));
    }

    /**
     * Creates a QRIS payment the guest scans on the invitation page.
     * The fee stays with the merchant so the guest pays exactly the gift amount.
     */
    public function chargeQris(WeddingGift $gift): array
    {
        // The merchant account is shared with other Bali Santih sales, so every label names the invitation.
        $label = sprintf('%s %s (undangan #%d)', $gift->invitation->gift_label, $gift->invitation->display_name, $gift->invitation_id);

        $data = $this->post('/payment/direct', [
            'product' => [$label],
            'qty' => [1],
            'price' => [$gift->total_amount],
            'name' => $gift->guest_name,
            'phone' => $gift->guest_phone ?: (string) config('services.ipaymu.fallback_phone'),
            'email' => $this->fallbackEmail(),
            'amount' => $gift->total_amount,
            'notifyUrl' => url('/api/ipaymu/notify'),
            'referenceId' => $gift->order_id,
            'paymentMethod' => 'qris',
            'paymentChannel' => (string) config('services.ipaymu.qris_channel'),
            'feeDirection' => 'MERCHANT',
            'expired' => (int) config('services.ipaymu.expiry_hours'),
            'comments' => $label.' /u/'.$gift->invitation->slug,
        ]);

        if (empty($data['TransactionId']) || (empty($data['QrString']) && empty($data['QrImage']))) {
            throw new RuntimeException('Respons iPaymu tidak berisi TransactionId atau kode QR.');
        }

        return $data;
    }

    /**
     * Transaction data straight from iPaymu (TransactionId, ReferenceId, Status, Amount, ...).
     */
    public function transaction(string $transactionId): array
    {
        return $this->post('/transaction', ['transactionId' => $transactionId, 'account' => config('services.ipaymu.va')]);
    }

    /**
     * True when iPaymu's own record belongs to this gift and covers its amount.
     */
    public function matchesGift(WeddingGift $gift, array $transaction): bool
    {
        if (($transaction['ReferenceId'] ?? null) !== $gift->order_id) {
            return false;
        }

        $amount = $transaction['Amount'] ?? $transaction['Total'] ?? null;

        return $amount === null || (int) round((float) $amount) >= $gift->total_amount;
    }

    public function applyTrustedStatus(WeddingGift $gift, array $transaction): WeddingGift
    {
        return DB::transaction(function () use ($gift, $transaction) {
            $locked = WeddingGift::query()->lockForUpdate()->findOrFail($gift->id);
            $incoming = $this->normalizedStatus((int) ($transaction['Status'] ?? 0));
            $updates = [
                'midtrans_transaction_id' => (string) ($transaction['TransactionId'] ?? $locked->midtrans_transaction_id),
                'raw_response' => $transaction,
            ];

            if ($incoming === 'refunded') {
                $updates['transaction_status'] = 'refunded';
            } elseif ($incoming === 'paid') {
                $updates['transaction_status'] = 'paid';
                $updates['paid_at'] = $locked->paid_at ?: $this->paidTime($transaction);
            } elseif ($locked->transaction_status !== 'paid' && $locked->transaction_status !== 'refunded') {
                $updates['transaction_status'] = $incoming;
                if ($incoming === 'expired') {
                    $updates['expired_at'] = $locked->expired_at ?: now();
                }
            }

            $locked->update($updates);

            return $locked->fresh();
        });
    }

    private function normalizedStatus(int $status): string
    {
        if (in_array($status, self::PAID_STATUSES, true)) {
            return 'paid';
        }

        return match ($status) {
            -2 => 'expired',
            2 => 'cancelled',
            3 => 'refunded',
            4, 5 => 'failure',
            default => 'pending',
        };
    }

    private function paidTime(array $transaction): Carbon
    {
        $time = $transaction['SuccessDate'] ?? $transaction['PaidAt'] ?? null;

        try {
            return $time ? Carbon::parse($time) : now();
        } catch (\Throwable) {
            return now();
        }
    }

    /**
     * Guests are not asked for an e-mail, but iPaymu requires one per transaction.
     */
    private function fallbackEmail(): string
    {
        $configured = (string) config('services.ipaymu.fallback_email');
        if ($configured !== '') {
            return $configured;
        }

        // An IP or localhost APP_URL would not make a valid address.
        $derived = 'tamu@'.parse_url((string) config('app.url'), PHP_URL_HOST);

        return filter_var($derived, FILTER_VALIDATE_EMAIL) ? $derived : 'tamu@undangan.balisantih.com';
    }

    private function post(string $path, array $body): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('IPAYMU_VA atau IPAYMU_API_KEY belum dikonfigurasi.');
        }

        $va = (string) config('services.ipaymu.va');
        $apiKey = (string) config('services.ipaymu.api_key');
        $json = json_encode($body, JSON_UNESCAPED_SLASHES);
        $stringToSign = 'POST:'.$va.':'.strtolower(hash('sha256', $json)).':'.$apiKey;

        $response = Http::acceptJson()
            ->timeout(20)
            ->withHeaders([
                'va' => $va,
                'signature' => hash_hmac('sha256', $stringToSign, $apiKey),
                'timestamp' => now()->format('YmdHis'),
            ])
            ->withBody($json, 'application/json')
            ->post($this->baseUrl().$path);

        $payload = $response->json() ?? [];

        if ($response->failed() || (int) ($payload['Status'] ?? 0) !== 200) {
            throw new RuntimeException(sprintf(
                'iPaymu %s gagal (HTTP %d): %s',
                $path,
                $response->status(),
                is_string($payload['Message'] ?? null) ? $payload['Message'] : $response->body(),
            ));
        }

        return $payload['Data'] ?? [];
    }

    private function baseUrl(): string
    {
        return config('services.ipaymu.sandbox') ? 'https://sandbox.ipaymu.com/api/v2' : 'https://my.ipaymu.com/api/v2';
    }
}
