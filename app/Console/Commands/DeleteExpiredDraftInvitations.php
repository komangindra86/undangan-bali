<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteExpiredDraftInvitations extends Command
{
    protected $signature = 'invitations:delete-expired-drafts {--days=7 : Days after creation before deleting drafts} {--dry-run : Show count without deleting}';

    protected $description = 'Delete unpublished draft invitations after the draft retention window has passed.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $query = Invitation::query()
            ->where('status', 'draft')
            ->whereNull('slug')
            ->whereNull('published_at')
            ->where('created_at', '<', $cutoff);

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("{$count} draft undangan akan dihapus.");

            return self::SUCCESS;
        }

        $processed = 0;
        $deletedFiles = 0;

        $query->orderBy('id')->chunkById(50, function ($invitations) use (&$processed, &$deletedFiles): void {
            foreach ($invitations as $invitation) {
                $deletedFiles += $this->deleteInvitationFiles($invitation);
                $invitation->delete();
                $processed++;
            }
        });

        $this->info("{$processed} draft undangan dihapus. {$deletedFiles} file dihapus.");

        return self::SUCCESS;
    }

    private function deleteInvitationFiles(Invitation $invitation): int
    {
        $files = array_values(array_filter([
            $invitation->groom_photo,
            $invitation->bride_photo,
            $invitation->music_file,
            ...($invitation->gallery_photos ?? []),
        ]));

        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }

        return count($files);
    }
}
