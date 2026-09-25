<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftPayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_can_save_bank_account_and_cannot_claim_same_balance_twice(): void
    {
        [$invitation, $token] = $this->publishedInvitation();
        $this->paidGift($invitation, 'WGIFT-PAYOUT-1', 100000);

        $account = $this->withToken($token)->postJson('/api/payout-account', [
            'bank_code' => 'BCA',
            'bank_name' => 'Bank Central Asia',
            'account_number' => '1234567890',
            'account_holder_name' => 'I Made Wira',
        ])->assertOk()
            ->assertJsonPath('data.is_verified', false)
            ->json('data');

        $this->withToken($token)->getJson("/api/invitations/{$invitation->id}/gifts")
            ->assertOk()
            ->assertJsonPath('summary.available_balance', 100000)
            ->assertJsonPath('summary.payout_minimum_amount', 50000);

        $request = $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/payout-requests", [
            'payout_account_id' => $account['id'],
            'amount' => 75000,
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.platform_fee', 750)
            ->assertJsonPath('data.net_amount', 74250)
            ->assertJsonPath('data.account_number', '1234567890');

        $this->assertDatabaseHas('gift_payout_items', [
            'payout_request_id' => $request->json('data.id'),
            'amount' => 75000,
        ]);

        $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/payout-requests", [
            'payout_account_id' => $account['id'],
            'amount' => 50000,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('amount');

        $this->withToken($token)->getJson("/api/invitations/{$invitation->id}/gifts")
            ->assertOk()
            ->assertJsonPath('summary.available_balance', 25000)
            ->assertJsonPath('summary.payout_pending', 74250)
            ->assertJsonPath('summary.platform_fee_pending', 750)
            ->assertJsonPath('summary.payout_fee_percent', 1);
    }

    public function test_admin_can_reject_or_complete_manual_payout_and_balance_updates(): void
    {
        [$invitation, $token] = $this->publishedInvitation();
        $this->paidGift($invitation, 'WGIFT-PAYOUT-2', 150000);
        $accountId = $this->withToken($token)->postJson('/api/payout-account', [
            'bank_code' => 'BNI',
            'bank_name' => 'Bank Negara Indonesia',
            'account_number' => '9988776655',
            'account_holder_name' => 'Ni Putu Ayu',
        ])->assertOk()->json('data.id');

        $rejected = $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/payout-requests", [
            'payout_account_id' => $accountId,
            'amount' => 50000,
        ])->assertCreated()->json('data.id');
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->get('/admin/payout')
            ->assertOk()
            ->assertSee('Dashboard Pencairan Wedding Gift')
            ->assertSee('Rekening tujuan transfer')
            ->assertSee('9988776655')
            ->assertSee('Ni Putu Ayu');

        $this->actingAs($admin, 'web')->put("/admin/payouts/{$rejected}", [
            'status' => 'rejected',
            'admin_note' => 'Nomor rekening perlu diperbarui.',
        ])->assertRedirect();
        auth('web')->logout();

        $this->withToken($token)->getJson("/api/invitations/{$invitation->id}/gifts")
            ->assertOk()
            ->assertJsonPath('summary.available_balance', 150000)
            ->assertJsonPath('summary.payout_pending', 0);

        $paid = $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/payout-requests", [
            'payout_account_id' => $accountId,
            'amount' => 100000,
        ])->assertCreated()->json('data.id');

        $this->actingAs($admin, 'web')->put("/admin/payouts/{$paid}", [
            'status' => 'paid',
            'transfer_reference' => 'TRX-BNI-001',
            'admin_note' => 'Transfer selesai.',
        ])->assertRedirect();
        auth('web')->logout();

        $this->assertDatabaseHas('gift_payout_requests', [
            'id' => $paid,
            'status' => 'paid',
            'transfer_reference' => 'TRX-BNI-001',
        ]);
        $this->assertDatabaseHas('gift_payout_accounts', ['id' => $accountId, 'is_verified' => true]);
        $this->withToken($token)->getJson("/api/invitations/{$invitation->id}/gifts")
            ->assertOk()
            ->assertJsonPath('summary.available_balance', 50000)
            ->assertJsonPath('summary.paid_out', 99000)
            ->assertJsonPath('summary.platform_fee_paid', 1000);
    }

    public function test_only_admin_can_open_payout_processing_page(): void
    {
        [$invitation] = $this->publishedInvitation();
        $user = $invitation->user;

        $this->get('/admin/payout')->assertRedirect('/login');
        $this->get('/login')->assertRedirect('/admin/login');

        $this->actingAs($user, 'web')->get('/admin/payouts')->assertForbidden();
        auth('web')->logout();
        $this->get('/admin/login')->assertOk()->assertSee('Masuk Admin');
        $this->post('/admin/login', [
            'email' => 'admin@undanganbali.test',
            'password' => 'password',
        ])->assertRedirect('/admin/payout');
    }

    public function test_admin_dashboard_shows_invitation_usage_summary(): void
    {
        [$invitation, $token] = $this->publishedInvitation();
        $template = $invitation->template;
        $user = $invitation->user;
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->paidGift($invitation, 'WGIFT-DASHBOARD-FEE', 150000);
        $accountId = $this->withToken($token)->postJson('/api/payout-account', [
            'bank_code' => 'BCA',
            'bank_name' => 'Bank Central Asia',
            'account_number' => '1234567890',
            'account_holder_name' => 'Pasangan Gift',
        ])->assertOk()->json('data.id');
        $payoutId = $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/payout-requests", [
            'payout_account_id' => $accountId,
            'amount' => 100000,
        ])->assertCreated()->json('data.id');
        $this->actingAs($admin, 'web')->put("/admin/payouts/{$payoutId}", [
            'status' => 'paid',
            'transfer_reference' => 'TRX-DASHBOARD-001',
        ])->assertRedirect();

        Invitation::create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'slug' => 'undangan-sudah-lewat',
            'status' => 'published',
            'groom_full_name' => 'I Komang Lama',
            'groom_nickname' => 'Komang',
            'bride_full_name' => 'Ni Kadek Lama',
            'bride_nickname' => 'Kadek',
            'event_type' => 'Pawiwahan',
            'event_date' => now()->subDay()->toDateString(),
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Gianyar, Bali',
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($admin, 'web')->get('/admin')
            ->assertOk()
            ->assertSee('Ringkasan Aplikasi')
            ->assertSee('Pengguna pasangan')
            ->assertSee('Undangan live')
            ->assertSee('Undangan sudah lewat')
            ->assertSee('Pencairan Gift')
            ->assertSee('Klik untuk melihat undangan live')
            ->assertSee($invitation->public_url, false)
            ->assertSee('Laporan Penghasilan Platform')
            ->assertSee('Total fee earned')
            ->assertSee('Rp1.000')
            ->assertSee('Fee platform terbaru');

        $this->actingAs($admin, 'web')->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Template populer');
    }

    public function test_admin_is_warned_when_a_pending_payout_contains_a_refunded_gift(): void
    {
        [$invitation, $token] = $this->publishedInvitation();
        $this->paidGift($invitation, 'WGIFT-REFUND-1', 80000);
        $accountId = $this->withToken($token)->postJson('/api/payout-account', [
            'bank_code' => 'BNI',
            'bank_name' => 'Bank Negara Indonesia',
            'account_number' => '9988776655',
            'account_holder_name' => 'Ni Putu Ayu',
        ])->assertOk()->json('data.id');
        $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/payout-requests", [
            'payout_account_id' => $accountId,
            'amount' => 80000,
        ])->assertCreated();

        $invitation->weddingGifts()->update(['transaction_status' => 'refunded']);

        $this->actingAs(User::where('role', 'admin')->firstOrFail(), 'web')
            ->get('/admin/payouts')
            ->assertOk()
            ->assertSee('sudah di-refund ke tamu')
            ->assertSee('Tolak pengajuan ini');
    }

    public function test_republishing_an_edited_invitation_does_not_count_template_usage_again(): void
    {
        [$invitation, $token] = $this->publishedInvitation();
        $usage = $invitation->template()->value('usage_count');

        $this->withToken($token)->putJson("/api/invitations/{$invitation->id}", [
            'template_id' => $invitation->template_id,
            'groom_full_name' => 'I Made Wira',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu',
            'bride_nickname' => 'Ayu Lestari',
            'event_type' => 'Pawiwahan',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Ubud, Bali',
        ])->assertOk();
        $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/publish")->assertOk();

        $this->assertSame($usage, $invitation->template()->value('usage_count'));
    }

    private function publishedInvitation(): array
    {
        $this->seed();
        $template = InvitationTemplate::firstOrFail();
        $register = $this->postJson('/api/register', [
            'name' => 'Pasangan Gift',
            'email' => 'payout@example.com',
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
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Ubud, Bali',
        ])->assertCreated();
        $invitation = Invitation::findOrFail($draft->json('data.id'));
        $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/publish")->assertOk();

        return [$invitation->fresh(), $token];
    }

    private function paidGift(Invitation $invitation, string $orderId, int $amount): void
    {
        $invitation->weddingGifts()->create([
            'guest_name' => 'Tamu Baik',
            'gift_amount' => $amount,
            'service_fee' => 0,
            'total_amount' => $amount,
            'order_id' => $orderId,
            'transaction_status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
