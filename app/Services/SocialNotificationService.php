<?php

namespace App\Services;

use App\Models\Invitation;
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
    }
}
