<?php

namespace Tests\Feature;

use App\Models\GiftPayoutRequest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GiftFeeAndTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_gift_keeps_one_percent_until_a_new_rate_is_set(): void
    {
        [$invitation, $owner] = $this->invitation($this->user());
        $this->gift($invitation, 100000, now()->subDay());

        $this->getJson('/api/gift-fee')->assertOk()
            ->assertJsonPath('data.payout_fee_percent', 1)
            ->assertJsonPath('data.payout_fee_new_from', null);
        $this->actingAs($owner, 'sanctum')->getJson("/api/invitations/{$invitation->id}/payout-quote?amount=100000")
            ->assertOk()->assertJsonPath('data.platform_fee', 1000)->assertJsonPath('data.net_amount', 99000);
        $this->get('/terms-of-service')->assertSee('Biaya platform 1% dipotong');
    }

    public function test_gifts_paid_from_the_change_carry_the_new_rate_and_older_ones_keep_theirs(): void
    {
        config(['wedding_gift.payout_fee_new_from' => now()->subHours(2)->timezone('Asia/Makassar')->format('Y-m-d H:i:s')]);
        [$invitation, $owner] = $this->invitation($this->user());
        $this->gift($invitation, 100000, now()->subDays(3), 'WGIFT-OLD');
        $this->gift($invitation, 50000, now()->subHour(), 'WGIFT-NEW');

        $this->getJson('/api/gift-fee')->assertOk()
            ->assertJsonPath('data.payout_fee_percent', 10)
            ->assertJsonPath('data.payout_fee_old_percent', 1);
        $this->get('/terms-of-service')->assertSee('Biaya platform 10% untuk tanda kasih yang diterima sejak')->assertSee('1% untuk tanda kasih yang diterima sebelumnya');
        $this->get('/')->assertSee('Fee pencairan 10%')->assertSee('Rp90.000');

        // The oldest gift is paid out first: 100.000 at 1% and 20.000 at 10%.
        $this->actingAs($owner, 'sanctum')->getJson("/api/invitations/{$invitation->id}/payout-quote?amount=120000")
            ->assertOk()->assertJsonPath('data.platform_fee', 3000)->assertJsonPath('data.net_amount', 117000);
        $this->actingAs($owner, 'sanctum')->getJson("/api/invitations/{$invitation->id}/gifts")
            ->assertOk()->assertJsonPath('summary.payout_fee_percent', 10);

        $account = $owner->payoutAccount()->create([
            'bank_code' => 'BCA', 'bank_name' => 'BCA', 'account_number' => '1234567890', 'account_holder_name' => 'Wira',
        ]);
        $this->actingAs($owner, 'sanctum')->postJson("/api/invitations/{$invitation->id}/payout-requests", [
            'payout_account_id' => $account->id,
            'amount' => 120000,
        ])->assertCreated()->assertJsonPath('data.platform_fee', 3000)->assertJsonPath('data.net_amount', 117000);

        // What is left is the rest of the new gift, all at 10%.
        $this->actingAs($owner, 'sanctum')->getJson("/api/invitations/{$invitation->id}/payout-quote?amount=30000")
            ->assertOk()->assertJsonPath('data.platform_fee', 3000);
    }

    public function test_quotes_are_for_the_owner_only(): void
    {
        [$invitation] = $this->invitation($this->user());
        $this->gift($invitation, 100000, now());

        $this->actingAs($this->user(), 'sanctum')->getJson("/api/invitations/{$invitation->id}/payout-quote?amount=1000")->assertNotFound();
    }

    public function test_admin_records_a_transfer_to_the_customer_and_the_customer_sees_it(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        [$invitation] = $this->invitation($admin, [
            'monitor_token' => str_repeat('a', 40),
            'order_details' => ['customer_name' => 'Ni Kadek Sari', 'customer_whatsapp' => '0812-3456-7890', 'bank_name' => 'BCA', 'account_number' => '1234567890', 'account_holder' => 'Ni Kadek Sari'],
        ]);
        $this->gift($invitation, 150000, now()->subDay());
        $edit = "/admin/custom/{$invitation->id}/edit";

        $this->actingAs($admin, 'web')->get($edit)->assertOk()
            ->assertSee('Tanda kasih dan transfer ke pelanggan')->assertSee('Catat transfer baru')->assertSee('value="1234567890"', false);

        $payload = [
            'amount' => 150000,
            'transferred_on' => now()->toDateString(),
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder_name' => 'Ni Kadek Sari',
            'transfer_reference' => 'TRF-001',
        ];
        $this->actingAs($admin, 'web')->post("/admin/custom/{$invitation->id}/transfers", ['amount' => 200000, 'proof' => UploadedFile::fake()->image('bukti.jpg')] + $payload)
            ->assertSessionHasErrors('amount');
        $this->assertSame(0, GiftPayoutRequest::count());
        $this->assertSame([], Storage::disk('public')->allFiles('invitations/transfers'));
        $this->actingAs($admin, 'web')->post("/admin/custom/{$invitation->id}/transfers", $payload)->assertSessionHasErrors('proof');

        $response = $this->actingAs($admin, 'web')->post("/admin/custom/{$invitation->id}/transfers", $payload + ['proof' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertRedirect($edit);
        $whatsapp = session('transfer_whatsapp');
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $whatsapp);
        $this->assertStringContainsString(rawurlencode('Rp148.500'), $whatsapp);
        $this->assertStringContainsString(rawurlencode('/pantau/'.str_repeat('a', 40)), $whatsapp);

        $transfer = GiftPayoutRequest::firstOrFail();
        $this->assertSame('paid', $transfer->status);
        $this->assertNull($transfer->payout_account_id);
        $this->assertSame(1500, $transfer->platform_fee);
        $this->assertSame(148500, $transfer->net_amount);
        Storage::disk('public')->assertExists($transfer->proof_path);

        $this->actingAs($admin, 'web')->get($edit)->assertSee('Tidak ada saldo yang perlu ditransfer')->assertSee('TRF-001');
        $this->app['auth']->forgetGuards();
        $this->get('/pantau/'.str_repeat('a', 40))
            ->assertOk()
            ->assertSee('Transfer ke rekening Anda')
            ->assertSee('Rp148.500')
            ->assertSee('••••7890')
            ->assertDontSee('1234567890')
            ->assertSee($transfer->proof_path);

        $this->actingAs($admin, 'web')->delete("/admin/custom/{$invitation->id}/transfers/{$transfer->id}")->assertRedirect($edit);
        $this->assertSame(0, GiftPayoutRequest::count());
        Storage::disk('public')->assertMissing($transfer->proof_path);
        $this->actingAs($admin, 'web')->get($edit)->assertSee('Catat transfer baru');
    }

    public function test_transfers_are_admin_only_and_refused_for_taken_down_or_app_made_invitations(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        [$custom] = $this->invitation($admin);
        [$appMade] = $this->invitation($this->user(), ['slug' => 'buatan-aplikasi']);
        $this->gift($custom, 100000, now(), 'WGIFT-C');
        $this->gift($appMade, 100000, now(), 'WGIFT-A');
        $payload = fn () => [
            'amount' => 100000,
            'transferred_on' => now()->toDateString(),
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder_name' => 'Ni Kadek Sari',
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ];

        $this->actingAs($this->user(), 'web')->post("/admin/custom/{$custom->id}/transfers", $payload())->assertForbidden();
        $this->actingAs($admin, 'web')->post("/admin/custom/{$appMade->id}/transfers", $payload())->assertNotFound();
        $custom->takeDown('Undangan palsu');
        $this->actingAs($admin, 'web')->post("/admin/custom/{$custom->id}/transfers", $payload())->assertStatus(422);

        $this->assertSame(0, GiftPayoutRequest::count());
    }

    private function user(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    private function invitation(User $owner, array $attributes = []): array
    {
        $template = InvitationTemplate::firstOrCreate(['slug' => 'bali-classic'], [
            'name' => 'Bali Classic',
            'blade_view' => 'invitations.templates.bali-classic',
        ]);
        // Order notes and the monitor token are not mass assignable; the admin form sets them one by one.
        $guarded = array_intersect_key($attributes, array_flip(['order_details', 'monitor_token']));
        $invitation = Invitation::create(array_diff_key($attributes, $guarded) + [
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
            'venue_address' => 'Ubud, Bali',
            'published_at' => now(),
        ]);
        $invitation->forceFill($guarded)->save();

        return [$invitation, $owner];
    }

    private function gift(Invitation $invitation, int $amount, $paidAt, string $orderId = 'WGIFT-1'): void
    {
        $invitation->weddingGifts()->create([
            'guest_name' => 'Tamu Baik',
            'gift_amount' => $amount,
            'service_fee' => 0,
            'total_amount' => $amount,
            'order_id' => $orderId,
            'transaction_status' => 'paid',
            'paid_at' => $paidAt,
        ]);
    }
}
