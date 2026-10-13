<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PremiumTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_premium_templates_only_show_for_accounts_with_access(): void
    {
        [$free, $premium] = $this->templates();
        $customer = User::factory()->create(['role' => 'user']);
        $studio = User::factory()->create(['role' => 'user']);
        $studio->forceFill(['can_use_premium_templates' => true])->save();

        $this->getJson('/api/templates')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $free->id);
        $this->getJson("/api/templates/{$premium->id}")->assertNotFound();
        $this->actingAs($customer, 'sanctum')->getJson('/api/templates')->assertJsonCount(1, 'data');
        $this->actingAs($customer, 'sanctum')->getJson("/api/templates/{$premium->id}")->assertNotFound();

        $this->actingAs($studio, 'sanctum')->getJson('/api/templates')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.1.id', $premium->id)
            ->assertJsonPath('data.1.is_premium', true);
        $this->actingAs($studio, 'sanctum')->getJson("/api/templates/{$premium->id}")->assertOk();

        // The preview stays open to anyone, so a design can be shown to a customer before the order.
        $this->app['auth']->forgetGuards();
        $this->get('/preview/templates/premium-sample')->assertOk();
    }

    public function test_only_accounts_with_access_can_build_and_publish_on_a_premium_template(): void
    {
        [, $premium] = $this->templates();
        $customer = User::factory()->create(['role' => 'user']);
        $studio = User::factory()->create(['role' => 'user']);
        $studio->forceFill(['can_use_premium_templates' => true])->save();
        $payload = [
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
        ];

        $this->actingAs($customer, 'sanctum')->postJson('/api/invitations', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('template_id');

        $id = $this->actingAs($studio, 'sanctum')->postJson('/api/invitations', $payload)->assertCreated()->json('data.id');
        $slug = $this->actingAs($studio, 'sanctum')->postJson("/api/invitations/{$id}/publish")->assertOk()->json('data.slug');
        $this->app['auth']->forgetGuards();
        $this->get('/u/'.$slug)->assertOk();

        // Losing access later leaves a live invitation alone, but it cannot be published again on that design.
        $studio->forceFill(['can_use_premium_templates' => false])->save();
        $this->get('/u/'.$slug)->assertOk();
        $this->actingAs($studio->fresh(), 'sanctum')->postJson("/api/invitations/{$id}/publish")->assertUnprocessable();
    }

    public function test_admin_grants_and_revokes_premium_access_by_email(): void
    {
        $this->templates();
        $admin = User::factory()->create(['role' => 'admin']);
        $studio = User::factory()->create(['role' => 'user', 'email' => 'studio@example.com', 'name' => 'Studio Santih']);

        $this->actingAs($admin, 'web')->get('/admin/templates')
            ->assertOk()->assertSee('Premium Sample')->assertSee('/preview/templates/premium-sample')->assertSee('Belum ada akun yang diberi akses');
        $this->actingAs($admin, 'web')->post('/admin/templates/access', ['email' => 'tidak-ada@example.com'])
            ->assertSessionHasErrors('email');
        $this->actingAs($admin, 'web')->post('/admin/templates/access', ['email' => 'admin-lain@example.com'])
            ->assertSessionHasErrors('email');

        $this->actingAs($admin, 'web')->post('/admin/templates/access', ['email' => ' Studio@Example.com '])->assertRedirect();
        $this->assertTrue($studio->fresh()->can_use_premium_templates);
        $this->actingAs($admin, 'web')->get('/admin/templates')->assertSee('Studio Santih')->assertSee('Cabut akses');

        $this->actingAs($admin, 'web')->put("/admin/templates/access/{$studio->id}/revoke")->assertRedirect();
        $this->assertFalse($studio->fresh()->can_use_premium_templates);

        $this->actingAs($studio, 'web')->get('/admin/templates')->assertForbidden();
        $this->actingAs($studio, 'web')->post('/admin/templates/access', ['email' => 'studio@example.com'])->assertForbidden();
        $this->assertFalse($studio->fresh()->can_use_premium_templates);
    }

    public function test_registering_cannot_switch_premium_access_on(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'can_use_premium_templates' => true,
        ])->assertCreated();

        $this->assertFalse(User::where('email', 'baru@example.com')->firstOrFail()->can_use_premium_templates);
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
