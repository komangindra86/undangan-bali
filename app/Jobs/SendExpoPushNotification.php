<?php

namespace App\Jobs;

use App\Services\ExpoPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendExpoPushNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $userId,
        public int $invitationId,
        public string $type,
        public string $title,
        public string $body,
    ) {}

    public function backoff(): array
    {
        return [10, 60, 180];
    }

    public function handle(ExpoPushService $push): void
    {
        $push->sendToUser(
            $this->userId,
            $this->title,
            $this->body,
            [
                'notification_type' => $this->type,
                'invitation_id' => $this->invitationId,
                'screen' => $this->type === 'invitation_request' ? 'InvitationRequests' : 'Notifications',
            ]
        );
    }
}
