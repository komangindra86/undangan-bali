<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use Illuminate\Console\Command;

class ArchiveExpiredInvitations extends Command
{
    protected $signature = 'invitations:archive-expired {--days=30 : Days after event date before archiving} {--dry-run : Show count without updating}';

    protected $description = 'Archive published invitations after the retention window has passed.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = today()->subDays($days);

        $query = Invitation::query()
            ->withoutRetentionExemptions()
            ->where('status', 'published')
            ->whereDate('event_date', '<=', $cutoff);

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("{$count} undangan akan diarsipkan.");

            return self::SUCCESS;
        }

        $updated = $query->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        $this->info("{$updated} undangan berhasil diarsipkan.");

        return self::SUCCESS;
    }
}
