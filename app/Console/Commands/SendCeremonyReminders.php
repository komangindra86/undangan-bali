<?php

namespace App\Console\Commands;

use App\Services\CeremonyReminderService;
use Illuminate\Console\Command;

class SendCeremonyReminders extends Command
{
    protected $signature = 'invitations:send-ceremony-reminders {--dry-run : Show count without sending}';

    protected $description = 'Remind owners of child ceremony invitations that the next ceremony is near.';

    public function handle(CeremonyReminderService $reminders): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $count = $reminders->sendDue(dryRun: $dryRun);

        $this->info($dryRun ? "{$count} pengingat upacara akan dikirim." : "{$count} pengingat upacara dikirim.");

        return self::SUCCESS;
    }
}
