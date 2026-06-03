<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\InvitationView;
use App\Models\Music;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvitationPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_catalog_has_bali_designs_with_dummy_previews(): void
    {
        $this->seed();

        $this->getJson('/api/templates')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonFragment(['slug' => 'bali-classic'])
            ->assertJsonFragment(['slug' => 'pura-sunset'])
            ->assertJsonFragment(['slug' => 'ubud-garden'])
            ->assertJsonFragment(['slug' => 'royal-kamasan']);

        $this->get('/preview/templates/bali-classic')
            ->assertOk()
            ->assertSee('Preview dummy')
            ->assertSee('Galeri Bahagia')
            ->assertSee('Wedding Gift')
            ->assertSee('Lihat Simulasi QRIS');

        $this->get('/preview/templates/pura-sunset')
            ->assertOk()
            ->assertSee('Preview dummy: Pura Sunset')
            ->assertSee('Momen Bahagia')
            ->assertSee('Rg Veda X.85.42')
            ->assertSee('Wedding Gift')
            ->assertSee('Lihat Simulasi QRIS');

        $this->get('/preview/templates/ubud-garden')
            ->assertOk()
            ->assertSee('Preview dummy: Ubud Garden')
            ->assertSee('Tema terang editorial')
            ->assertSee('Galeri Bahagia')
            ->assertSee('Wedding Gift')
            ->assertSee('Lihat Simulasi QRIS');
    }

    public function test_local_draft_can_be_synced_after_register_and_published(): void
    {
        $this->seed();
        $template = InvitationTemplate::where('slug', 'bali-classic')->firstOrFail();

        $register = $this->postJson('/api/register', [
            'name' => 'Komang',
            'email' => 'komang@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $token = $register->json('token');

        $draft = $this->withToken($token)->postJson('/api/invitations/sync-local-draft', [
            'selected_template' => $template->id,
            'groom_data' => [
                'groom_full_name' => 'I Made Wira',
                'groom_nickname' => 'Wira',
                'groom_father_name' => 'I Ketut Darma',
                'groom_mother_name' => 'Ni Luh Sari',
            ],
            'bride_data' => [
                'bride_full_name' => 'Ni Putu Ayu',
                'bride_nickname' => 'Ayu',
            ],
            'event_data' => [
                'event_type' => 'Pawiwahan',
                'event_date' => '2026-08-18',
                'start_time' => '10:00',
                'end_time' => '13:00',
                'venue_name' => 'Bale Banjar Ubud',
                'venue_address' => 'Jalan Raya Ubud, Gianyar, Bali',
            ],
            'location_data' => [
                'google_maps_url' => 'https://maps.google.com/?q=-8.5069,115.2625',
                'latitude' => -8.5069,
                'longitude' => 115.2625,
            ],
            'music_data' => ['music_type' => 'none'],
        ])->assertCreated();

        $invitationId = $draft->json('data.id');

        $publish = $this->withToken($token)
            ->postJson("/api/invitations/{$invitationId}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', 'published');

        $slug = $publish->json('data.slug');

        $this->get("/u/{$slug}")
            ->assertOk()
            ->assertSee('Wira')
            ->assertSee('Ayu');

        $this->assertDatabaseHas('invitations', [
            'id' => $invitationId,
            'slug' => $slug,
            'status' => 'published',
        ]);
        $this->assertSame(1, InvitationView::count());
    }

    public function test_incomplete_draft_cannot_be_published(): void
    {
        $this->seed();
        $template = InvitationTemplate::firstOrFail();

        $register = $this->postJson('/api/register', [
            'name' => 'Ayu',
            'email' => 'ayu@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $draft = $this->withToken($register->json('token'))->postJson('/api/invitations', [
            'template_id' => $template->id,
            'groom_nickname' => 'Wira',
        ])->assertCreated();

        $this->withToken($register->json('token'))
            ->postJson('/api/invitations/'.$draft->json('data.id').'/publish')
            ->assertUnprocessable();
    }

    public function test_user_uploaded_photos_and_gallery_are_used_on_published_invitation(): void
    {
        Storage::fake('public');
        $this->seed();
        $template = InvitationTemplate::where('slug', 'bali-classic')->firstOrFail();

        $register = $this->postJson('/api/register', [
            'name' => 'Komang',
            'email' => 'photos@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $draft = $this->withToken($register->json('token'))->post('/api/invitations/sync-local-draft', [
            'template_id' => $template->id,
            'groom_full_name' => 'I Made Wira',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => '2026-08-18',
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Ubud, Bali',
            'groom_photo' => UploadedFile::fake()->image('groom.jpg', 800, 1000),
            'bride_photo' => UploadedFile::fake()->image('bride.jpg', 800, 1000),
            'gallery_photos_changed' => true,
            'gallery_photos' => [
                UploadedFile::fake()->image('gallery-1.jpg', 1200, 800),
                UploadedFile::fake()->image('gallery-2.jpg', 1200, 800),
            ],
        ])->assertCreated();

        $invitation = Invitation::findOrFail($draft->json('data.id'));
        $this->assertCount(2, $invitation->gallery_photos);
        Storage::disk('public')->assertExists($invitation->groom_photo);
        Storage::disk('public')->assertExists($invitation->bride_photo);
        Storage::disk('public')->assertExists($invitation->gallery_photos[0]);

        $publish = $this->withToken($register->json('token'))
            ->postJson("/api/invitations/{$invitation->id}/publish")
            ->assertOk();

        $this->get('/u/'.$publish->json('data.slug'))
            ->assertOk()
            ->assertSee(Storage::url($invitation->groom_photo))
            ->assertSee(Storage::url($invitation->gallery_photos[0]))
            ->assertDontSee('templates/bali-preview/hero-couple.jpg');
    }

    public function test_mobile_form_boolean_strings_are_accepted_when_syncing_draft(): void
    {
        $this->seed();
        $template = InvitationTemplate::where('slug', 'bali-classic')->firstOrFail();

        $register = $this->postJson('/api/register', [
            'name' => 'Boolean Mobile',
            'email' => 'boolean-mobile@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $this->withToken($register->json('token'))->post('/api/invitations/sync-local-draft', [
            'selected_template' => $template->id,
            'groom_data' => [
                'groom_full_name' => 'I Made Wira',
                'groom_nickname' => 'Wira',
            ],
            'bride_data' => [
                'bride_full_name' => 'Ni Putu Ayu',
                'bride_nickname' => 'Ayu',
            ],
            'event_data' => [
                'event_type' => 'Pawiwahan',
                'event_date' => '2026-08-18',
                'start_time' => '10:00',
                'venue_name' => 'Bale Banjar',
                'venue_address' => 'Ubud, Bali',
            ],
            'music_data' => ['music_type' => 'none'],
            'gift_data' => [
                'is_active' => 'false',
                'receiver_name' => '',
                'receiver_note' => '',
                'minimum_amount' => '10000',
                'show_amount_public' => 'false',
                'allow_message' => 'true',
            ],
        ])->assertCreated()
            ->assertJsonPath('data.gift_setting.is_active', false)
            ->assertJsonPath('data.gift_setting.allow_message', true);
    }

    public function test_editing_a_published_invitation_returns_it_to_draft_until_republished(): void
    {
        $this->seed();
        $template = InvitationTemplate::firstOrFail();
        $register = $this->postJson('/api/register', [
            'name' => 'Wira',
            'email' => 'wira@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();
        $token = $register->json('token');

        $draft = $this->withToken($token)->postJson('/api/invitations', [
            'template_id' => $template->id,
            'groom_full_name' => 'I Made Wira',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => '2026-08-18',
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Ubud, Bali',
        ])->assertCreated();

        $id = $draft->json('data.id');
        $published = $this->withToken($token)->postJson("/api/invitations/{$id}/publish")->assertOk();
        $slug = $published->json('data.slug');

        $this->withToken($token)->putJson("/api/invitations/{$id}", [
            'template_id' => $template->id,
            'groom_nickname' => 'Wira Baru',
        ])->assertOk()->assertJsonPath('data.status', 'draft');

        $this->get("/u/{$slug}")->assertNotFound();
    }

    public function test_published_invitation_with_default_music_has_manual_audio_player(): void
    {
        $this->seed();
        $template = InvitationTemplate::where('slug', 'pura-sunset')->firstOrFail();
        $music = Music::where('title', 'Bali Romantis')->firstOrFail();
        $register = $this->postJson('/api/register', [
            'name' => 'Audio',
            'email' => 'audio@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $draft = $this->withToken($register->json('token'))->postJson('/api/invitations', [
            'template_id' => $template->id,
            'music_id' => $music->id,
            'music_type' => 'default',
            'groom_full_name' => 'I Made Wira',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => '2026-08-18',
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Ubud, Bali',
        ])->assertCreated();

        $published = $this->withToken($register->json('token'))
            ->postJson('/api/invitations/'.$draft->json('data.id').'/publish')
            ->assertOk();

        $this->get('/u/'.$published->json('data.slug'))
            ->assertOk()
            ->assertSee('data-audio-toggle', false)
            ->assertSee('storage/musics/bali-romantis.wav', false);
    }

    public function test_uploaded_music_is_used_and_removed_when_user_selects_no_music(): void
    {
        Storage::fake('public');
        $this->seed();
        $template = InvitationTemplate::firstOrFail();
        $register = $this->postJson('/api/register', [
            'name' => 'Audio Sendiri',
            'email' => 'own-audio@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();
        $token = $register->json('token');

        $draft = $this->withToken($token)->post('/api/invitations', [
            'template_id' => $template->id,
            'music_type' => 'upload',
            'music_file' => UploadedFile::fake()->create('lagu-kami.mp3', 500, 'audio/mpeg'),
            'groom_full_name' => 'I Made Wira',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => '2026-08-18',
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Ubud, Bali',
        ])->assertCreated();

        $invitation = Invitation::findOrFail($draft->json('data.id'));
        Storage::disk('public')->assertExists($invitation->music_file);

        $published = $this->withToken($token)
            ->postJson("/api/invitations/{$invitation->id}/publish")
            ->assertOk();

        $this->get('/u/'.$published->json('data.slug'))
            ->assertOk()
            ->assertSee(Storage::url($invitation->music_file))
            ->assertSee('data-audio-toggle', false);

        $oldMusicPath = $invitation->music_file;
        $this->withToken($token)->putJson("/api/invitations/{$invitation->id}", [
            'template_id' => $template->id,
            'music_type' => 'none',
        ])->assertOk()->assertJsonPath('data.music_file', null);

        Storage::disk('public')->assertMissing($oldMusicPath);
    }
}
