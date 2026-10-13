<?php

namespace Tests\Feature;

use App\Models\ContentReport;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\WeddingGift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InvitationTakedownTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_takes_down_an_invitation_and_everything_public_goes_off(): void
    {
        $invitation = $this->publishedInvitation();
        $invitation->giftSetting()->create(['is_active' => true, 'receiver_name' => 'Wira dan Ayu', 'minimum_amount' => 10000]);
        $reporter = User::factory()->create(['role' => 'user']);
        $this->actingAs($reporter, 'sanctum')
            ->postJson("/api/moments/{$invitation->id}/report", ['reason' => 'other'])
            ->assertCreated();
        $this->app['auth']->forgetGuards();

        $this->get('/u/wira-ayu')->assertOk();
        $this->get('/gift/wira-ayu')->assertOk();
        $this->getJson('/api/moments')->assertJsonCount(1, 'data');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'web')->get('/admin/invitations?q=wira')->assertOk()->assertSee('Wira')->assertSee('1 laporan menunggu');
        $this->actingAs($admin, 'web')
            ->put("/admin/invitations/{$invitation->id}/take-down", ['takedown_note' => 'Undangan palsu, dilaporkan keluarga'])
            ->assertRedirect();

        $invitation->refresh();
        $this->assertTrue($invitation->isTakenDown());
        $this->assertSame('resolved', ContentReport::firstOrFail()->status);
        $this->actingAs($admin, 'web')->get('/admin/invitations?filter=taken_down')
            ->assertOk()->assertSee('Undangan palsu, dilaporkan keluarga')->assertSee('Pulihkan undangan');
        // The admin can still open the page to review it.
        $this->actingAs($admin, 'web')->get('/u/wira-ayu')->assertOk();
        $this->app['auth']->forgetGuards();

        $views = $invitation->views()->count();
        $this->get('/u/wira-ayu')->assertStatus(410)->assertSee('Undangan tidak tersedia')->assertDontSee('Wira');
        $this->assertSame($views, $invitation->views()->count());
        $this->get('/gift/wira-ayu')->assertNotFound();
        Http::fake();
        $this->postJson('/api/public/invitations/wira-ayu/wedding-gift/create', ['guest_name' => 'Komang', 'gift_amount' => 100000])
            ->assertNotFound();
        Http::assertNothingSent();
        $this->assertSame(0, WeddingGift::count());
        $this->getJson('/api/moments')->assertJsonCount(0, 'data');
        $this->getJson("/api/moments/{$invitation->id}")->assertNotFound();
    }

    public function test_owner_cannot_undo_or_erase_a_takedown(): void
    {
        $invitation = $this->publishedInvitation();
        $invitation->takeDown('Undangan palsu');
        $owner = $invitation->user;

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/invitations/{$invitation->id}/publish")
            ->assertUnprocessable();
        $this->actingAs($owner, 'sanctum')
            ->putJson("/api/invitations/{$invitation->id}/feed-visibility", ['is_hidden_from_feed' => false])
            ->assertUnprocessable();
        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/invitations/{$invitation->id}")
            ->assertUnprocessable();
        $this->actingAs($owner, 'sanctum')
            ->getJson("/api/invitations/{$invitation->id}")
            ->assertOk()
            ->assertJsonMissingPath('data.takedown_note')
            ->assertJsonPath('data.taken_down_at', $invitation->fresh()->taken_down_at->toJSON());

        $this->assertTrue($invitation->fresh()->isTakenDown());
    }

    public function test_admin_can_restore_a_taken_down_invitation(): void
    {
        $invitation = $this->publishedInvitation();
        $invitation->takeDown('Keliru');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'web')->put("/admin/invitations/{$invitation->id}/restore")->assertRedirect();
        $this->app['auth']->forgetGuards();

        $this->assertFalse($invitation->fresh()->isTakenDown());
        $this->assertNull($invitation->fresh()->takedown_note);
        $this->get('/u/wira-ayu')->assertOk();
        $this->getJson('/api/moments')->assertJsonCount(1, 'data');
    }

    public function test_report_queue_can_take_an_invitation_down_with_a_reason(): void
    {
        $invitation = $this->publishedInvitation();
        $reporter = User::factory()->create(['role' => 'user']);
        $this->actingAs($reporter, 'sanctum')
            ->postJson("/api/moments/{$invitation->id}/report", ['reason' => 'other', 'note' => 'Ini bukan acara kami'])
            ->assertCreated();
        $report = ContentReport::firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'web')
            ->put("/admin/reports/{$report->id}", ['action' => 'take_down'])
            ->assertSessionHasErrors('admin_note');
        $this->assertFalse($invitation->fresh()->isTakenDown());

        $this->actingAs($admin, 'web')
            ->put("/admin/reports/{$report->id}", ['action' => 'take_down', 'admin_note' => 'Dikonfirmasi palsu'])
            ->assertRedirect();

        $this->assertTrue($invitation->fresh()->isTakenDown());
        $this->assertSame('Dikonfirmasi palsu', $invitation->fresh()->takedown_note);
        $this->assertSame('resolved', $report->fresh()->status);
    }

    public function test_owner_cannot_bring_back_a_moment_the_admin_hid(): void
    {
        $invitation = $this->publishedInvitation();
        $reporter = User::factory()->create(['role' => 'user']);
        $this->actingAs($reporter, 'sanctum')
            ->postJson("/api/moments/{$invitation->id}/report", ['reason' => 'inappropriate'])
            ->assertCreated();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'web')
            ->put('/admin/reports/'.ContentReport::firstOrFail()->id, ['action' => 'hide_moment'])
            ->assertRedirect();

        $this->actingAs($invitation->user, 'sanctum')
            ->putJson("/api/invitations/{$invitation->id}/feed-visibility", ['is_hidden_from_feed' => false])
            ->assertUnprocessable();
        // Even with the owner's own switch on, the admin's block keeps it out of the feed.
        $invitation->update(['is_hidden_from_feed' => false]);
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/moments')->assertJsonCount(0, 'data');
        // Hiding a moment leaves the invitation itself reachable by its link.
        $this->get('/u/wira-ayu')->assertOk();
    }

    public function test_gift_payout_is_locked_while_an_invitation_is_taken_down(): void
    {
        $invitation = $this->publishedInvitation();
        $owner = $invitation->user;
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (['WGIFT-TD-1', 'WGIFT-TD-2'] as $orderId) {
            $invitation->weddingGifts()->create([
                'guest_name' => 'Tamu Baik',
                'gift_amount' => 100000,
                'service_fee' => 0,
                'total_amount' => 100000,
                'order_id' => $orderId,
                'transaction_status' => 'paid',
                'paid_at' => now(),
            ]);
        }
        $accountId = $this->actingAs($owner, 'sanctum')->postJson('/api/payout-account', [
            'bank_code' => 'BCA',
            'bank_name' => 'Bank Central Asia',
            'account_number' => '1234567890',
            'account_holder_name' => 'I Made Wira',
        ])->assertOk()->json('data.id');
        $payoutUrl = "/api/invitations/{$invitation->id}/payout-requests";
        $pending = $this->actingAs($owner, 'sanctum')
            ->postJson($payoutUrl, ['payout_account_id' => $accountId, 'amount' => 100000])
            ->assertCreated()->json('data.id');

        // An open report is flagged on the payout before any decision about the invitation.
        $reporter = User::factory()->create(['role' => 'user']);
        $this->actingAs($reporter, 'sanctum')
            ->postJson("/api/moments/{$invitation->id}/report", ['reason' => 'other'])
            ->assertCreated();
        $this->actingAs($admin, 'web')->get('/admin/payouts')->assertOk()->assertSee('1 laporan yang belum ditinjau');

        $invitation->takeDown('Undangan palsu');

        $this->actingAs($owner, 'sanctum')
            ->postJson($payoutUrl, ['payout_account_id' => $accountId, 'amount' => 100000])
            ->assertUnprocessable();
        $this->actingAs($admin, 'web')->get('/admin/payouts')->assertOk()->assertSee('Jangan ditransfer')->assertSee('Undangan palsu');
        $this->actingAs($admin, 'web')
            ->put("/admin/payouts/{$pending}", ['status' => 'paid', 'transfer_reference' => 'TRF-1'])
            ->assertSessionHasErrors('status');
        $this->assertDatabaseHas('gift_payout_requests', ['id' => $pending, 'status' => 'pending']);
        $this->actingAs($admin, 'web')
            ->put("/admin/payouts/{$pending}", ['status' => 'rejected', 'admin_note' => 'Undangan diturunkan'])
            ->assertRedirect();
        $this->assertDatabaseHas('gift_payout_requests', ['id' => $pending, 'status' => 'rejected']);

        $this->actingAs($admin, 'web')->put("/admin/invitations/{$invitation->id}/restore")->assertRedirect();
        $this->actingAs($owner, 'sanctum')
            ->postJson($payoutUrl, ['payout_account_id' => $accountId, 'amount' => 100000])
            ->assertCreated();
    }

    public function test_guest_can_report_an_invitation_from_its_public_page(): void
    {
        $invitation = $this->publishedInvitation();

        $this->get('/u/wira-ayu')->assertOk()->assertSee('/u/wira-ayu/lapor', false)->assertSee('laporkan undangan ini');
        $this->get('/preview/templates/bali-classic')->assertOk()->assertDontSee('laporkan undangan ini');
        $this->get('/u/wira-ayu/lapor')->assertOk()->assertSee('Undangan palsu atau penipuan')->assertSee('Kirim laporan');

        $this->post('/u/wira-ayu/lapor', ['reason' => 'fake', 'note' => 'Singkat'])->assertSessionHasErrors('note');
        $this->post('/u/wira-ayu/lapor', ['reason' => 'spam', 'note' => 'Acara ini tidak pernah ada.'])->assertSessionHasErrors('reason');
        // Scripts that fill the hidden field get the same answer, but nothing is stored.
        $this->post('/u/wira-ayu/lapor', ['reason' => 'fake', 'note' => 'Acara ini tidak pernah ada.', 'website' => 'http://spam.test'])
            ->assertRedirect('/u/wira-ayu/lapor');
        $this->assertSame(0, ContentReport::count());

        $this->post('/u/wira-ayu/lapor', ['reason' => 'fake', 'note' => 'Acara ini tidak pernah ada.'])
            ->assertRedirect('/u/wira-ayu/lapor')
            ->assertSessionHas('reported');
        $this->post('/u/wira-ayu/lapor', [
            'reason' => 'privacy',
            'note' => 'Nama keluarga kami dipakai tanpa izin.',
            'reporter_contact' => '081234567890',
        ])->assertRedirect('/u/wira-ayu/lapor');

        $this->assertSame(1, ContentReport::count());
        $report = ContentReport::firstOrFail();
        $this->assertNull($report->reporter_user_id);
        $this->assertSame('privacy', $report->reason);
        $this->assertSame($invitation->user_id, $report->reported_user_id);
        $this->assertSame('081234567890', $report->reporter_contact);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'web')->get('/admin/reports')
            ->assertOk()
            ->assertSee('Dilaporkan oleh tamu dari halaman undangan')
            ->assertSee('081234567890')
            ->assertSee('Nama keluarga kami dipakai tanpa izin.');
        $this->actingAs($admin, 'web')
            ->put("/admin/reports/{$report->id}", ['action' => 'take_down', 'admin_note' => 'Dikonfirmasi palsu'])
            ->assertRedirect();
        $this->app['auth']->forgetGuards();

        $this->get('/u/wira-ayu')->assertStatus(410);
        $this->get('/u/wira-ayu/lapor')->assertNotFound();
    }

    public function test_guest_reports_are_rate_limited(): void
    {
        $this->publishedInvitation();

        foreach (range(1, 5) as $attempt) {
            $this->post('/u/wira-ayu/lapor', ['reason' => 'fake', 'note' => 'Acara ini tidak pernah ada.'])->assertRedirect();
        }
        $this->post('/u/wira-ayu/lapor', ['reason' => 'fake', 'note' => 'Acara ini tidak pernah ada.'])->assertStatus(429);
        $this->get('/u/tidak-ada/lapor')->assertNotFound();
    }

    public function test_non_admin_cannot_manage_invitations(): void
    {
        $invitation = $this->publishedInvitation();
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user, 'web')->get('/admin/invitations')->assertForbidden();
        $this->actingAs($user, 'web')
            ->put("/admin/invitations/{$invitation->id}/take-down", ['takedown_note' => 'Iseng'])
            ->assertForbidden();
        $this->actingAs($user, 'web')->put("/admin/invitations/{$invitation->id}/restore")->assertForbidden();

        $this->assertFalse($invitation->fresh()->isTakenDown());
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
            'event_type' => 'Pawiwahan',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar Ubud',
            'venue_address' => 'Jalan Raya Ubud, Gianyar, Bali',
            'published_at' => now(),
        ]);
    }
}
