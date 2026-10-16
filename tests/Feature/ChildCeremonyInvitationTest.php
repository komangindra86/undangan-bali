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
        $this->getJson('/api/templates')->assertOk()->assertJsonCount(8, 'data');
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
            ->assertSee('upacara-bali.css')
            ->assertSee('penyucian bagi ibu dan buah hati')->assertSee('orn-emblem')
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

    public function test_tigang_sasih_offers_six_templates_including_the_shared_designs(): void
    {
        $this->getJson('/api/templates?invitation_type=pitung_dina')->assertOk()->assertJsonCount(3, 'data');
        $response = $this->getJson('/api/templates?invitation_type=tigang_sasih')->assertOk()->assertJsonCount(6, 'data');
        $this->assertSame(
            ['Nyambutin Kencana', 'Tunjung Petak', 'Surya Alit', 'Rare Kencana', 'Sekar Jepun', 'Langit Kumara'],
            array_column($response->json('data'), 'name')
        );

        foreach (['nyambutin-kencana', 'tunjung-petak', 'surya-alit', 'tigang-sasih-rare-kencana', 'tigang-sasih-sekar-jepun', 'tigang-sasih-langit-kumara'] as $slug) {
            $this->get('/preview/templates/'.$slug)->assertOk()
                ->assertSee('Upacara Tigang Sasih')->assertSee('3 Bulanan')
                ->assertSee('Putra dari Wira &amp; Ayu', false)
                ->assertSee('I Putu Bagus Aditya')
                ->assertDontSee('Abulan Pitung Dina');
        }

        // The shared designs keep serving abulan pitung dina under their original slugs.
        $this->get('/preview/templates/rare-kencana')->assertOk()->assertSee('Upacara Abulan Pitung Dina');
    }

    public function test_tigang_sasih_is_titled_by_the_child_name(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $id = $this->postJson('/api/invitations/sync-local-draft', $this->payload([
            'child_full_name' => 'Ni Luh Kirana Dewi',
            'child_nickname' => 'Kirana',
            'child_gender' => 'putri',
        ], 'tigang_sasih'))->assertCreated()->json('data.id');

        $published = $this->postJson("/api/invitations/{$id}/publish")->assertOk()
            ->assertJsonPath('data.slug', 'tigang-sasih-kirana');
        $this->assertStringContainsString('upacara tigang sasih (3 bulanan) buah hati kami, Kirana', $published->json('share_text'));

        $invitation = Invitation::findOrFail($id);
        $this->assertSame('Kirana', $invitation->display_name);
        $this->assertSame('Putri dari Wira & Ayu', $invitation->child_relation);

        $this->get('/u/tigang-sasih-kirana')->assertOk()
            ->assertSee('Upacara Tigang Sasih')
            ->assertSee('Putri dari Wira &amp; Ayu', false)
            ->assertSee('Ni Luh Kirana Dewi');

        $this->getJson('/api/moments')->assertOk()
            ->assertJsonPath('data.0.invitation_type', 'tigang_sasih')
            ->assertJsonPath('data.0.names', 'Kirana');
    }

    public function test_tigang_sasih_cannot_be_published_without_the_child_name(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $id = $this->postJson('/api/invitations/sync-local-draft', $this->payload([], 'tigang_sasih'))
            ->assertCreated()->json('data.id');

        $this->postJson("/api/invitations/{$id}/publish")->assertUnprocessable()
            ->assertJsonValidationErrors(['child_full_name', 'child_nickname'])
            ->assertJsonMissingValidationErrors(['groom_full_name', 'bride_full_name'])
            ->assertJsonPath('errors.child_full_name.0', fn ($message) => str_contains($message, 'nama lengkap buah hati'));

        // The draft still has a readable title while the name is missing.
        $this->assertSame('Buah Hati Wira & Ayu', Invitation::findOrFail($id)->display_name);
        $this->assertNull(Invitation::findOrFail($id)->child_relation);
    }

    public function test_otonan_offers_nine_templates_with_its_own_designs_first(): void
    {
        $response = $this->getJson('/api/templates?invitation_type=otonan')->assertOk()->assertJsonCount(9, 'data');
        $this->assertSame(
            ['Oton Kencana', 'Sekar Cempaka', 'Purnama Alit', 'Nyambutin Kencana', 'Tunjung Petak', 'Surya Alit', 'Rare Kencana', 'Sekar Jepun', 'Langit Kumara'],
            array_column($response->json('data'), 'name')
        );
        $this->getJson('/api/templates?invitation_type=tigang_sasih')->assertOk()->assertJsonCount(6, 'data');

        foreach (['oton-kencana', 'sekar-cempaka', 'purnama-alit', 'otonan-nyambutin-kencana', 'otonan-tunjung-petak', 'otonan-surya-alit', 'otonan-rare-kencana', 'otonan-sekar-jepun', 'otonan-langit-kumara'] as $slug) {
            $this->get('/preview/templates/'.$slug)->assertOk()
                ->assertSee('Upacara Otonan')->assertSee('Otonan Pertama')
                ->assertSee('upacara Otonan bagi buah hati kami')
                ->assertSee('Putra dari Wira &amp; Ayu', false)
                ->assertSee('I Putu Bagus Aditya')
                ->assertSee('Momen berharga')
                ->assertDontSee('Tigang Sasih')->assertDontSee('Hari-hari pertama');
        }

        // Otonan's own designs each have their own layout and ornament, and explain the ceremony.
        foreach (['oton-kencana' => 'orn-gapura', 'sekar-cempaka' => 'orn-penjor', 'purnama-alit' => 'orn-pawukon'] as $slug => $ornament) {
            $this->get('/preview/templates/'.$slug)->assertOk()
                ->assertSee('upacara-bali.css')->assertSee('ub--'.$slug)
                ->assertSee($ornament)->assertSee('ub-tridatu')
                ->assertSee('kalender pawukon Bali')
                ->assertSee('Tanda Kasih')->assertSee('Om Shanti, Shanti, Shanti Om');
        }
        $this->get('/preview/templates/otonan-rare-kencana')->assertOk()->assertSee('ub--rare-kencana')->assertDontSee('orn-gapura');
    }

    public function test_every_design_has_its_own_ornament_and_shows_the_ceremony_emblem(): void
    {
        $designs = [
            'rare-kencana' => 'orn-lamak', 'sekar-jepun' => 'orn-karangan', 'langit-kumara' => 'orn-layangan',
            'nyambutin-kencana' => 'orn-gebogan', 'tunjung-petak' => 'orn-kolam', 'surya-alit' => 'orn-fajar',
        ];
        foreach ($designs as $slug => $ornament) {
            // Without a photo the opening cover is the design's own illustration, embedded in the page.
            $this->get('/preview/templates/'.$slug)->assertOk()
                ->assertSee('ub--'.$slug)->assertSee($ornament)
                ->assertSee('ub-cover-art')->assertSee('data:image/svg+xml;base64,', false)
                ->assertSee('Tanda Kasih')->assertSee('Om Shanti, Shanti, Shanti Om');
            foreach (array_diff($designs, [$ornament]) as $other) {
                $this->get('/preview/templates/'.$slug)->assertDontSee($other);
            }
        }

        // The emblem and the line about the ceremony follow the ceremony, whichever design is chosen.
        $this->get('/preview/templates/rare-kencana')->assertSee('satu bulan Bali ditambah tujuh hari')->assertDontSee('ub-tridatu');
        $this->get('/preview/templates/tigang-sasih-rare-kencana')->assertSee('menapak Ibu Pertiwi')->assertDontSee('ub-tridatu');
        $this->get('/preview/templates/otonan-rare-kencana')->assertSee('kalender pawukon Bali')->assertSee('ub-tridatu');
    }

    public function test_a_child_photo_becomes_the_opening_cover(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(), 'sanctum');
        $id = $this->post('/api/invitations/sync-local-draft', $this->payload([]) + [
            'child_photo' => UploadedFile::fake()->image('bayi.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated()->json('data.id');
        $slug = $this->postJson("/api/invitations/{$id}/publish")->assertOk()->json('data.slug');

        $this->get('/u/'.$slug)->assertOk()
            ->assertSee(Invitation::findOrFail($id)->child_photo)
            ->assertSee('ub-child-photo')
            ->assertDontSee('ub-cover-art')->assertDontSee('data:image/svg+xml;base64,', false);
    }

    public function test_otonan_is_titled_by_the_child_name_and_may_carry_its_number(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $child = ['child_full_name' => 'Ni Luh Kirana Dewi', 'child_nickname' => 'Kirana', 'child_gender' => 'putri'];

        $third = $this->postJson('/api/invitations/sync-local-draft', $this->payload($child + ['ceremony_number' => 3], 'otonan'))
            ->assertCreated()->assertJsonPath('data.ceremony_number', 3)->json('data.id');
        $published = $this->postJson("/api/invitations/{$third}/publish")->assertOk()
            ->assertJsonPath('data.slug', 'otonan-ke-3-kirana');
        $this->assertStringContainsString('upacara otonan ke-3 buah hati kami, Kirana', $published->json('share_text'));
        $this->get('/u/otonan-ke-3-kirana')->assertOk()
            ->assertSee('Upacara Otonan')->assertSee('Otonan ke-3')
            ->assertSee('Putri dari Wira &amp; Ayu', false)
            ->assertSee('Ni Luh Kirana Dewi');

        $first = $this->postJson('/api/invitations/sync-local-draft', $this->payload($child + ['ceremony_number' => 1], 'otonan'))
            ->assertCreated()->json('data.id');
        $this->postJson("/api/invitations/{$first}/publish")->assertOk()->assertJsonPath('data.slug', 'otonan-pertama-kirana');
        $this->assertSame('Otonan Pertama', Invitation::findOrFail($first)->ceremony_note);

        // The number is optional: without it the invitation simply says "Otonan".
        $plain = $this->postJson('/api/invitations/sync-local-draft', $this->payload($child + ['ceremony_number' => ''], 'otonan'))
            ->assertCreated()->assertJsonPath('data.ceremony_number', null)->json('data.id');
        $published = $this->postJson("/api/invitations/{$plain}/publish")->assertOk()->assertJsonPath('data.slug', 'otonan-kirana');
        $this->assertStringContainsString('upacara otonan buah hati kami, Kirana', $published->json('share_text'));
        $this->get('/u/otonan-kirana')->assertOk()->assertSee('Upacara Otonan')->assertDontSee('Otonan ke-')->assertDontSee('Otonan Pertama');

        $this->getJson('/api/moments')->assertOk()->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.invitation_type', 'otonan')
            ->assertJsonPath('data.0.names', 'Kirana');
    }

    public function test_otonan_requires_the_child_name_and_a_sensible_number(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $id = $this->postJson('/api/invitations/sync-local-draft', $this->payload([], 'otonan'))->assertCreated()->json('data.id');
        $this->postJson("/api/invitations/{$id}/publish")->assertUnprocessable()
            ->assertJsonValidationErrors(['child_full_name', 'child_nickname']);

        foreach ([0, 201, 'tiga'] as $number) {
            $this->postJson('/api/invitations/sync-local-draft', $this->payload(['ceremony_number' => $number], 'otonan'))
                ->assertUnprocessable()->assertJsonValidationErrors('ceremony_number');
        }

        // Ceremonies held once ignore the number and keep their fixed note.
        $tigangSasih = $this->postJson('/api/invitations/sync-local-draft', $this->payload(['ceremony_number' => 5], 'tigang_sasih'))
            ->assertCreated()->assertJsonPath('data.ceremony_number', null)->json('data.id');
        $this->assertSame('3 Bulanan', Invitation::findOrFail($tigangSasih)->ceremony_note);
    }

    private function payload(array $child, string $type = 'pitung_dina'): array
    {
        return [
            'invitation_type' => $type,
            'selected_template' => InvitationTemplate::where('invitation_type', $type)->orderBy('id')->value('id'),
            'groom_data' => ['groom_full_name' => 'I Made Wira Adnyana', 'groom_nickname' => 'Wira'],
            'bride_data' => ['bride_full_name' => 'Ni Putu Ayu Lestari', 'bride_nickname' => 'Ayu'],
            'child_data' => $child,
            'event_data' => [
                'event_type' => Invitation::EVENT_TYPES[$type][0],
                'event_date' => now()->addDays(10)->toDateString(),
                'start_time' => '09:00',
                'venue_name' => 'Kediaman Keluarga I Made Wira',
                'venue_address' => 'Banjar Tegal, Ubud, Gianyar, Bali',
            ],
            'music_data' => ['music_type' => 'none'],
        ];
    }
}
