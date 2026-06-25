<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupArchivedInvitationMedia extends Command
{
    protected $signature = 'invitations:cleanup-media {--days=90 : Days after event date before deleting media} {--dry-run : Show count without deleting}';

    protected $description = 'Delete uploaded invitation media after archived invitations pass the media retention window.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = today()->subDays($days);

        $query = Invitation::query()
            ->withoutRetentionExemptions()
            ->where('status', 'archived')
            ->whereNull('media_deleted_at')
            ->whereDate('event_date', '<=', $cutoff);

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("Media dari {$count} undangan archived akan dibersihkan.");

            return self::SUCCESS;
        }

        $processed = 0;
        $deletedFiles = 0;

        $query->orderBy('id')->chunkById(50, function ($invitations) use (&$processed, &$deletedFiles): void {
            foreach ($invitations as $invitation) {
                $deletedFiles += $this->deleteInvitationFiles($invitation);

                $updates = [
                    'groom_photo' => null,
                    'bride_photo' => null,
                    'gallery_photos' => null,
                    'music_file' => null,
                    'media_deleted_at' => now(),
                ];

                if ($invitation->music_type === 'upload') {
                    $updates['music_type'] = 'none';
                    $updates['music_id'] = null;
                }

                $invitation->update($updates);
                $processed++;
            }
        });

        $this->info("Media {$processed} undangan dibersihkan. {$deletedFiles} file dihapus.");

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
