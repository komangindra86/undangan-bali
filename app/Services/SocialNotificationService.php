<?php

namespace App\Services;

use App\Jobs\SendFirebasePushNotification;
use App\Models\Invitation;
use App\Models\PushToken;
use App\Models\SocialNotification;
use App\Models\WeddingGift;
use Illuminate\Support\Facades\DB;

class SocialNotificationService
{
    public function send(Invitation $invitation, string $type, array $data): void
    {
        if (! $invitation->user_id) {
            return;
        }

        SocialNotification::create([
            'user_id' => $invitation->user_id,
            'invitation_id' => $invitation->id,
            'type' => $type,
            'data' => $data,
        ]);

        if (PushToken::where('user_id', $invitation->user_id)->whereNull('disabled_at')->exists()) {
            [$title, $body] = $this->pushCopy($type, $data, $invitation->gift_label);
            SendFirebasePushNotification::dispatch(
                $invitation->user_id,
                $invitation->id,
                $type,
                $title,
                $body,
            )->afterCommit();
        }
    }

    public function sendGiftPaidIfNeeded(WeddingGift $gift): bool
    {
        return DB::transaction(function () use ($gift) {
            $claimed = WeddingGift::query()
                ->whereKey($gift->id)
                ->where('transaction_status', 'paid')
                ->whereNull('paid_notification_sent_at')
                ->update(['paid_notification_sent_at' => now()]);

            if ($claimed !== 1) {
                return false;
            }

            $gift = WeddingGift::with('invitation')->findOrFail($gift->id);
            $this->send($gift->invitation, 'wedding_gift_paid', [
                'gift_id' => $gift->id,
                'guest_name' => $gift->guest_name,
                'gift_amount' => $gift->gift_amount,
                'message' => $gift->invitation->gift_label.' dari '.$gift->guest_name.' berhasil diterima.',
            ]);

            return true;
        });
    }

    private function pushCopy(string $type, array $data, string $giftLabel): array
    {
        return match ($type) {
            'invitation_request' => [
                'Permintaan undangan baru',
                ($data['requester_name'] ?? 'Seorang tamu').' meminta undangan Anda.',
            ],
            'reaction' => ['Reaksi baru', $data['message'] ?? 'Ada reaksi baru pada Moment Anda.'],
            'comment' => ['Komentar baru', $data['message'] ?? 'Ada komentar baru pada Moment Anda.'],
            'wedding_gift_paid' => [$giftLabel.' diterima', $data['message'] ?? $giftLabel.' baru telah dikonfirmasi.'],
            default => ['Pembaruan undangan', $data['message'] ?? 'Ada pembaruan baru pada undangan Anda.'],
        };
    }
}
