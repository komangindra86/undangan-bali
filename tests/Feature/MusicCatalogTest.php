<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Music;
use App\Models\User;
use Database\Seeders\InvitationTemplateSeeder;
use Database\Seeders\PixabayMusicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MusicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_pixabay_seeder_deletes_obsolete_catalog_files_and_records(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('musics/old.wav', 'old audio');
        $old = Music::create(['title' => 'Musik lama', 'file_path' => 'musics/old.wav', 'is_active' => true]);
        $this->seed(PixabayMusicSeeder::class);

        $this->assertNull($old->fresh());
        Storage::disk('public')->assertMissing('musics/old.wav');
        $this->assertSame(20, Music::where('is_active', true)->count());
        $this->assertSame(20, Music::where('catalog_key', 'like', 'pixabay/%')->count());

        $track = Music::where('catalog_key', 'pixabay/162472')->firstOrFail();
        $track->update(['is_active' => false]);
        $this->seed(PixabayMusicSeeder::class);
        $this->assertFalse($track->fresh()->is_active);
        $this->assertDatabaseCount('musics', 20);
    }

    public function test_api_returns_only_reviewed_active_tracks_and_never_exposes_evidence_hashes(): void
    {
        $this->seed(PixabayMusicSeeder::class);
        Music::where('catalog_key', 'pixabay/559487')->update(['license_verified_at' => null]);

        $response = $this->getJson('/api/musics')->assertOk()->assertJsonCount(19, 'data');
        $track = collect($response->json('data'))->firstWhere('catalog_key', 'pixabay/162472');

        $this->assertSame(['pernikahan'], $track['categories']);
        $this->assertSame(asset('storage/musics/pixabay/previews/162472.mp3'), $track['preview_url']);
        $this->assertSame('PIXABAY-CONTENT-LICENSE', $track['license_code']);
        $this->assertStringContainsString('Pixabay Content ID 162472', $track['attribution']);
        $this->assertArrayNotHasKey('license_evidence_path', $track);
        $this->assertArrayNotHasKey('audio_sha256', $track);
        $this->assertArrayNotHasKey('preview_sha256', $track);
    }

    public function test_manifest_and_generated_audio_are_complete_unique_and_lightweight(): void
    {
        $manifest = json_decode(file_get_contents(resource_path('music/pixabay-catalog.json')), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(20, $manifest);
        $this->assertCount(20, array_unique(array_column($manifest, 'asset_id')));
        $wedding = $birthday = 0;
        foreach ($manifest as $track) {
            $this->assertNotEmpty($track['title']);
            $this->assertNotEmpty($track['artist']);
            $this->assertNotEmpty($track['original_filename']);
            $this->assertSame([], array_values(array_diff($track['categories'], ['pernikahan', 'ulang-tahun'])));
            $wedding += (int) in_array('pernikahan', $track['categories'], true);
            $birthday += (int) in_array('ulang-tahun', $track['categories'], true);

            $full = storage_path('app/public/musics/pixabay/'.$track['asset_id'].'.mp3');
            $preview = storage_path('app/public/musics/pixabay/previews/'.$track['asset_id'].'.mp3');
            $this->assertFileExists($full);
            $this->assertFileExists($preview);
            $this->assertGreaterThan(20_000, filesize($full));
            $this->assertLessThan(3_100_000, filesize($full));
            $this->assertGreaterThan(200_000, filesize($preview));
            $this->assertLessThan(400_000, filesize($preview));
        }
        $this->assertSame(11, $wedding);
        $this->assertSame(12, $birthday);
    }

    public function test_every_template_streams_full_music_lazily_and_displays_credit(): void
    {
        $this->seed(InvitationTemplateSeeder::class);
        $this->seed(PixabayMusicSeeder::class);
        $track = Music::where('catalog_key', 'pixabay/307716')->firstOrFail();

        foreach (InvitationTemplate::all() as $template) {
            $invitation = $this->invitation($template, $track);
            $this->get('/u/'.$invitation->slug)->assertOk()
                ->assertSee('data-music-credit', false)
                ->assertSee($track->attribution)
                ->assertSee($track->source_url, false)
                ->assertSee($track->license_url, false)
                ->assertDontSee($track->modifications)
                ->assertSee('storage/'.$track->file_path, false)
                ->assertSee('preload="none"', false)
                ->assertDontSee('storage/'.$track->preview_file_path, false);
        }
    }

    public function test_no_music_or_upload_does_not_show_an_unrelated_catalog_credit(): void
    {
        $this->seed(InvitationTemplateSeeder::class);
        $this->seed(PixabayMusicSeeder::class);
        $track = Music::where('catalog_key', 'pixabay/162472')->firstOrFail();
        $invitation = $this->invitation(InvitationTemplate::first(), $track);

        foreach (['none', 'upload'] as $type) {
            $invitation->update(['music_type' => $type, 'music_file' => $type === 'upload' ? 'invitations/musics/own.mp3' : null]);
            $this->get('/u/'.$invitation->slug)->assertOk()->assertDontSee('data-music-credit', false);
        }
    }

    public function test_credit_is_escaped_and_cannot_inject_html(): void
    {
        $this->seed(InvitationTemplateSeeder::class);
        $track = $this->track(['attribution' => '<script>alert(1)</script> Pixabay']);
        $invitation = $this->invitation(InvitationTemplate::first(), $track);

        $this->get('/u/'.$invitation->slug)->assertOk()->assertSee($track->attribution)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_retention_cleanup_does_not_delete_shared_catalog_music_or_evidence(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->seed(InvitationTemplateSeeder::class);
        $track = $this->track();
        Storage::disk('public')->put($track->file_path, 'shared audio');
        Storage::disk('public')->put($track->preview_file_path, 'shared preview');
        Storage::disk('local')->put($track->license_evidence_path, '{}');
        $invitation = $this->invitation(InvitationTemplate::first(), $track);
        $invitation->update(['status' => 'archived', 'event_date' => today()->subDays(100)]);

        $this->artisan('invitations:cleanup-media')->assertSuccessful();

        Storage::disk('public')->assertExists($track->file_path);
        Storage::disk('public')->assertExists($track->preview_file_path);
        Storage::disk('local')->assertExists($track->license_evidence_path);
    }

    public function test_draft_sync_uses_music_id_and_cannot_change_catalog_license(): void
    {
        $this->seed(InvitationTemplateSeeder::class);
        $track = $this->track();
        $data = [
            'selected_template' => InvitationTemplate::where('invitation_type', 'wedding')->first()->id,
            'music_data' => ['music_type' => 'default', 'music_id' => $track->id, 'attribution' => 'Diubah pengguna'],
        ];

        $this->postJson('/api/invitations/sync-local-draft', $data)->assertUnauthorized();
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/invitations/sync-local-draft', $data)->assertCreated()
            ->assertJsonPath('data.music_id', $track->id)
            ->assertJsonPath('data.music.attribution', $track->attribution);
        $this->assertSame($track->attribution, $track->fresh()->attribution);
    }

    private function track(array $overrides = []): Music
    {
        return Music::create(array_merge([
            'catalog_key' => 'pixabay/162472',
            'title' => 'Wedding Piano',
            'artist' => 'Paul Yudin',
            'artist_url' => 'https://pixabay.com/music/search/wedding%20piano/',
            'categories' => ['pernikahan'],
            'source_url' => 'https://pixabay.com/music/search/wedding%20piano/',
            'license_code' => 'PIXABAY-CONTENT-LICENSE',
            'license_url' => 'https://pixabay.com/service/license-summary/',
            'attribution' => 'Wedding Piano oleh Paul Yudin (Pixabay Content ID 162472).',
            'modifications' => 'Audio dikompresi. Preview 30 detik.',
            'file_path' => 'musics/pixabay/162472.mp3',
            'preview_file_path' => 'musics/pixabay/previews/162472.mp3',
            'file_bytes' => 120000,
            'license_verified_at' => now(),
            'license_evidence_path' => 'music-licenses/pixabay/162472/evidence.json',
            'is_active' => true,
        ], $overrides));
    }

    private function invitation(InvitationTemplate $template, Music $music): Invitation
    {
        return Invitation::create([
            'user_id' => User::factory()->create()->id,
            'template_id' => $template->id,
            'invitation_type' => $template->invitation_type,
            'slug' => 'music-'.$template->slug,
            'status' => 'published',
            'music_type' => 'default',
            'music_id' => $music->id,
            'groom_full_name' => 'I Made Wira',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu',
            'bride_nickname' => 'Ayu',
            'celebrant_full_name' => 'Kirana',
            'celebrant_nickname' => 'Kirana',
            'event_type' => $template->invitation_type === 'birthday' ? 'Ulang Tahun' : 'Pawiwahan',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '10:00',
            'venue_name' => 'Ubud',
            'venue_address' => 'Bali',
            'published_at' => now(),
        ]);
    }
}
