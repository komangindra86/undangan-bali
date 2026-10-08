<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MegedongInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InvitationTemplateSeeder::class);
        Http::preventStrayRequests();
    }

    public function test_catalog_adds_megedong_without_changing_existing_types(): void
    {
        $this->getJson('/api/templates')->assertOk()->assertJsonCount(5, 'data');
        $this->getJson('/api/templates?invitation_type=birthday')->assertOk()->assertJsonCount(3, 'data');
        $this->getJson('/api/templates?invitation_type=megedong')->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.invitation_type', 'megedong');

        foreach (['garbha-kencana', 'padma-sari', 'tirta-hening'] as $slug) {
            $this->get('/preview/templates/'.$slug.'?to=Bli%20Komang')->assertOk()
                ->assertSee('Buka Undangan')->assertSee('Bli Komang')
                ->assertSee('Om Swastyastu')->assertSee('Upacara Megedong-gedongan')
                ->assertSee('Ayu &amp; Wira', false)
                ->assertSee('Calon Ibu')->assertSee('Usia kandungan')
                ->assertSee('Tanda Kasih')->assertSee('Om Shanti, Shanti, Shanti Om')
                ->assertDontSee('The Wedding of');
        }
    }

    public function test_megedong_draft_publishes_with_its_own_slug_wording_and_feed_entry(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner, 'sanctum');

        $draft = $this->postJson('/api/invitations/sync-local-draft', $this->payload())->assertCreated()
            ->assertJsonPath('data.invitation_type', 'megedong')
            ->assertJsonPath('data.bride_nickname', 'Ayu')
            ->assertJsonPath('data.pregnancy_age', '7 bulan')
            ->assertJsonPath('data.child_order', 'Anak pertama');
        $id = $draft->json('data.id');
        $this->assertFalse(Invitation::findOrFail($id)->is_hidden_from_feed);

        $published = $this->postJson("/api/invitations/{$id}/publish")->assertOk()
            ->assertJsonPath('data.slug', 'megedong-gedongan-ayu-wira');
        $this->assertStringContainsString('upacara megedong-gedongan kami', $published->json('share_text'));

        $this->get('/u/megedong-gedongan-ayu-wira')->assertOk()
            ->assertSee('Ni Putu Ayu Lestari')->assertSee('I Made Wira Adnyana')
            ->assertSee('7 bulan')->assertSee('Anak pertama')
            ->assertSee('megedong-invitation.css');

        $this->getJson('/api/moments')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.invitation_type', 'megedong')
            ->assertJsonPath('data.0.names', 'Ayu & Wira');
    }

    public function test_megedong_requires_both_parents_and_its_own_event_type_to_publish(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $payload = $this->payload();
        unset($payload['groom_data']);
        $id = $this->postJson('/api/invitations/sync-local-draft', $payload)->assertCreated()->json('data.id');

        $this->postJson("/api/invitations/{$id}/publish")->assertUnprocessable()
            ->assertJsonValidationErrors(['groom_full_name', 'groom_nickname'])
            ->assertJsonPath('errors.groom_full_name.0', fn ($message) => str_contains($message, 'calon ayah'));

        $wrongEvent = $this->payload();
        $wrongEvent['event_data']['event_type'] = 'Pawiwahan';
        $this->postJson('/api/invitations/sync-local-draft', $wrongEvent)->assertUnprocessable()
            ->assertJsonValidationErrors('event_type');
    }

    public function test_type_and_template_cannot_be_mixed_and_other_types_ignore_megedong_fields(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $weddingTemplate = InvitationTemplate::where('invitation_type', 'wedding')->firstOrFail();
        $mixed = $this->payload();
        $mixed['selected_template'] = $weddingTemplate->id;
        $this->postJson('/api/invitations/sync-local-draft', $mixed)->assertUnprocessable()
            ->assertJsonValidationErrors('template_id');

        $wedding = $this->postJson('/api/invitations', [
            'template_id' => $weddingTemplate->id,
            'groom_nickname' => 'Wira',
            'bride_nickname' => 'Ayu',
            'pregnancy_age' => '7 bulan',
        ])->assertCreated();
        $this->assertNull(Invitation::findOrFail($wedding->json('data.id'))->pregnancy_age);

        $megedongId = $this->postJson('/api/invitations/sync-local-draft', $this->payload())->json('data.id');
        $switch = $this->payload();
        $switch['invitation_type'] = 'wedding';
        $this->putJson("/api/invitations/{$megedongId}", $switch)->assertUnprocessable()
            ->assertJsonValidationErrors('invitation_type');
    }

    public function test_gift_uses_tanda_kasih_label(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $payload = $this->payload();
        $payload['gift_data'] = [
            'is_active' => true,
            'receiver_name' => 'Ayu & Wira',
            'minimum_amount' => 10000,
            'show_amount_public' => false,
            'allow_message' => true,
        ];
        $id = $this->postJson('/api/invitations/sync-local-draft', $payload)->assertCreated()->json('data.id');
        $this->postJson("/api/invitations/{$id}/publish")->assertOk();

        $invitation = Invitation::findOrFail($id);
        $this->assertSame('Tanda Kasih', $invitation->gift_label);
        $this->get('/u/'.$invitation->slug)->assertOk()
            ->assertSee('Tanda Kasih')
            ->assertSee('Doa untuk ibu dan calon buah hati')
            ->assertDontSee('Wedding Gift');
    }

    private function payload(): array
    {
        return [
            'invitation_type' => 'megedong',
            'selected_template' => InvitationTemplate::where('slug', 'garbha-kencana')->value('id'),
            'bride_data' => ['bride_full_name' => 'Ni Putu Ayu Lestari', 'bride_nickname' => 'Ayu'],
            'groom_data' => ['groom_full_name' => 'I Made Wira Adnyana', 'groom_nickname' => 'Wira'],
            'megedong_data' => ['pregnancy_age' => '7 bulan', 'child_order' => 'Anak pertama'],
            'event_data' => [
                'event_type' => 'Megedong-gedongan',
                'event_date' => now()->addMonth()->toDateString(),
                'start_time' => '09:00',
                'venue_name' => 'Kediaman Keluarga I Made Wira',
                'venue_address' => 'Banjar Tegal, Ubud, Gianyar, Bali',
            ],
            'location_data' => ['google_maps_url' => 'https://maps.google.com/?q=-8.5069,115.2625'],
            'music_data' => ['music_type' => 'none'],
        ];
    }
}
