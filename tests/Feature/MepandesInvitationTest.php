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

class MepandesInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InvitationTemplateSeeder::class);
        Http::preventStrayRequests();
        Storage::fake('public');
    }

    public function test_catalog_offers_three_mepandes_designs_each_with_its_own_ornament(): void
    {
        $response = $this->getJson('/api/templates?invitation_type=mepandes')->assertOk()->assertJsonCount(3, 'data');
        $this->assertSame(['Bale Gading', 'Sekar Emas', 'Wastra Songket'], array_column($response->json('data'), 'name'));
        $this->getJson('/api/templates?invitation_type=otonan')->assertOk()->assertJsonCount(9, 'data');

        foreach (['bale-gading' => 'orn-bale', 'sekar-emas' => 'orn-sekar-emas', 'wastra-songket' => 'ub-songket'] as $slug => $ornament) {
            $this->get('/preview/templates/'.$slug.'?to=Bli%20Komang')->assertOk()
                ->assertSee('Buka Undangan')->assertSee('Bli Komang')
                ->assertSee('Upacara Mepandes')->assertSee('Potong Gigi')
                ->assertSee('Kirana, Bagus &amp; Dewi', false)
                ->assertSee('Putra-putri dari Wira &amp; Ayu', false)
                ->assertSee('Ni Luh Kirana Dewi')->assertSee('Putri · 19 tahun')
                ->assertSee('sad ripu')->assertSee('orn-emblem')
                ->assertSee('ub--'.$slug)->assertSee($ornament)
                ->assertSee('Momen berharga')->assertSee('Tanda Kasih')->assertSee('Om Shanti, Shanti, Shanti Om')
                ->assertDontSee('buah hati');
        }
    }

    public function test_one_family_lists_its_children_with_or_without_photos(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $id = $this->post('/api/invitations/sync-local-draft', $this->payload([
            'family_mode' => 'single',
            'participants' => [
                ['full_name' => 'Ni Luh Kirana Dewi', 'nickname' => 'Kirana', 'gender' => 'putri', 'age' => '19'],
                ['full_name' => 'I Made Bagus Aditya', 'nickname' => 'Bagus', 'gender' => 'putra'],
            ],
        ]) + ['participant_photos_changed' => '1', 'participant_photos' => [0 => UploadedFile::fake()->image('kirana.jpg')]], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.invitation_type', 'mepandes')
            ->assertJsonPath('data.family_mode', 'single')
            ->assertJsonPath('data.participants.0.nickname', 'Kirana')
            ->assertJsonPath('data.participants.0.age', 19)
            ->assertJsonPath('data.participants.1.photo', null)
            ->json('data.id');

        $invitation = Invitation::findOrFail($id);
        $photo = $invitation->participants[0]['photo'];
        Storage::disk('public')->assertExists($photo);
        $this->assertSame('Kirana & Bagus', $invitation->display_name);
        $this->assertSame('Putra-putri dari Wira & Ayu', $invitation->child_relation);
        $this->assertSame('Tanda Kasih', $invitation->gift_label);

        $published = $this->postJson("/api/invitations/{$id}/publish")->assertOk()->assertJsonPath('data.slug', 'mepandes-kirana-bagus');
        $this->assertStringContainsString('upacara mepandes (potong gigi) Kirana & Bagus', $published->json('share_text'));

        $this->get('/u/mepandes-kirana-bagus')->assertOk()
            ->assertSee('Kirana &amp; Bagus', false)
            ->assertSee('Ni Luh Kirana Dewi')->assertSee('Putri · 19 tahun')
            ->assertSee('I Made Bagus Aditya')
            ->assertSee($photo)
            ->assertSee('Putra-putri dari pasangan')
            ->assertSee('I Made Wira Adnyana')->assertSee('Ni Putu Ayu Lestari')
            ->assertSee('Wira &amp; Ayu</strong> beserta keluarga', false)
            // The first participant's photo is the opening cover.
            ->assertDontSee('ub-cover-art');

        $this->getJson('/api/moments')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.invitation_type', 'mepandes')
            ->assertJsonPath('data.0.names', 'Kirana & Bagus')
            ->assertJsonCount(1, 'data.0.photo_urls');
    }

    public function test_several_families_name_a_host_and_each_participants_parents(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $payload = $this->payload([
            'family_mode' => 'multi',
            'host_name' => 'Keluarga Besar I Made Wira',
            'participants' => [
                ['full_name' => 'Ni Luh Kirana Dewi', 'nickname' => 'Kirana', 'gender' => 'putri', 'father_name' => 'I Made Wira Adnyana', 'mother_name' => 'Ni Putu Ayu Lestari'],
                ['full_name' => 'I Gede Bagus Aditya', 'nickname' => 'Bagus', 'gender' => 'putra', 'father_name' => 'I Ketut Darma', 'mother_name' => 'Ni Wayan Sari'],
                ['full_name' => 'Ni Komang Dewi', 'nickname' => 'Dewi', 'father_name' => 'I Nyoman Rai', 'mother_name' => 'Ni Kadek Rina'],
                ['full_name' => 'I Putu Agus', 'nickname' => 'Agus', 'gender' => 'putra', 'father_name' => 'I Nyoman Rai', 'mother_name' => 'Ni Kadek Rina'],
            ],
        ]);
        unset($payload['groom_data'], $payload['bride_data']);

        $id = $this->postJson('/api/invitations/sync-local-draft', $payload)->assertCreated()->json('data.id');
        $invitation = Invitation::findOrFail($id);
        $this->assertSame('Kirana, Bagus & 2 lainnya', $invitation->display_name);
        $this->assertSame('Keluarga Besar I Made Wira', $invitation->child_relation);

        $this->postJson("/api/invitations/{$id}/publish")->assertOk()->assertJsonPath('data.slug', 'mepandes-kirana-bagus-2-lainnya');
        $this->get('/u/mepandes-kirana-bagus-2-lainnya')->assertOk()
            ->assertSee('Keluarga Besar I Made Wira')
            ->assertSee('Putra dari I Ketut Darma &amp; Ni Wayan Sari', false)
            ->assertSee('Ananda dari I Nyoman Rai &amp; Ni Kadek Rina', false)
            ->assertSee('Keluarga Besar I Made Wira</strong></p>', false)
            ->assertDontSee('dari pasangan')->assertDontSee('beserta keluarga');

        // Listed in the feed although there are no shared parents.
        $this->getJson('/api/moments')->assertOk()->assertJsonPath('data.0.names', 'Kirana, Bagus & 2 lainnya');
    }

    public function test_publishing_needs_the_people_and_parents_its_family_mode_calls_for(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $empty = $this->payload(['family_mode' => 'single']);
        unset($empty['bride_data']);
        $id = $this->postJson('/api/invitations/sync-local-draft', $empty)->assertCreated()->json('data.id');
        $this->postJson("/api/invitations/{$id}/publish")->assertUnprocessable()
            ->assertJsonValidationErrors(['participants', 'bride_full_name', 'bride_nickname'])
            ->assertJsonMissingValidationErrors(['groom_full_name', 'host_name'])
            ->assertJsonPath('errors.bride_full_name.0', fn ($message) => str_contains($message, 'nama lengkap ibu'));

        $multi = $this->payload(['family_mode' => 'multi', 'participants' => [['full_name' => 'Ni Luh Kirana Dewi', 'father_name' => 'I Made Wira']]]);
        unset($multi['groom_data'], $multi['bride_data']);
        $id = $this->postJson('/api/invitations/sync-local-draft', $multi)->assertCreated()->json('data.id');
        $this->postJson("/api/invitations/{$id}/publish")->assertUnprocessable()
            ->assertJsonValidationErrors(['host_name', 'participants.0.nickname', 'participants.0.mother_name'])
            ->assertJsonMissingValidationErrors(['groom_full_name', 'bride_full_name', 'participants.0.full_name', 'participants.0.father_name']);
        $this->assertSame('Yang Mepandes', Invitation::findOrFail($id)->display_name);
    }

    public function test_participant_fields_are_validated_and_capped(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $this->postJson('/api/invitations/sync-local-draft', $this->payload(['participants' => [['full_name' => 'Kirana', 'gender' => 'lainnya', 'age' => 0]]]))
            ->assertUnprocessable()->assertJsonValidationErrors(['participants.0.gender', 'participants.0.age']);
        $this->postJson('/api/invitations/sync-local-draft', $this->payload(['family_mode' => 'besar']))
            ->assertUnprocessable()->assertJsonValidationErrors('family_mode');
        $this->postJson('/api/invitations/sync-local-draft', $this->payload(['participants' => array_fill(0, 11, ['full_name' => 'Kirana', 'nickname' => 'Kirana'])]))
            ->assertUnprocessable()->assertJsonValidationErrors('participants');
        $this->post('/api/invitations/sync-local-draft', $this->payload(['participants' => [['full_name' => 'Kirana']]]) + [
            'participant_photos_changed' => '1', 'participant_photos' => [0 => UploadedFile::fake()->create('catatan.pdf', 10, 'application/pdf')],
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('participant_photos.0');
    }

    public function test_photos_follow_their_participant_through_edits_and_are_removed_with_them(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $people = [
            ['full_name' => 'Ni Luh Kirana Dewi', 'nickname' => 'Kirana'],
            ['full_name' => 'I Made Bagus Aditya', 'nickname' => 'Bagus'],
            ['full_name' => 'Ni Komang Dewi', 'nickname' => 'Dewi'],
        ];
        $id = $this->post('/api/invitations/sync-local-draft', $this->payload(['participants' => $people]) + [
            'participant_photos_changed' => '1',
            'participant_photos' => [0 => UploadedFile::fake()->image('kirana.jpg'), 2 => UploadedFile::fake()->image('dewi.jpg')],
        ], ['Accept' => 'application/json'])->assertCreated()->json('data.id');
        [$kirana, , $dewi] = array_column(Invitation::findOrFail($id)->participants, 'photo');
        Storage::disk('public')->assertExists([$kirana, $dewi]);

        // A text-only save sends no photos; everyone keeps theirs.
        $people[1]['nickname'] = 'Gus Aditya';
        $this->putJson("/api/invitations/{$id}", $this->payload(['participants' => $people]))->assertOk()
            ->assertJsonPath('data.participants.0.photo', $kirana)
            ->assertJsonPath('data.participants.1.nickname', 'Gus Aditya')
            ->assertJsonPath('data.participants.2.photo', $dewi);

        // Kirana is removed and Bagus gets a photo: Dewi keeps hers by naming it, and a path that is not hers is ignored.
        $this->post("/api/invitations/{$id}", ['_method' => 'PUT'] + $this->payload(['participants' => [
            $people[1] + ['photo_path' => 'invitations/photos/milik-orang-lain.jpg'],
            $people[2] + ['photo_path' => $dewi],
        ]]) + ['participant_photos_changed' => '1', 'participant_photos' => [0 => UploadedFile::fake()->image('bagus.jpg')]], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonCount(2, 'data.participants')->assertJsonPath('data.participants.1.photo', $dewi);
        $bagus = Invitation::findOrFail($id)->participants[0]['photo'];
        Storage::disk('public')->assertMissing($kirana);
        Storage::disk('public')->assertExists([$bagus, $dewi]);
        $this->assertNotSame('invitations/photos/milik-orang-lain.jpg', $bagus);

        // Cleaning up an archived invitation's media drops the photos but keeps the names.
        $invitation = Invitation::findOrFail($id);
        $invitation->forceFill(['status' => 'archived', 'event_date' => now()->subDays(200)])->save();
        $this->artisan('invitations:cleanup-media')->assertSuccessful();
        Storage::disk('public')->assertMissing([$bagus, $dewi]);
        $this->assertSame(['Gus Aditya', 'Dewi'], array_column($invitation->fresh()->participants, 'nickname'));
        $this->assertSame([null, null], array_column($invitation->fresh()->participants, 'photo'));
    }

    public function test_deleting_the_draft_removes_its_participant_photos(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $id = $this->post('/api/invitations/sync-local-draft', $this->payload(['participants' => [['full_name' => 'Kirana', 'nickname' => 'Kirana']]]) + [
            'participant_photos_changed' => '1', 'participant_photos' => [0 => UploadedFile::fake()->image('kirana.jpg')],
        ], ['Accept' => 'application/json'])->assertCreated()->json('data.id');
        $photo = Invitation::findOrFail($id)->participants[0]['photo'];

        $this->deleteJson("/api/invitations/{$id}")->assertOk();
        Storage::disk('public')->assertMissing($photo);
    }

    public function test_other_invitation_types_ignore_mepandes_fields(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');
        $wedding = $this->postJson('/api/invitations', [
            'template_id' => InvitationTemplate::where('invitation_type', 'wedding')->value('id'),
            'groom_nickname' => 'Wira', 'bride_nickname' => 'Ayu',
            'family_mode' => 'multi', 'host_name' => 'Tidak Boleh Tersimpan',
            'participants' => [['full_name' => 'Tidak Boleh Tersimpan', 'nickname' => 'Tidak']],
        ])->assertCreated();

        $saved = Invitation::findOrFail($wedding->json('data.id'));
        $this->assertNull($saved->participants);
        $this->assertNull($saved->family_mode);
        $this->assertNull($saved->host_name);
        $this->assertSame('Wira & Ayu', $saved->display_name);
    }

    private function payload(array $mepandes): array
    {
        return [
            'invitation_type' => 'mepandes',
            'selected_template' => InvitationTemplate::where('invitation_type', 'mepandes')->orderBy('id')->value('id'),
            'groom_data' => ['groom_full_name' => 'I Made Wira Adnyana', 'groom_nickname' => 'Wira'],
            'bride_data' => ['bride_full_name' => 'Ni Putu Ayu Lestari', 'bride_nickname' => 'Ayu'],
            'mepandes_data' => $mepandes,
            'event_data' => [
                'event_type' => 'Mepandes',
                'event_date' => now()->addDays(10)->toDateString(),
                'start_time' => '09:00',
                'venue_name' => 'Kediaman Keluarga I Made Wira',
                'venue_address' => 'Banjar Tegal, Ubud, Gianyar, Bali',
            ],
            'music_data' => ['music_type' => 'none'],
        ];
    }
}
