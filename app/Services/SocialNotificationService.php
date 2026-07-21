<?php

namespace App\Services;

use App\Jobs\SendFirebasePushNotification;
use App\Models\Invitation;
use App\Models\PushToken;
use App\Models\SocialNotification;

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
            [$title, $body] = $this->pushCopy($type, $data);
            SendFirebasePushNotification::dispatch(
                $invitation->user_id,
                $invitation->id,
                $type,
                $title,
                $body,
            )->afterCommit();
        }
    }

    private function pushCopy(string $type, array $data): array
    {
        return match ($type) {
            'invitation_request' => [
                'Permintaan undangan baru',
                ($data['requester_name'] ?? 'Seorang tamu').' meminta undangan Anda.',
            ],
            'reaction' => ['Reaksi baru', $data['message'] ?? 'Ada reaksi baru pada Moment Anda.'],
            'comment' => ['Komentar baru', $data['message'] ?? 'Ada komentar baru pada Moment Anda.'],
            'wedding_gift_paid' => ['Wedding Gift diterima', $data['message'] ?? 'Wedding Gift baru telah dikonfirmasi.'],
            default => ['Pembaruan undangan', $data['message'] ?? 'Ada pembaruan baru pada undangan Anda.'],
        };
    }
}
