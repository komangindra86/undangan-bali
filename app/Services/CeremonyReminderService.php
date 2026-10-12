<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\SocialNotification;
use Carbon\CarbonInterface;

/**
 * Reminds the owner of a child ceremony invitation when the child's next ceremony approaches:
 * after abulan pitung dina come tigang sasih and the first otonan, after tigang sasih the first otonan,
 * and after every otonan the next one.
 */
class CeremonyReminderService
{
    /** Days before the ceremony at which a reminder goes out, each once. */
    public const LEAD_DAYS = [14, 7];

    public const TYPE = 'ceremony_reminder';

    public function __construct(private SocialNotificationService $notifications) {}

    /**
     * The ceremonies that follow this invitation's for the same child, as type => date.
     * Dates count from the birth date, or from the ceremony's own date when no birth date was given.
     */
    public function upcomingCeremonies(Invitation $invitation): array
    {
        $ceremonies = Invitation::CHILD_CEREMONIES;
        $source = $ceremonies[$invitation->invitation_type] ?? null;
        if (! $source || ! $invitation->event_date) {
            return [];
        }

        // A repeating ceremony comes round again one cycle after it was held.
        if ($source['numbered']) {
            return [$invitation->invitation_type => $invitation->event_date->copy()->addDays($source['age_days'])];
        }

        $birth = $invitation->child_birth_date ?: $invitation->event_date->copy()->subDays($source['age_days']);
        $dates = [];
        foreach ($ceremonies as $type => $ceremony) {
            if ($ceremony['age_days'] > $source['age_days']) {
                $dates[$type] = $birth->copy()->addDays($ceremony['age_days']);
            }
        }

        return $dates;
    }

    /**
     * Sends every reminder due today and returns how many; a dry run only counts them.
     */
    public function sendDue(?CarbonInterface $today = null, bool $dryRun = false): int
    {
        $today = ($today ?? today())->copy()->startOfDay();
        $sent = 0;

        Invitation::query()
            ->withoutRetentionExemptions()
            ->whereIn('invitation_type', array_keys(Invitation::CHILD_CEREMONIES))
            // An invitation is archived a month after its event, long before the next ceremony.
            ->whereIn('status', ['published', 'archived'])
            ->whereNotNull('published_at')
            ->whereNotNull('user_id')
            ->whereNotNull('event_date')
            ->whereDate('event_date', '>=', $today->copy()->subDays(400))
            ->eachById(function (Invitation $invitation) use ($today, $dryRun, &$sent) {
                foreach ($this->upcomingCeremonies($invitation) as $type => $date) {
                    $daysLeft = (int) $today->diffInDays($date, false);
                    $stage = $this->stageFor($daysLeft);
                    if (! $stage || $this->alreadyReminded($invitation, $type, $date, $stage) || $this->alreadyCreated($invitation, $type, $date)) {
                        continue;
                    }

                    if (! $dryRun) {
                        $this->notifications->send($invitation, self::TYPE, $this->copy($invitation, $type, $date, $daysLeft) + [
                            'target_type' => $type,
                            'ceremony_date' => $date->toDateString(),
                            'days_before' => $stage,
                        ]);
                    }
                    $sent++;
                }
            });

        return $sent;
    }

    /**
     * The reminder whose window the ceremony is in: 14 from two weeks to eight days ahead, 7 for the last week.
     * Windows rather than exact days, so a missed run or a late invitation still gets its reminder.
     */
    private function stageFor(int $daysLeft): ?int
    {
        if ($daysLeft < 1) {
            return null;
        }

        $stage = null;
        foreach (self::LEAD_DAYS as $lead) {
            if ($daysLeft <= $lead) {
                $stage = $lead;
            }
        }

        return $stage;
    }

    /**
     * Two invitations for the same child (42 hari and tigang sasih) both lead to the same otonan, and their
     * dates can differ by a few days, so a reminder counts as sent when one exists near that date.
     */
    private function alreadyReminded(Invitation $invitation, string $type, CarbonInterface $date, int $stage): bool
    {
        return SocialNotification::query()
            ->where('user_id', $invitation->user_id)
            ->where('type', self::TYPE)
            ->where('data->target_type', $type)
            ->where('data->days_before', $stage)
            ->whereBetween('data->ceremony_date', [$date->copy()->subDays(10)->toDateString(), $date->copy()->addDays(10)->toDateString()])
            ->exists();
    }

    /**
     * No reminder once the owner has an invitation for that ceremony: one dated near it, or an undated draft
     * started after this invitation was published.
     */
    private function alreadyCreated(Invitation $invitation, string $type, CarbonInterface $date): bool
    {
        return Invitation::query()
            ->where('user_id', $invitation->user_id)
            ->where('invitation_type', $type)
            ->whereKeyNot($invitation->id)
            ->where(function ($query) use ($invitation, $date) {
                $query->whereBetween('event_date', [$date->copy()->subDays(30)->toDateString(), $date->copy()->addDays(30)->toDateString()])
                    ->orWhere(function ($query) use ($invitation) {
                        $query->whereNull('event_date')->where('created_at', '>=', $invitation->published_at);
                    });
            })
            ->exists();
    }

    private function copy(Invitation $invitation, string $type, CarbonInterface $date, int $daysLeft): array
    {
        $ceremony = Invitation::CHILD_CEREMONIES[$type];
        $name = $ceremony['title'];
        $label = $name.($ceremony['note'] ? ' ('.$ceremony['note'].')' : '');
        if ($ceremony['numbered'] && $invitation->invitation_type === $type && $invitation->ceremony_number) {
            $label = $name.' ke-'.($invitation->ceremony_number + 1);
        }

        return [
            'title' => $name.' '.$daysLeft.' hari lagi',
            'message' => $label.' '.($invitation->child_nickname ?: 'buah hati Anda').' jatuh sekitar '
                .$date->copy()->locale('id')->translatedFormat('l, d F Y').'. Buat undangannya sekarang.',
        ];
    }
}
