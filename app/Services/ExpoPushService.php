<?php

namespace App\Services;

use App\Jobs\CheckExpoPushReceipts;
use App\Models\PushToken;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ExpoPushService
{
    public function sendToUser(int $userId, string $title, string $body, array $data = []): void
    {
        PushToken::query()
            ->where('user_id', $userId)
            ->whereNull('disabled_at')
            ->get()
            ->chunk(100)
            ->each(function ($tokens) use ($title, $body, $data) {
                $messages = $tokens->map(fn (PushToken $token) => [
                    'to' => $token->token,
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                    'sound' => 'default',
                    'priority' => 'high',
                    'channelId' => 'social',
                    'ttl' => 86400,
                ])->values()->all();

                $response = $this->request()
                    ->post(config('services.expo.push_url'), $messages)
                    ->throw();
                $tickets = $response->json('data', []);
                $receipts = [];

                foreach ($tokens->values() as $index => $token) {
                    $ticket = $tickets[$index] ?? null;
                    if (($ticket['status'] ?? null) === 'ok' && ! empty($ticket['id'])) {
                        $token->update([
                            'last_ticket_id' => $ticket['id'],
                            'last_error' => null,
                        ]);
                        $receipts[$ticket['id']] = $token->id;

                        continue;
                    }

                    $this->recordError(
                        $token,
                        $ticket['details']['error'] ?? 'PushTicketError',
                        $ticket['message'] ?? 'Expo tidak menerima push notification.'
                    );
                }

                if ($receipts !== []) {
                    CheckExpoPushReceipts::dispatch($receipts)->delay(now()->addMinutes(15));
                }
            });
    }

    public function checkReceipts(array $receiptTokenMap): void
    {
        foreach (array_chunk($receiptTokenMap, 1000, true) as $receiptChunk) {
            $response = $this->request()
                ->post(config('services.expo.receipt_url'), ['ids' => array_keys($receiptChunk)])
                ->throw();
            $receipts = $response->json('data', []);

            foreach ($receipts as $receiptId => $receipt) {
                $tokenId = $receiptChunk[$receiptId] ?? null;
                $token = $tokenId ? PushToken::find($tokenId) : null;
                if (! $token) {
                    continue;
                }

                if (($receipt['status'] ?? null) === 'ok') {
                    $token->update(['last_error' => null]);

                    continue;
                }

                $this->recordError(
                    $token,
                    $receipt['details']['error'] ?? 'PushReceiptError',
                    $receipt['message'] ?? 'Push notification tidak diterima perangkat.'
                );
            }
        }
    }

    private function request(): PendingRequest
    {
        $request = Http::acceptJson()->asJson()->timeout(10);
        $accessToken = config('services.expo.access_token');

        return $accessToken ? $request->withToken($accessToken) : $request;
    }

    private function recordError(PushToken $token, string $code, string $message): void
    {
        $token->update([
            'last_error' => Str::limit($code.': '.$message, 255, ''),
            'disabled_at' => $code === 'DeviceNotRegistered' ? now() : $token->disabled_at,
        ]);
    }
}
