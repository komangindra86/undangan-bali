<?php

namespace App\Jobs;

use App\Services\ExpoPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckExpoPushReceipts implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public array $receipts) {}

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(ExpoPushService $push): void
    {
        $push->checkReceipts($this->receipts);
    }
}
