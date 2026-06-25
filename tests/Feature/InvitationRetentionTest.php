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
}
