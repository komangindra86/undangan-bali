<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChildCeremonyInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InvitationTemplateSeeder::class);
        Http::preventStrayRequests();
    }

    public function test_catalog_adds_abulan_pitung_dina_without_changing_existing_types(): void
    {
        $this->getJson('/api/templates')->assertOk()->assertJsonCount(5, 'data');
        $this->getJson('/api/templates?invitation_type=megedong')->assertOk()->assertJsonCount(3, 'data');
        $this->getJson('/api/templates?invitation_type=pitung_dina')->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.invitation_type', 'pitung_dina');

        foreach (['rare-kencana', 'sekar-jepun', 'langit-kumara'] as $slug) {
            $this->get('/preview/templates/'.$slug.'?to=Bli%20Komang')->assertOk()
                ->assertSee('Buka Undangan')->assertSee('Bli Komang')
                ->assertSee('Om Swastyastu')->assertSee('Upacara Abulan Pitung Dina')->assertSee('42 Hari')
                ->assertSee('Putra Wira &amp; Ayu', false)
                ->assertSee('I Putu Bagus Aditya')->assertSee('Anak pertama')
                ->assertSee('Tanda Kasih')->assertSee('Om Shanti, Shanti, Shanti Om')
                ->assertDontSee('The Wedding of');
        }
    }

    public function test_draft_with_only_parents_publishes_as_buah_hati(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $id = $this->postJson('/api/invitations/sync-local-draft', $this->payload([]))->assertCreated()
            ->assertJsonPath('data.invitation_type', 'pitung_dina')
            ->assertJsonPath('data.child_full_name', null)
            ->json('data.id');

        $published = $this->postJson("/api/invitations/{$id}/publish")->assertOk()
            ->assertJsonPath('data.slug', 'abulan-pitung-dina-buah-hati-wira-ayu');
        $this->assertStringContainsString('upacara abulan pitung dina (42 hari) buah hati kami', $published->json('share_text'));

        $this->get('/u/abulan-pitung-dina-buah-hati-wira-ayu')->assertOk()
            ->assertSee('Buah Hati Wira &amp; Ayu', false)
            ->assertSee('I Made Wira Adnyana')->assertSee('Ni Putu Ayu Lestari')
            ->assertSee('Buah Hati dari pasangan')
            ->assertSee('upacara-anak.css')
            ->assertDontSee('Lahir');
    }

    public function test_full_child_details_are_saved_shown_and_listed_in_the_feed(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(), 'sanctum');
        $birthDate = now()->subDays(35)->toDateString();

        $id = $this->post('/api/invitations/sync-local-draft', $this->payload([
            'child_full_name' => 'Ni Luh Kirana Dewi',
            'child_nickname' => 'Kirana',
            'child_gender' => 'putri',
            'child_order' => 'Anak kedua',
            'child_birth_date' => $birthDate,
        ]) + ['child_photo' => UploadedFile::fake()->image('bayi.jpg')], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.child_gender', 'putri')
            ->assertJsonPath('data.child_birth_date', $birthDate)
            ->json('data.id');

        $invitation = Invitation::findOrFail($id);
        Storage::disk('public')->assertExists($invitation->child_photo);
        $this->assertSame('Putri Wira & Ayu', $invitation->display_name);
        $this->assertSame('Tanda Kasih', $invitation->gift_label);

        $this->postJson("/api/invitations/{$id}/publish")->assertOk()
            ->assertJsonPath('data.slug', 'abulan-pitung-dina-putri-wira-ayu');
        $this->get('/u/abulan-pitung-dina-putri-wira-ayu')->assertOk()
            ->assertSee('Ni Luh Kirana Dewi')
            ->assertSee('Putri · Anak kedua dari pasangan')
            ->assertSee('Lahir');

        $this->getJson('/api/moments')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.invitation_type', 'pitung_dina')
            ->assertJsonPath('data.0.names', 'Putri Wira & Ayu')
            ->assertJsonCount(1, 'data.0.photo_urls');
    }

    public function test_parents_are_required_and_child_fields_are_validated(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $payload = $this->payload([]);
        unset($payload['bride_data']);
        $id = $this->postJson('/api/invitations/sync-local-draft', $payload)->assertCreated()->json('data.id');

        $this->postJson("/api/invitations/{$id}/publish")->assertUnprocessable()
            ->assertJsonValidationErrors(['bride_full_name', 'bride_nickname'])
            ->assertJsonPath('errors.bride_full_name.0', fn ($message) => str_contains($message, 'nama lengkap ibu'));

        $this->postJson('/api/invitations/sync-local-draft', $this->payload(['child_gender' => 'lainnya']))
            ->assertUnprocessable()->assertJsonValidationErrors('child_gender');
        $this->postJson('/api/invitations/sync-local-draft', $this->payload(['child_birth_date' => now()->addDay()->toDateString()]))
            ->assertUnprocessable()->assertJsonValidationErrors('child_birth_date');

        $wrongEvent = $this->payload([]);
        $wrongEvent['event_data']['event_type'] = 'Megedong-gedongan';
        $this->postJson('/api/invitations/sync-local-draft', $wrongEvent)->assertUnprocessable()
            ->assertJsonValidationErrors('event_type');
    }

    public function test_child_fields_are_ignored_by_other_invitation_types(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $weddingTemplate = InvitationTemplate::where('invitation_type', 'wedding')->firstOrFail();

        $wedding = $this->postJson('/api/invitations', [
            'template_id' => $weddingTemplate->id,
            'groom_nickname' => 'Wira',
            'bride_nickname' => 'Ayu',
            'child_full_name' => 'Tidak Boleh Tersimpan',
            'child_gender' => 'putra',
        ])->assertCreated();

        $saved = Invitation::findOrFail($wedding->json('data.id'));
        $this->assertNull($saved->child_full_name);
        $this->assertNull($saved->child_gender);
        $this->assertSame('Wira & Ayu', $saved->display_name);
    }

    public function test_child_photo_is_removed_with_the_draft(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(), 'sanctum');
        $id = $this->post('/api/invitations/sync-local-draft', $this->payload([]) + [
            'child_photo' => UploadedFile::fake()->image('bayi.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated()->json('data.id');
        $path = Invitation::findOrFail($id)->child_photo;
        Storage::disk('public')->assertExists($path);

        $this->deleteJson("/api/invitations/{$id}")->assertOk();
        Storage::disk('public')->assertMissing($path);
    }

    private function payload(array $child): array
    {
        return [
            'invitation_type' => 'pitung_dina',
            'selected_template' => InvitationTemplate::where('slug', 'rare-kencana')->value('id'),
            'groom_data' => ['groom_full_name' => 'I Made Wira Adnyana', 'groom_nickname' => 'Wira'],
            'bride_data' => ['bride_full_name' => 'Ni Putu Ayu Lestari', 'bride_nickname' => 'Ayu'],
            'child_data' => $child,
            'event_data' => [
                'event_type' => 'Abulan Pitung Dina',
                'event_date' => now()->addDays(10)->toDateString(),
                'start_time' => '09:00',
                'venue_name' => 'Kediaman Keluarga I Made Wira',
                'venue_address' => 'Banjar Tegal, Ubud, Gianyar, Bali',
            ],
            'music_data' => ['music_type' => 'none'],
        ];
    }
}
