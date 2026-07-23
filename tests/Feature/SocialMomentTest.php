<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialMomentTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_exposes_only_minimal_invitation_information(): void
    {
        $invitation = $this->publishedInvitation();
        $invitation->update([
            'moment_caption' => 'Kami sedang mempersiapkan hari bahagia.',
            'venue_name' => 'Alamat Rahasia',
            'venue_address' => 'Jalan Sangat Rahasia Nomor 1',
            'google_maps_url' => 'https://maps.google.com/?q=-8.5,115.2',
        ]);

        $response = $this->getJson('/api/moments')->assertOk();

        $response->assertJsonPath('data.0.names', 'Wira & Ayu')
            ->assertJsonPath('data.0.caption', 'Kami sedang mempersiapkan hari bahagia.')
            ->assertJsonMissing(['venue_name' => 'Alamat Rahasia'])
            ->assertJsonMissing(['venue_address' => 'Jalan Sangat Rahasia Nomor 1'])
            ->assertJsonMissing(['google_maps_url' => 'https://maps.google.com/?q=-8.5,115.2'])
            ->assertJsonMissing(['event_date' => '2026-08-18']);
    }

    public function test_guest_can_request_invitation_and_owner_can_prepare_whatsapp_share(): void
    {
        $invitation = $this->publishedInvitation();

        $this->postJson('/api/moments/'.$invitation->id.'/request-invitation', [
            'requester_name' => 'Komang Tamu',
            'requester_whatsapp' => '0812 3456 7890',
        ])->assertCreated();

        $this->assertDatabaseHas('invitation_requests', [
            'invitation_id' => $invitation->id,
            'requester_name' => 'Komang Tamu',
            'requester_whatsapp' => '6281234567890',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('social_notifications', [
            'user_id' => $invitation->user_id,
            'type' => 'invitation_request',
        ]);

        $requestId = $invitation->invitationRequests()->firstOrFail()->id;
        $response = $this->actingAs($invitation->user, 'sanctum')
            ->putJson('/api/invitations/'.$invitation->id.'/invitation-requests/'.$requestId.'/shared')
            ->assertOk()
            ->assertJsonPath('data.status', 'shared')
            ->assertJsonPath('data.requester_whatsapp', '6281234567890');

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $response->json('whatsapp_url'));
        $this->assertStringContainsString('wira-ayu', urldecode($response->json('whatsapp_url')));
    }

    public function test_authenticated_user_can_react_and_comment(): void
    {
        $invitation = $this->publishedInvitation();
        $guest = User::factory()->create(['role' => 'user']);

        $this->actingAs($guest, 'sanctum')
            ->postJson('/api/moments/'.$invitation->id.'/reaction', ['type' => 'love'])
            ->assertOk()
            ->assertJsonPath('data.type', 'love');

        $this->actingAs($guest, 'sanctum')
            ->postJson('/api/moments/'.$invitation->id.'/comments', ['body' => 'Selamat menempuh hidup baru!'])
            ->assertCreated()
            ->assertJsonPath('data.user.name', $guest->name);

        $this->assertDatabaseHas('invitation_reactions', [
            'invitation_id' => $invitation->id,
            'user_id' => $guest->id,
            'type' => 'love',
        ]);
        $this->assertDatabaseCount('social_notifications', 2);
    }

    public function test_feed_excludes_demo_and_sanitizes_legacy_names(): void
    {
        $invitation = $this->publishedInvitation();
        $invitation->update([
            'groom_nickname' => '<script>alert(1)</script>',
            'bride_nickname' => '<b>Ayu</b>',
        ]);
        $demo = $invitation->replicate();
        $demo->slug = 'demo-social-feed';
        $demo->save();

        $this->getJson('/api/moments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.names', 'Mempelai & Ayu')
            ->assertJsonMissing(['slug' => 'demo-social-feed']);
    }

    public function test_feed_prioritizes_gallery_and_returns_every_photo_without_duplicates(): void
    {
        $invitation = $this->publishedInvitation();
        $invitation->update([
            'groom_photo' => 'invitations/photos/groom.jpg',
            'bride_photo' => 'invitations/photos/bride.jpg',
            'gallery_photos' => [
                'invitations/gallery/one.jpg',
                'invitations/gallery/two.jpg',
                'invitations/photos/groom.jpg',
            ],
        ]);
        $invitation->moments()->create([
            'title' => 'Prewedding',
            'photo_path' => 'invitations/moments/prewedding.jpg',
        ]);

        $this->getJson('/api/moments')
            ->assertOk()
            ->assertJsonCount(5, 'data.0.photo_urls')
            ->assertJsonPath('data.0.cover_photo_url', url('/storage/invitations/gallery/one.jpg'))
            ->assertJsonPath('data.0.photo_urls.1', url('/storage/invitations/gallery/two.jpg'))
            ->assertJsonPath('data.0.photo_urls.3', url('/storage/invitations/moments/prewedding.jpg'))
            ->assertJsonPath('data.0.photo_urls.4', url('/storage/invitations/photos/bride.jpg'));
    }

    public function test_feed_uses_couple_photo_when_gallery_and_moments_are_empty(): void
    {
        $invitation = $this->publishedInvitation();
        $invitation->update([
            'groom_photo' => 'invitations/photos/groom.jpg',
            'bride_photo' => 'invitations/photos/bride.jpg',
            'gallery_photos' => [],
        ]);

        $this->getJson('/api/moments')
            ->assertOk()
            ->assertJsonPath('data.0.cover_photo_url', url('/storage/invitations/photos/groom.jpg'))
            ->assertJsonCount(2, 'data.0.photo_urls');
    }

    public function test_feed_is_paginated_ten_moments_per_page(): void
    {
        $invitation = $this->publishedInvitation();

        foreach (range(1, 10) as $index) {
            $copy = $invitation->replicate();
            $copy->slug = 'wira-ayu-'.$index;
            $copy->save();
        }

        $this->getJson('/api/moments')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.last_page', 2);

        $this->getJson('/api/moments?page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    private function publishedInvitation(): Invitation
    {
        $owner = User::factory()->create(['role' => 'user']);
        $template = InvitationTemplate::create([
            'name' => 'Bali Classic',
            'slug' => 'bali-classic',
            'blade_view' => 'invitations.templates.bali-classic',
        ]);

        return Invitation::create([
            'user_id' => $owner->id,
            'template_id' => $template->id,
            'slug' => 'wira-ayu',
            'status' => 'published',
            'groom_full_name' => 'I Made Wira Adnyana',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu Lestari',
            'bride_nickname' => 'Ayu',
            'event_date' => '2026-08-18',
            'published_at' => now(),
        ]);
    }
}
