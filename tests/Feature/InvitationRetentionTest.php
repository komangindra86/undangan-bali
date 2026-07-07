<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvitationRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_invitations_are_archived_after_retention_window(): void
    {
        $this->seed();
        $invitation = $this->publishedInvitation(now()->subDays(31)->toDateString());

        Artisan::call('invitations:archive-expired');

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => 'archived',
        ]);
        $this->assertNotNull($invitation->fresh()->archived_at);
    }

    public function test_archived_invitation_media_is_deleted_after_media_retention_window(): void
    {
        Storage::fake('public');
        $this->seed();

        foreach ([
            'invitations/photos/groom.jpg',
            'invitations/photos/bride.jpg',
            'invitations/gallery/one.jpg',
            'invitations/gallery/two.jpg',
            'invitations/musics/song.mp3',
        ] as $path) {
            Storage::disk('public')->put($path, 'fake-file');
        }

        $invitation = $this->publishedInvitation(now()->subDays(91)->toDateString(), [
            'status' => 'archived',
            'archived_at' => now()->subDays(60),
            'groom_photo' => 'invitations/photos/groom.jpg',
            'bride_photo' => 'invitations/photos/bride.jpg',
            'gallery_photos' => ['invitations/gallery/one.jpg', 'invitations/gallery/two.jpg'],
            'music_type' => 'upload',
            'music_file' => 'invitations/musics/song.mp3',
        ]);

        Artisan::call('invitations:cleanup-media');

        Storage::disk('public')->assertMissing('invitations/photos/groom.jpg');
        Storage::disk('public')->assertMissing('invitations/photos/bride.jpg');
        Storage::disk('public')->assertMissing('invitations/gallery/one.jpg');
        Storage::disk('public')->assertMissing('invitations/gallery/two.jpg');
        Storage::disk('public')->assertMissing('invitations/musics/song.mp3');

        $invitation = $invitation->fresh();
        $this->assertNull($invitation->groom_photo);
        $this->assertNull($invitation->bride_photo);
        $this->assertNull($invitation->gallery_photos);
        $this->assertNull($invitation->music_file);
        $this->assertSame('none', $invitation->music_type);
        $this->assertNotNull($invitation->media_deleted_at);

        $this->get($invitation->public_url)
            ->assertOk()
            ->assertSee('Undangan Diarsipkan')
            ->assertSee('media foto/musiknya telah dibersihkan');
    }

    public function test_demo_and_preview_slugs_are_never_archived_or_cleaned_up(): void
    {
        Storage::fake('public');
        $this->seed();
        Storage::disk('public')->put('invitations/photos/demo.jpg', 'fake-file');

        $demo = $this->publishedInvitation(now()->subDays(120)->toDateString(), [
            'slug' => 'demo-wedding-gift-xendit',
            'groom_photo' => 'invitations/photos/demo.jpg',
        ]);
        $preview = $this->publishedInvitation(now()->subDays(120)->toDateString(), [
            'slug' => 'preview-bali-classic',
        ]);

        Artisan::call('invitations:archive-expired');
        Artisan::call('invitations:cleanup-media');

        $this->assertSame('published', $demo->fresh()->status);
        $this->assertSame('published', $preview->fresh()->status);
        $this->assertNull($demo->fresh()->media_deleted_at);
        Storage::disk('public')->assertExists('invitations/photos/demo.jpg');
    }

    public function test_unpublished_drafts_older_than_retention_window_are_deleted_with_media(): void
    {
        Storage::fake('public');
        $this->seed();

        foreach ([
            'invitations/photos/draft-groom.jpg',
            'invitations/photos/draft-bride.jpg',
            'invitations/gallery/draft-one.jpg',
            'invitations/musics/draft-song.mp3',
        ] as $path) {
            Storage::disk('public')->put($path, 'fake-file');
        }

        $expiredDraft = $this->draftInvitation([
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
            'groom_photo' => 'invitations/photos/draft-groom.jpg',
            'bride_photo' => 'invitations/photos/draft-bride.jpg',
            'gallery_photos' => ['invitations/gallery/draft-one.jpg'],
            'music_type' => 'upload',
            'music_file' => 'invitations/musics/draft-song.mp3',
        ]);
        $recentDraft = $this->draftInvitation([
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);
        $republishDraft = $this->draftInvitation([
            'slug' => 'retention-republish-draft',
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(30),
        ]);

        Artisan::call('invitations:delete-expired-drafts');

        $this->assertDatabaseMissing('invitations', ['id' => $expiredDraft->id]);
        $this->assertDatabaseHas('invitations', ['id' => $recentDraft->id]);
        $this->assertDatabaseHas('invitations', ['id' => $republishDraft->id]);
        Storage::disk('public')->assertMissing('invitations/photos/draft-groom.jpg');
        Storage::disk('public')->assertMissing('invitations/photos/draft-bride.jpg');
        Storage::disk('public')->assertMissing('invitations/gallery/draft-one.jpg');
        Storage::disk('public')->assertMissing('invitations/musics/draft-song.mp3');
    }

    private function publishedInvitation(string $eventDate, array $overrides = []): Invitation
    {
        $template = InvitationTemplate::firstOrFail();

        return Invitation::create([
            'template_id' => $template->id,
            'slug' => 'retention-test-'.strtolower(fake()->bothify('??##')),
            'status' => 'published',
            'groom_full_name' => 'I Made Wira',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => $eventDate,
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Ubud, Bali',
            'music_type' => 'none',
            'published_at' => now()->subDays(100),
            ...$overrides,
        ]);
    }

    private function draftInvitation(array $overrides = []): Invitation
    {
        $template = InvitationTemplate::firstOrFail();

        $invitation = Invitation::create([
            'template_id' => $template->id,
            'status' => 'draft',
            'groom_full_name' => 'I Made Draft',
            'groom_nickname' => 'Draft',
            'bride_full_name' => 'Ni Putu Draft',
            'bride_nickname' => 'Draft',
            'music_type' => 'none',
            ...$overrides,
        ]);

        if ($overrides !== []) {
            $invitation->forceFill($overrides)->save();
        }

        return $invitation;
    }
}
