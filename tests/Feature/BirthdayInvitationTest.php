<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BirthdayInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InvitationTemplateSeeder::class);
        Http::preventStrayRequests();
    }

    public function test_catalog_is_backwards_compatible_and_seeder_is_idempotent(): void
    {
        $ids = InvitationTemplate::pluck('id', 'slug')->all();
        $this->seed(InvitationTemplateSeeder::class);
        $this->assertSame($ids, InvitationTemplate::pluck('id', 'slug')->all());
        $this->getJson('/api/templates')->assertOk()->assertJsonCount(5, 'data');
        $this->getJson('/api/templates?invitation_type=birthday')->assertOk()
            ->assertJsonCount(3, 'data')->assertJsonPath('data.0.invitation_type', 'birthday');
        $this->getJson('/api/templates?invitation_type=unknown')->assertUnprocessable();

        foreach (['ceria-confetti', 'ruang-putih', 'bali-pradnyan'] as $slug) {
            $this->get('/preview/templates/'.$slug.'?to=Teman%20Kirana')->assertOk()
                ->assertSee('Buka Undangan')->assertSee('Teman Kirana')
                ->assertSee('Kado Digital')->assertSee('Lihat Simulasi Pembayaran')
                ->assertSee('birthday-celebration.svg')->assertDontSee('Wira &amp; Ayu', false);
        }
    }

    public function test_guest_can_preview_but_cannot_save_or_publish_without_login(): void
    {
        $this->postJson('/api/invitations/sync-local-draft', $this->payload())->assertUnauthorized();
        $this->postJson('/api/invitations/1/publish')->assertUnauthorized();
    }

    public function test_birthday_draft_sync_resume_publish_and_unique_links(): void
    {
        $this->actingAs(User::factory()->create());
        $payload = $this->payload();
        $payload['is_hidden_from_feed'] = false;
        $payload['feed_consent_at'] = now()->toISOString();
        $draft = $this->postJson('/api/invitations/sync-local-draft', $payload)->assertCreated()
            ->assertJsonPath('data.is_hidden_from_feed', true)
            ->assertJsonPath('data.celebrant_nickname', 'Kirana');
        $id = $draft->json('data.id');
        $this->assertNull(Invitation::findOrFail($id)->feed_consent_at);
        $this->getJson('/api/invitations/'.$id)->assertOk()
            ->assertJsonPath('data.celebrant_age', 7)
            ->assertJsonPath('data.template.invitation_type', 'birthday');

        $published = $this->postJson('/api/invitations/'.$id.'/publish')->assertOk()
            ->assertJsonPath('data.status', 'published')->assertJsonPath('data.is_hidden_from_feed', true);
        $slug = $published->json('data.slug');
        $this->assertStringStartsWith('ulang-tahun-kirana', $slug);
        $this->assertStringContainsString('perayaan ulang tahun Kirana', $published->json('share_text'));
        $this->get('/u/'.$slug.'?to=Komang')->assertOk()->assertSee('Komang')
            ->assertSee('Kirana')->assertSee('Buka Undangan')->assertDontSee('Wedding Gift')
            ->assertDontSee('hero-couple.jpg')->assertDontSee('Galeri cerita');
        $this->getJson('/api/moments')->assertJsonCount(0, 'data');

        $second = $this->postJson('/api/invitations', $this->payload())->assertCreated()->json('data.id');
        $secondSlug = $this->postJson('/api/invitations/'.$second.'/publish')->assertOk()->json('data.slug');
        $this->assertNotSame($slug, $secondSlug);
    }

    public function test_template_type_immutable_type_and_field_validation(): void
    {
        $this->actingAs(User::factory()->create());
        $payload = $this->payload();
        $this->postJson('/api/invitations', array_replace($payload, [
            'selected_template' => InvitationTemplate::where('slug', 'bali-classic')->value('id'),
        ]))->assertUnprocessable()->assertJsonValidationErrors('template_id');
        $this->postJson('/api/invitations', array_replace_recursive($payload, [
            'birthday_data' => ['celebrant_full_name' => '<script>alert(1)</script>', 'celebrant_nickname' => str_repeat('a', 19), 'celebrant_age' => -1],
            'event_data' => ['event_type' => 'Pawiwahan', 'event_date' => now()->subDay()->toDateString()],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['celebrant_full_name', 'celebrant_nickname', 'celebrant_age', 'event_type', 'event_date']);
        $id = $this->postJson('/api/invitations', $payload)->assertCreated()->json('data.id');
        $this->putJson('/api/invitations/'.$id, [
            'invitation_type' => 'wedding',
            'template_id' => InvitationTemplate::where('slug', 'bali-classic')->value('id'),
        ])->assertUnprocessable()->assertJsonValidationErrors('invitation_type');
        $this->assertSame('birthday', Invitation::findOrFail($id)->invitation_type);

        $this->putJson('/api/invitations/'.$id, [
            'template_id' => $this->templateId(),
            'birthday_data' => ['celebrant_age' => null, 'host_name' => null],
        ])->assertOk()->assertJsonPath('data.celebrant_age', null)->assertJsonPath('data.host_name', null);
    }

    public function test_incomplete_draft_publish_errors_use_birthday_labels(): void
    {
        $this->actingAs(User::factory()->create());
        $id = $this->postJson('/api/invitations', [
            'invitation_type' => 'birthday', 'template_id' => $this->templateId(),
        ])->assertCreated()->json('data.id');
        $response = $this->postJson('/api/invitations/'.$id.'/publish')->assertUnprocessable()
            ->assertJsonValidationErrors(['celebrant_full_name', 'celebrant_nickname', 'event_date']);
        $this->assertArrayNotHasKey('groom_full_name', $response->json('errors'));
    }

    public function test_owner_consent_is_required_for_feed_and_sensitive_fields_are_absent(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $id = $this->postJson('/api/invitations', $this->payload())->assertCreated()->json('data.id');
        $this->postJson('/api/invitations/'.$id.'/publish')->assertOk();
        $this->getJson('/api/moments/'.$id)->assertNotFound();
        $this->postJson('/api/moments/'.$id.'/comments', ['body' => 'Selamat ulang tahun'])->assertNotFound();
        $this->putJson('/api/invitations/'.$id.'/feed-visibility', ['is_hidden_from_feed' => false])
            ->assertUnprocessable()->assertJsonValidationErrors('privacy_acknowledged');

        $this->actingAs(User::factory()->create());
        $this->getJson('/api/invitations/'.$id)->assertNotFound();
        $this->postJson('/api/invitations/'.$id.'/publish')->assertNotFound();
        $this->putJson('/api/invitations/'.$id.'/feed-visibility', ['is_hidden_from_feed' => false, 'privacy_acknowledged' => true])->assertNotFound();

        $this->actingAs($owner);
        $this->putJson('/api/invitations/'.$id.'/feed-visibility', ['is_hidden_from_feed' => false, 'privacy_acknowledged' => true])->assertOk();
        $this->assertNotNull(Invitation::findOrFail($id)->feed_consent_at);
        $data = $this->getJson('/api/moments/'.$id)->assertOk()->assertJsonPath('data.names', 'Kirana')->json('data');
        foreach (['celebrant_age', 'celebrant_full_name', 'host_name', 'event_date', 'venue_name', 'venue_address', 'latitude', 'longitude', 'public_url'] as $field) {
            $this->assertArrayNotHasKey($field, $data);
        }
        $this->getJson('/api/moments')->assertJsonCount(1, 'data');
        $this->putJson('/api/invitations/'.$id.'/feed-visibility', ['is_hidden_from_feed' => true])->assertOk();
        $this->getJson('/api/moments/'.$id)->assertNotFound();
    }

    public function test_uploaded_photo_gallery_and_music_are_used_and_retained_on_resume(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $response = $this->post('/api/invitations', [
            ...$this->payload(),
            'celebrant_photo' => UploadedFile::fake()->image('birthday.jpg'),
            'gallery_photos_changed' => 1,
            'gallery_photos' => [UploadedFile::fake()->image('party.jpg')],
            'music_file' => UploadedFile::fake()->create('song.mp3', 50, 'audio/mpeg'),
        ])->assertCreated();
        $id = $response->json('data.id');
        $photo = $response->json('data.celebrant_photo');
        $gallery = $response->json('data.gallery_photos');
        $music = $response->json('data.music_file');
        foreach ([$photo, ...$gallery, $music] as $file) {
            Storage::disk('public')->assertExists($file);
        }
        $this->putJson('/api/invitations/'.$id, [
            'template_id' => $this->templateId(), 'music_type' => 'upload',
        ])->assertOk()->assertJsonPath('data.celebrant_photo', $photo)->assertJsonPath('data.gallery_photos', $gallery);
        $slug = $this->postJson('/api/invitations/'.$id.'/publish')->assertOk()->json('data.slug');
        $this->get('/u/'.$slug)->assertOk()->assertSee($photo, false)->assertSee($gallery[0], false)
            ->assertSee($music, false)->assertSee('data-audio-toggle', false)->assertDontSee('hero-couple.jpg');

        $this->post('/api/invitations/'.$id, [
            '_method' => 'PUT', 'template_id' => $this->templateId(), 'music_type' => 'upload',
            'celebrant_photo' => UploadedFile::fake()->image('replacement.jpg'),
        ])->assertOk();
        Storage::disk('public')->assertMissing($photo);
    }

    public function test_birthday_gift_uses_existing_xendit_flow_without_frontend_paid_status(): void
    {
        config(['services.xendit.payment_provider' => 'xendit', 'services.xendit.secret_key' => 'test-key', 'services.xendit.webhook_token' => 'birthday-test-token']);
        Http::fake(['api.xendit.co/v2/invoices' => Http::response([
            'id' => 'birthday-test-invoice', 'status' => 'PENDING', 'invoice_url' => 'https://checkout.xendit.co/test',
        ])]);
        $this->actingAs(User::factory()->create());
        $id = $this->postJson('/api/invitations', [
            ...$this->payload(),
            'gift_data' => ['is_active' => true, 'receiver_name' => 'Kirana', 'minimum_amount' => 10000, 'show_amount_public' => false, 'allow_message' => true],
        ])->assertCreated()->json('data.id');
        $slug = $this->postJson('/api/invitations/'.$id.'/publish')->assertOk()->json('data.slug');
        $this->get('/u/'.$slug)->assertOk()->assertSee('Kado Digital')->assertDontSee('Wedding Gift');
        $gift = $this->postJson('/api/public/invitations/'.$slug.'/wedding-gift/create', [
            'guest_name' => 'Komang', 'gift_amount' => 50000, 'transaction_status' => 'paid',
        ])->assertCreated()->assertJsonPath('data.service_fee', 2000)
            ->assertJsonPath('data.total_amount', 52000)->assertJsonPath('data.transaction_status', 'pending');
        Http::assertSent(fn ($request) => $request['description'] === 'Kado Digital Kirana' && $request['amount'] === 52000);
        $notification = [
            'id' => 'birthday-test-invoice', 'external_id' => $gift->json('data.order_id'),
            'status' => 'PAID', 'amount' => 52000,
        ];
        $this->postJson('/api/xendit/webhook', $notification)->assertForbidden();
        $this->withHeader('x-callback-token', 'birthday-test-token')->postJson('/api/xendit/webhook', $notification)
            ->assertOk()->assertJsonPath('transaction_status', 'paid');
        $this->withHeader('x-callback-token', 'birthday-test-token')->postJson('/api/xendit/webhook', $notification)->assertOk();
        $this->assertDatabaseCount('wedding_gift_fees', 1);
        $this->assertSame('Kado Digital dari Komang berhasil diterima.', Invitation::findOrFail($id)->socialNotifications()->sole()->data['message']);
    }

    public function test_birthday_media_cleanup_preserves_template_demo_assets(): void
    {
        Storage::fake('public');
        $photo = 'invitations/photos/birthday-expired.jpg';
        Storage::disk('public')->put($photo, 'test');
        $invitation = Invitation::create([
            'template_id' => $this->templateId(), 'invitation_type' => 'birthday', 'status' => 'archived',
            'slug' => 'birthday-expired', 'event_date' => now()->subDays(100), 'archived_at' => now()->subDays(60),
            'celebrant_nickname' => 'Kirana', 'celebrant_photo' => $photo,
        ]);
        Artisan::call('invitations:cleanup-media');
        Storage::disk('public')->assertMissing($photo);
        $this->assertNull($invitation->fresh()->celebrant_photo);
        $this->assertFileExists(public_path('images/birthday-celebration.svg'));
        $this->get('/u/birthday-expired')->assertOk()->assertSee('Kirana')->assertSee('Undangan Diarsipkan');
    }

    private function templateId(): int
    {
        return InvitationTemplate::where('slug', 'ceria-confetti')->value('id');
    }

    private function payload(): array
    {
        return [
            'invitation_type' => 'birthday', 'selected_template' => $this->templateId(),
            'birthday_data' => ['celebrant_full_name' => 'Ni Putu Kirana', 'celebrant_nickname' => 'Kirana', 'celebrant_age' => 7, 'host_name' => 'Papa & Mama'],
            'event_data' => ['event_type' => 'Ulang Tahun', 'event_date' => now()->addMonth()->toDateString(), 'start_time' => '15:00', 'end_time' => '17:00', 'venue_name' => 'Taman Keluarga', 'venue_address' => 'Alamat hanya untuk tamu', 'event_title' => 'Hari Bahagia Kirana'],
            'music_data' => ['music_type' => 'none'],
        ];
    }
}
