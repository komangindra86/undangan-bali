<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PremiumTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_premium_templates_never_reach_the_app(): void
    {
        [$free, $premium] = $this->templates();
        $user = User::factory()->create(['role' => 'user']);

        $this->getJson('/api/templates')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $free->id);
        $this->actingAs($user, 'sanctum')->getJson('/api/templates')->assertJsonCount(1, 'data');
        $this->actingAs($user, 'sanctum')->getJson("/api/templates/{$premium->id}")->assertNotFound();
        $this->actingAs($user, 'sanctum')->postJson('/api/invitations', [
            'invitation_type' => 'wedding',
            'template_id' => $premium->id,
            'groom_full_name' => 'I Made Wira',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Ubud, Bali',
        ])->assertUnprocessable()->assertJsonValidationErrors('template_id');

        // The preview stays open to anyone, so a design can be shown to a customer before the order.
        $this->app['auth']->forgetGuards();
        $this->get('/preview/templates/premium-sample')->assertOk();
    }

    public function test_admin_builds_a_customer_invitation_on_the_web_and_hands_over_the_link(): void
    {
        Storage::fake('public');
        [, $premium] = $this->templates();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'web')->get('/admin/custom')
            ->assertOk()->assertSee('Premium Sample')->assertSee('/preview/templates/premium-sample')->assertSee('Belum ada pesanan custom');
        $this->actingAs($admin, 'web')->get('/admin/custom/create')->assertOk()->assertSee('Premium Sample (premium)');

        $payload = $this->orderPayload($premium) + [
            'groom_photo' => UploadedFile::fake()->image('groom.jpg'),
            'gallery_photos' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')],
        ];
        $this->actingAs($admin, 'web')->post('/admin/custom', ['intent' => 'draft'] + $payload)->assertRedirect();

        $invitation = Invitation::firstOrFail();
        $this->assertSame('draft', $invitation->status);
        $this->assertNull($invitation->slug);
        $this->assertSame($admin->id, $invitation->user_id);
        $this->assertTrue($invitation->is_hidden_from_feed);
        $this->assertSame('1234567890', $invitation->order_details['account_number']);
        $this->assertArrayNotHasKey('order_details', $invitation->toArray());
        $this->assertCount(2, $invitation->gallery_photos);
        Storage::disk('public')->assertExists($invitation->groom_photo);
        $this->assertSame(['Pertama bertemu'], $invitation->moments()->pluck('title')->all());
        $this->assertTrue($invitation->giftSetting->is_active);

        $removed = $invitation->gallery_photos[0];
        $this->actingAs($admin, 'web')->put("/admin/custom/{$invitation->id}", [
            'intent' => 'publish',
            'venue_name' => 'Puri Agung Ubud',
            'remove_gallery' => [$removed],
            'story' => [['title' => 'Memadik', 'when' => '2024-06', 'body' => 'Dua keluarga bertemu.']],
        ] + $this->orderPayload($premium))->assertRedirect("/admin/custom/{$invitation->id}/edit");

        $invitation->refresh();
        $this->assertSame('published', $invitation->status);
        $this->assertSame('undangan-wira-ayu', $invitation->slug);
        $this->assertSame('Puri Agung Ubud', $invitation->venue_name);
        $this->assertCount(1, $invitation->gallery_photos);
        Storage::disk('public')->assertMissing($removed);
        $this->assertSame(['Memadik'], $invitation->moments()->pluck('title')->all());
        $this->assertSame(1, $premium->fresh()->usage_count);

        $this->actingAs($admin, 'web')->get("/admin/custom/{$invitation->id}/edit")
            ->assertOk()->assertSee('/u/undangan-wira-ayu/bagikan')->assertSee('Simpan perubahan')->assertDontSee('Simpan sebagai draft');
        $this->actingAs($admin, 'web')->get('/admin/custom')->assertSee('Ni Kadek Sari')->assertSee('Halaman bagikan');

        // Saving again keeps it live and does not count the template twice.
        $this->actingAs($admin, 'web')->put("/admin/custom/{$invitation->id}", ['intent' => 'publish'] + $this->orderPayload($premium))->assertRedirect();
        $this->assertSame(1, $premium->fresh()->usage_count);
        $this->app['auth']->forgetGuards();

        $this->get('/u/undangan-wira-ayu')->assertOk();
        $this->get('/u/undangan-wira-ayu/bagikan')->assertOk();
        $this->get('/gift/undangan-wira-ayu')->assertOk();
        $this->getJson('/api/moments')->assertJsonCount(0, 'data');
    }

    public function test_custom_order_takes_picked_times_uploaded_music_and_shows_gifts_to_the_customer(): void
    {
        Storage::fake('public');
        [, $premium] = $this->templates();
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = [
            'intent' => 'publish',
            'start_hour' => '9',
            'start_minute' => '30',
            'end_hour' => '',
            'end_minute' => '0',
            'story' => [['title' => 'Memadik', 'month' => '6', 'year' => '2024', 'body' => 'Dua keluarga bertemu.']],
        ] + collect($this->orderPayload($premium))->except(['start_time', 'end_time', 'story'])->all();
        $song = fn () => UploadedFile::fake()->create('lagu.mp3', 500, 'audio/mpeg');

        $this->actingAs($admin, 'web')->post('/admin/custom', $payload + ['music_file' => $song()])
            ->assertSessionHasErrors('music_rights_confirmed');
        $this->assertSame(0, Invitation::count());

        $this->actingAs($admin, 'web')->post('/admin/custom', $payload + ['music_file' => $song(), 'music_rights_confirmed' => '1'])->assertRedirect();
        $invitation = Invitation::firstOrFail();
        $this->assertStringStartsWith('09:30', $invitation->start_time);
        $this->assertNull($invitation->end_time);
        $this->assertSame('upload', $invitation->music_type);
        $this->assertNotNull($invitation->music_rights_accepted_at);
        Storage::disk('public')->assertExists($invitation->music_file);
        $this->assertSame('2024-06', $invitation->moments()->firstOrFail()->occurred_at->format('Y-m'));
        $this->assertSame(40, strlen($invitation->monitor_token));
        $this->assertArrayNotHasKey('monitor_token', $invitation->toArray());

        $this->actingAs($admin, 'web')->get("/admin/custom/{$invitation->id}/edit")
            ->assertOk()->assertSee('Musik unggahan saat ini')->assertSee('/pantau/'.$invitation->monitor_token);

        // Saving with the uploaded music still chosen keeps the file; choosing none removes it.
        $uploaded = $invitation->music_file;
        $this->actingAs($admin, 'web')->put("/admin/custom/{$invitation->id}", $payload + ['music_id' => 'upload'])->assertRedirect();
        Storage::disk('public')->assertExists($uploaded);
        $this->actingAs($admin, 'web')->put("/admin/custom/{$invitation->id}", $payload + ['music_id' => ''])->assertRedirect();
        Storage::disk('public')->assertMissing($uploaded);
        $this->assertSame('none', $invitation->fresh()->music_type);

        $tooMany = array_map(fn ($number) => UploadedFile::fake()->image("foto-{$number}.jpg", 20, 20), range(1, 21));
        $this->actingAs($admin, 'web')->put("/admin/custom/{$invitation->id}", $payload + ['gallery_photos' => $tooMany])
            ->assertSessionHasErrors('gallery_photos');
        $this->actingAs($admin, 'web')->put("/admin/custom/{$invitation->id}", $payload + ['gallery_photos' => array_slice($tooMany, 0, 20)])
            ->assertRedirect();
        $this->assertCount(20, $invitation->fresh()->gallery_photos);

        foreach ([['Tamu Baik', 150000, 'paid', 'Selamat menempuh hidup baru'], ['Tamu Batal', 99000, 'pending', null]] as [$guest, $amount, $status, $message]) {
            $invitation->weddingGifts()->create([
                'guest_name' => $guest,
                'message' => $message,
                'gift_amount' => $amount,
                'service_fee' => 0,
                'total_amount' => $amount,
                'order_id' => 'WGIFT-'.$status,
                'transaction_status' => $status,
                'paid_at' => $status === 'paid' ? now() : null,
            ]);
        }
        $this->app['auth']->forgetGuards();

        $this->get('/pantau/'.$invitation->monitor_token)
            ->assertOk()
            ->assertSee('Tamu Baik')
            ->assertSee('Rp150.000')
            ->assertSee('Selamat menempuh hidup baru')
            ->assertDontSee('Tamu Batal')
            ->assertDontSee('Rp99.000');
        $this->get('/pantau/'.str_repeat('x', 40))->assertNotFound();
    }

    public function test_custom_orders_are_for_admins_and_only_cover_admin_made_invitations(): void
    {
        [$free, $premium] = $this->templates();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $theirs = Invitation::create([
            'user_id' => $user->id,
            'template_id' => $free->id,
            'status' => 'draft',
            'groom_nickname' => 'Wira',
            'bride_nickname' => 'Ayu',
        ]);

        $this->actingAs($user, 'web')->get('/admin/custom')->assertForbidden();
        $this->actingAs($user, 'web')->post('/admin/custom', ['intent' => 'publish'] + $this->orderPayload($premium))->assertForbidden();
        $this->assertSame(1, Invitation::count());

        $this->actingAs($admin, 'web')->get("/admin/custom/{$theirs->id}/edit")->assertNotFound();
        $this->actingAs($admin, 'web')->put("/admin/custom/{$theirs->id}", ['intent' => 'publish'] + $this->orderPayload($premium))->assertNotFound();
        $this->assertSame('draft', $theirs->fresh()->status);

        $this->actingAs($admin, 'web')->post('/admin/custom', ['intent' => 'publish', 'groom_nickname' => 'Wira<b>', 'gift_receiver_name' => ''] + $this->orderPayload($premium))
            ->assertSessionHasErrors(['groom_nickname', 'gift_receiver_name']);
        $this->assertSame(1, Invitation::count());
    }

    public function test_the_other_premium_designs_render_with_the_least_and_the_most_an_invitation_can_hold(): void
    {
        $this->seed(InvitationTemplateSeeder::class);
        $owner = User::factory()->create(['role' => 'admin']);

        foreach (['taman-tirta' => 'Taman Tirta', 'lamak-prada' => 'Lamak Prada'] as $slug => $name) {
            $template = InvitationTemplate::where('slug', $slug)->firstOrFail();
            $this->assertTrue($template->is_premium);
            $base = [
                'user_id' => $owner->id,
                'template_id' => $template->id,
                'status' => 'published',
                'groom_full_name' => 'I Made Wira Adnyana',
                'groom_nickname' => 'Wira',
                'bride_full_name' => 'Ni Putu Ayu Lestari',
                'bride_nickname' => 'Ayu',
                'event_type' => 'Pawiwahan',
                'event_date' => now()->addMonth()->toDateString(),
                'start_time' => '10:00',
                'venue_name' => 'Bale Banjar Ubud',
                'venue_address' => 'Jalan Raya Ubud, Gianyar, Bali',
                'music_type' => 'none',
                'published_at' => now(),
            ];
            Invitation::create($base + ['slug' => $slug.'-minimal']);
            $full = Invitation::create($base + [
                'slug' => $slug.'-lengkap',
                'groom_father_name' => 'I Ketut Darma',
                'groom_mother_name' => 'Ni Luh Sari',
                'bride_mother_name' => 'Ni Made Rini',
                'end_time' => '13:00',
                'google_maps_url' => 'https://maps.google.com/?q=-8.5069,115.2625',
                'groom_photo' => 'invitations/qa/groom.jpg',
                'gallery_photos' => array_map(fn ($number) => "invitations/qa/{$number}.jpg", range(1, 20)),
            ]);
            $full->moments()->create(['title' => 'Pertama bertemu', 'body' => 'Di pura desa.', 'occurred_at' => '2021-03-01']);

            $this->get('/preview/templates/'.$slug)->assertOk()->assertSee('Preview '.$name)->assertSee('Kisah kami')->assertSee('Simpan ke kalender');
            $this->get('/u/'.$slug.'-minimal?to=Bapak+Wayan')
                ->assertOk()
                ->assertSee('Bapak Wayan')
                ->assertDontSee('Kisah kami')
                ->assertDontSee('Putra dari')
                ->assertDontSee('Momen kami')
                ->assertSee('laporkan undangan ini');
            $this->get('/u/'.$slug.'-lengkap')
                ->assertOk()
                ->assertSee('Pertama bertemu')
                ->assertSee('I Ketut Darma &amp; Ni Luh Sari', false)
                ->assertSee('Ni Made Rini')
                ->assertSee('10:00 - 13:00 WITA')
                ->assertSee('invitations/qa/20.jpg')
                ->assertSee('Buka Google Maps');
        }
    }

    public function test_free_designs_leave_out_the_couples_story(): void
    {
        $this->seed(InvitationTemplateSeeder::class);
        $owner = User::factory()->create(['role' => 'user']);
        $invitation = Invitation::create([
            'user_id' => $owner->id,
            'template_id' => InvitationTemplate::where('slug', 'jepun-kirana')->value('id'),
            'slug' => 'wira-ayu-gratis',
            'status' => 'published',
            'groom_full_name' => 'I Made Wira Adnyana',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu Lestari',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar Ubud',
            'venue_address' => 'Jalan Raya Ubud, Gianyar, Bali',
            'music_type' => 'none',
            'published_at' => now(),
        ]);
        $invitation->moments()->create(['title' => 'Fitting baju adat', 'body' => 'Persiapan hari H.']);

        $this->get('/preview/templates/jepun-kirana')->assertOk()->assertDontSee('Kisah kami');
        $this->get('/u/wira-ayu-gratis')->assertOk()->assertSee('Mempelai')->assertDontSee('Kisah kami')->assertDontSee('Fitting baju adat');

        // The other free designs, with the least and the most an invitation made in the app can hold.
        foreach (['carik-asri' => 'Carik Asri', 'ukir-kencana' => 'Ukir Kencana'] as $slug => $name) {
            $this->get('/preview/templates/'.$slug)->assertOk()->assertSee('Preview '.$name)->assertDontSee('Kisah kami');
            $invitation->update(['template_id' => InvitationTemplate::where('slug', $slug)->value('id'), 'gallery_photos' => null, 'groom_photo' => null]);
            $this->get('/u/wira-ayu-gratis?to=Bapak+Wayan')
                ->assertOk()
                ->assertSee('Bapak Wayan')
                ->assertDontSee('Kisah kami')
                ->assertDontSee('Putra dari')
                ->assertDontSee('Momen kami');
            $invitation->update([
                'groom_father_name' => 'I Ketut Darma',
                'groom_mother_name' => 'Ni Luh Sari',
                'end_time' => '13:00',
                'google_maps_url' => 'https://maps.google.com/?q=-8.5069,115.2625',
                'groom_photo' => 'invitations/qa/groom.jpg',
                'gallery_photos' => array_map(fn ($number) => "invitations/qa/{$number}.jpg", range(1, 6)),
            ]);
            $this->get('/u/wira-ayu-gratis')
                ->assertOk()
                ->assertSee('I Ketut Darma &amp; Ni Luh Sari', false)
                ->assertSee('10:00 - 13:00 WITA')
                ->assertSee('invitations/qa/6.jpg')
                ->assertSee('Buka Google Maps')
                ->assertDontSee('Fitting baju adat');
            $invitation->update(['groom_father_name' => null, 'groom_mother_name' => null, 'end_time' => null, 'google_maps_url' => null]);
        }
    }

    public function test_share_page_lets_the_host_send_personal_links_without_the_app(): void
    {
        [$free] = $this->templates();
        $owner = User::factory()->create(['role' => 'user']);
        $invitation = Invitation::create([
            'user_id' => $owner->id,
            'template_id' => $free->id,
            'slug' => 'wira-ayu',
            'status' => 'published',
            'groom_full_name' => 'I Made Wira Adnyana',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu Lestari',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => now()->addMonth()->toDateString(),
            'published_at' => now(),
        ]);

        $this->get('/u/wira-ayu/bagikan')
            ->assertOk()
            ->assertSee('Bagikan Undangan')
            ->assertSee('Banyak tamu sekaligus')
            ->assertSee('data-url="'.route('invitations.public', 'wira-ayu').'"', false);
        $this->get('/u/tidak-ada/bagikan')->assertNotFound();

        $invitation->takeDown('Undangan palsu');
        $this->get('/u/wira-ayu/bagikan')->assertNotFound();
    }

    public function test_wiwaha_agung_renders_with_the_least_and_the_most_an_invitation_can_hold(): void
    {
        $this->seed(InvitationTemplateSeeder::class);
        $template = InvitationTemplate::where('slug', 'wiwaha-agung')->firstOrFail();
        $this->assertTrue($template->is_premium);
        $owner = User::factory()->create(['role' => 'user']);
        $base = [
            'user_id' => $owner->id,
            'template_id' => $template->id,
            'status' => 'published',
            'groom_full_name' => 'I Made Wira Adnyana',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu Lestari',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar Ubud',
            'venue_address' => 'Jalan Raya Ubud, Gianyar, Bali',
            'music_type' => 'none',
            'published_at' => now(),
        ];
        Invitation::create($base + ['slug' => 'wira-ayu-minimal']);
        $full = Invitation::create($base + [
            'slug' => 'wira-ayu-lengkap',
            'groom_father_name' => 'I Ketut Darma',
            'groom_mother_name' => 'Ni Luh Sari',
            'bride_father_name' => 'I Wayan Sudarta',
            'end_time' => '13:00',
            'opening_quote' => 'Dengan restu keluarga kami mengundang.',
            'google_maps_url' => 'https://maps.google.com/?q=-8.5069,115.2625',
            'groom_photo' => 'invitations/qa/groom.jpg',
            'gallery_photos' => ['invitations/qa/one.jpg', 'invitations/qa/two.jpg'],
        ]);
        $full->moments()->create(['title' => 'Pertama bertemu', 'body' => 'Di pura desa.', 'occurred_at' => '2021-03-01']);

        $this->get('/preview/templates/wiwaha-agung')->assertOk()->assertSee('Kisah kami')->assertSee('Simpan ke kalender');
        $this->get('/u/wira-ayu-minimal?to=Bapak+Wayan')
            ->assertOk()
            ->assertSee('Bapak Wayan')
            ->assertDontSee('Kisah kami')
            ->assertDontSee('Putra dari')
            ->assertDontSee('Momen kami')
            ->assertSee('laporkan undangan ini');
        $this->get('/u/wira-ayu-lengkap')
            ->assertOk()
            ->assertSee('Pertama bertemu')
            ->assertSee('I Ketut Darma &amp; Ni Luh Sari', false)
            ->assertSee('I Wayan Sudarta')
            ->assertSee('10:00 - 13:00 WITA')
            ->assertSee('Momen kami')
            ->assertSee('Buka Google Maps');
    }

    private function orderPayload(InvitationTemplate $template): array
    {
        return [
            'template_id' => $template->id,
            'groom_full_name' => 'I Made Wira Adnyana',
            'groom_nickname' => 'Wira',
            'groom_father_name' => 'I Ketut Darma',
            'bride_full_name' => 'Ni Putu Ayu Lestari',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '13:00',
            'venue_name' => 'Bale Banjar Ubud',
            'venue_address' => 'Jalan Raya Ubud, Gianyar, Bali',
            'story' => [['title' => 'Pertama bertemu', 'when' => '2021-03', 'body' => 'Di pura desa.'], ['title' => '', 'when' => '', 'body' => '']],
            'gift_active' => '1',
            'gift_receiver_name' => 'Wira & Ayu',
            'gift_minimum_amount' => 25000,
            'order' => ['customer_name' => 'Ni Kadek Sari', 'customer_whatsapp' => '081234567890', 'bank_name' => 'BCA', 'account_number' => '1234567890', 'account_holder' => 'Ni Kadek Sari'],
        ];
    }

    private function templates(): array
    {
        $free = InvitationTemplate::create([
            'name' => 'Bali Classic',
            'slug' => 'bali-classic',
            'blade_view' => 'invitations.templates.bali-classic',
        ]);
        $premium = InvitationTemplate::create([
            'name' => 'Premium Sample',
            'slug' => 'premium-sample',
            'blade_view' => 'invitations.templates.bali-classic',
            'is_premium' => true,
        ]);

        return [$free, $premium];
    }
}
