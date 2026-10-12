<?php

namespace Tests\Feature;

use App\Jobs\SendFirebasePushNotification;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\PushToken;
use App\Models\SocialNotification;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CeremonyReminderTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $birth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InvitationTemplateSeeder::class);
        Queue::fake();
        $this->birth = Carbon::parse('2026-06-01');
    }

    public function test_abulan_pitung_dina_leads_to_reminders_for_tigang_sasih_and_otonan(): void
    {
        $owner = $this->ownerWithDevice();
        $invitation = $this->invitation($owner, 'pitung_dina', $this->birth->copy()->addDays(42), ['child_birth_date' => $this->birth, 'child_nickname' => 'Kirana']);

        // Fifteen days ahead is too early; fourteen is the first reminder.
        $this->runOn($this->birth->copy()->addDays(90))->expectsOutput('0 pengingat upacara dikirim.');
        $this->runOn($this->birth->copy()->addDays(91))->expectsOutput('1 pengingat upacara dikirim.');

        $reminder = SocialNotification::sole();
        $this->assertSame('ceremony_reminder', $reminder->type);
        $this->assertSame($owner->id, $reminder->user_id);
        $this->assertSame($invitation->id, $reminder->invitation_id);
        $this->assertSame('tigang_sasih', $reminder->data['target_type']);
        $this->assertSame('2026-09-14', $reminder->data['ceremony_date']);
        $this->assertSame(14, $reminder->data['days_before']);
        $this->assertSame('Tigang Sasih 14 hari lagi', $reminder->data['title']);
        $this->assertSame('Tigang Sasih (3 Bulanan) Kirana jatuh sekitar Senin, 14 September 2026. Buat undangannya sekarang.', $reminder->data['message']);
        Queue::assertPushed(SendFirebasePushNotification::class, fn ($job) => $job->userId === $owner->id
            && $job->type === 'ceremony_reminder'
            && $job->title === 'Tigang Sasih 14 hari lagi'
            && str_contains($job->body, 'Kirana'));

        // Once per stage: nothing more that day or the next, then the seven-day reminder, then nothing.
        $this->runOn($this->birth->copy()->addDays(91))->expectsOutput('0 pengingat upacara dikirim.');
        $this->runOn($this->birth->copy()->addDays(92))->expectsOutput('0 pengingat upacara dikirim.');
        $this->runOn($this->birth->copy()->addDays(98))->expectsOutput('1 pengingat upacara dikirim.');
        $this->runOn($this->birth->copy()->addDays(104))->expectsOutput('0 pengingat upacara dikirim.');
        $this->runOn($this->birth->copy()->addDays(105))->expectsOutput('0 pengingat upacara dikirim.');

        // Then the first otonan, 210 days after the birth.
        $this->runOn($this->birth->copy()->addDays(196))->expectsOutput('1 pengingat upacara dikirim.');
        $this->runOn($this->birth->copy()->addDays(203))->expectsOutput('1 pengingat upacara dikirim.');
        $this->assertSame(
            [['tigang_sasih', 14], ['tigang_sasih', 7], ['otonan', 14], ['otonan', 7]],
            SocialNotification::orderBy('id')->get()->map(fn ($item) => [$item->data['target_type'], $item->data['days_before']])->all()
        );
        $this->assertSame('Otonan Kirana jatuh sekitar Senin, 28 Desember 2026. Buat undangannya sekarang.', SocialNotification::latest('id')->first()->data['message']);
        Queue::assertPushed(SendFirebasePushNotification::class, 4);
    }

    public function test_without_a_birth_date_the_dates_count_from_the_ceremony_and_a_missed_day_is_caught_up(): void
    {
        $owner = $this->ownerWithDevice();
        // Held on day 42, so tigang sasih is 63 days later; the invitation has long been archived by then.
        $this->invitation($owner, 'pitung_dina', $this->birth->copy()->addDays(42), ['status' => 'archived']);

        // The run fourteen days ahead was missed; the next day still sends that reminder.
        $this->runOn($this->birth->copy()->addDays(93))->expectsOutput('1 pengingat upacara dikirim.');

        $reminder = SocialNotification::sole();
        $this->assertSame(14, $reminder->data['days_before']);
        $this->assertSame('Tigang Sasih 12 hari lagi', $reminder->data['title']);
        $this->assertStringContainsString('buah hati Anda jatuh sekitar Senin, 14 September 2026', $reminder->data['message']);
    }

    public function test_no_reminder_once_the_owner_has_started_that_invitation(): void
    {
        $owner = $this->ownerWithDevice();
        $this->invitation($owner, 'pitung_dina', $this->birth->copy()->addDays(42), ['child_birth_date' => $this->birth]);
        // An undated tigang sasih draft started afterwards, and an otonan invitation dated near the oton day.
        $this->invitation($owner, 'tigang_sasih', null, ['status' => 'draft', 'published_at' => null, 'created_at' => $this->birth->copy()->addDays(60)]);
        $this->invitation($owner, 'otonan', $this->birth->copy()->addDays(212), ['status' => 'draft', 'published_at' => null]);

        foreach ([91, 98, 196, 203] as $day) {
            $this->runOn($this->birth->copy()->addDays($day))->expectsOutput('0 pengingat upacara dikirim.');
        }
        $this->assertDatabaseCount('social_notifications', 0);
        Queue::assertNothingPushed();
    }

    public function test_tigang_sasih_leads_to_otonan_without_repeating_the_reminder_from_abulan_pitung_dina(): void
    {
        $owner = $this->ownerWithDevice();
        // Same child, two invitations; their otonan dates differ by two days because only one has the birth date.
        $this->invitation($owner, 'pitung_dina', $this->birth->copy()->addDays(42), ['child_birth_date' => $this->birth]);
        $this->invitation($owner, 'tigang_sasih', $this->birth->copy()->addDays(107), ['child_nickname' => 'Kirana']);

        $this->runOn($this->birth->copy()->addDays(196))->expectsOutput('1 pengingat upacara dikirim.');
        $this->runOn($this->birth->copy()->addDays(198))->expectsOutput('0 pengingat upacara dikirim.');
        $this->assertSame('otonan', SocialNotification::sole()->data['target_type']);

        // On its own, a tigang sasih invitation leads to the otonan too.
        $other = $this->ownerWithDevice();
        $this->invitation($other, 'tigang_sasih', $this->birth->copy()->addDays(105), ['child_nickname' => 'Bagus']);
        $this->runOn($this->birth->copy()->addDays(199))->expectsOutput('1 pengingat upacara dikirim.');
        $this->assertSame('Otonan 11 hari lagi', SocialNotification::where('user_id', $other->id)->sole()->data['title']);
    }

    public function test_every_otonan_leads_to_the_next_one(): void
    {
        $owner = $this->ownerWithDevice();
        $held = $this->birth->copy()->addDays(630);
        $this->invitation($owner, 'otonan', $held, ['child_nickname' => 'Kirana', 'ceremony_number' => 3, 'child_birth_date' => $this->birth]);

        $this->runOn($held->copy()->addDays(195))->expectsOutput('0 pengingat upacara dikirim.');
        $this->runOn($held->copy()->addDays(196))->expectsOutput('1 pengingat upacara dikirim.');

        $reminder = SocialNotification::sole();
        $this->assertSame('otonan', $reminder->data['target_type']);
        $this->assertSame($held->copy()->addDays(210)->toDateString(), $reminder->data['ceremony_date']);
        $this->assertStringStartsWith('Otonan ke-4 Kirana jatuh sekitar', $reminder->data['message']);
    }

    public function test_drafts_ownerless_demo_and_other_invitation_types_are_not_reminded(): void
    {
        $owner = $this->ownerWithDevice();
        $event = $this->birth->copy()->addDays(42);
        $this->invitation($owner, 'pitung_dina', $event, ['status' => 'draft', 'published_at' => null]);
        $this->invitation(null, 'pitung_dina', $event);
        $this->invitation($owner, 'pitung_dina', $event, ['slug' => 'demo-abulan-pitung-dina']);
        $this->invitation($owner, 'megedong', $event);
        $this->invitation($owner, 'wedding', $event);

        $this->runOn($this->birth->copy()->addDays(91))->expectsOutput('0 pengingat upacara dikirim.');
        $this->assertDatabaseCount('social_notifications', 0);
    }

    public function test_a_dry_run_counts_without_sending_and_the_owner_sees_the_reminder_in_the_app(): void
    {
        $owner = User::factory()->create();
        $this->invitation($owner, 'pitung_dina', $this->birth->copy()->addDays(42), ['child_birth_date' => $this->birth]);

        $this->travelTo($this->birth->copy()->addDays(91)->setTime(9, 0));
        $this->artisan('invitations:send-ceremony-reminders --dry-run')->expectsOutput('1 pengingat upacara akan dikirim.')->assertSuccessful();
        $this->assertDatabaseCount('social_notifications', 0);

        // Without a registered device there is no push, but the reminder still reaches the notification list.
        $this->artisan('invitations:send-ceremony-reminders')->expectsOutput('1 pengingat upacara dikirim.')->assertSuccessful();
        Queue::assertNothingPushed();
        $this->actingAs($owner, 'sanctum')->getJson('/api/social/notifications')->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.type', 'ceremony_reminder')
            ->assertJsonPath('data.0.data.target_type', 'tigang_sasih');
    }

    private function runOn(Carbon $day)
    {
        $this->travelTo($day->copy()->setTime(9, 0));

        return $this->artisan('invitations:send-ceremony-reminders')->assertSuccessful();
    }

    private function ownerWithDevice(): User
    {
        $owner = User::factory()->create();
        PushToken::create(['user_id' => $owner->id, 'token' => 'fcm_owner_token:Reminder-device_'.$owner->id, 'platform' => 'android']);

        return $owner;
    }

    private function invitation(?User $owner, string $type, ?Carbon $eventDate, array $attributes = []): Invitation
    {
        $invitation = new Invitation;
        $invitation->forceFill($attributes + [
            'user_id' => $owner?->id,
            'invitation_type' => $type,
            'template_id' => InvitationTemplate::where('invitation_type', $type)->orderBy('id')->value('id'),
            'groom_full_name' => 'I Made Wira Adnyana', 'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu Lestari', 'bride_nickname' => 'Ayu',
            'event_date' => $eventDate,
            'status' => 'published',
            'published_at' => $eventDate?->copy()->subDays(10) ?? now(),
            'slug' => $type.'-'.fake()->unique()->numerify('#####'),
        ])->save();

        return $invitation;
    }
}
