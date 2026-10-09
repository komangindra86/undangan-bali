<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\WeddingGift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IpaymuGiftTest extends TestCase
{
    use RefreshDatabase;

    private const VA = '0000001234567890';

    private const API_KEY = 'SANDBOX-TEST-KEY';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.xendit.payment_provider' => 'ipaymu',
            'services.ipaymu.va' => self::VA,
            'services.ipaymu.api_key' => self::API_KEY,
            'services.ipaymu.sandbox' => true,
            'services.ipaymu.qris_channel' => 'mpm',
            'wedding_gift.payout_fee_percent' => 1,
        ]);
        Http::preventStrayRequests();
    }

    public function test_guest_gets_a_qris_code_for_exactly_the_gift_amount(): void
    {
        $invitation = $this->invitationWithGift();
        Http::fake([
            'https://sandbox.ipaymu.com/api/v2/payment/direct' => Http::response([
                'Status' => 200,
                // Shape taken from a real production response on 2026-10-09; QrImage is an HTML page, not an image.
                'Data' => [
                    'TransactionId' => 98765, 'SessionId' => 'WGIFT-SESSION', 'Via' => 'QRIS', 'Channel' => 'MPM',
                    'QrString' => '000201-ipaymu-qris', 'QrImage' => 'https://my.ipaymu.com/qris-basic/261009-1-98765-072710',
                    'QrTemplate' => 'https://my.ipaymu.com/qris/261009-1-98765-072710',
                    'SubTotal' => 100000, 'Total' => 100000, 'Fee' => 700, 'FeeDirection' => 'MERCHANT', 'Expired' => '2026-10-10 07:27:10',
                ],
            ]),
        ]);

        $this->get("/u/{$invitation->slug}")->assertOk()->assertSee('js/toqr.js')
            ->assertSee('nama penerima tampil sebagai')->assertSee('Dana diteruskan kepada Wira &amp; Ayu', false);

        $response = $this->postJson("/api/public/invitations/{$invitation->slug}/wedding-gift/create", [
            'guest_name' => 'Komang',
            'gift_amount' => 100000,
        ])->assertCreated()
            ->assertJsonPath('data.payment_type', 'ipaymu_qris')
            ->assertJsonPath('data.qr_string', '000201-ipaymu-qris')
            ->assertJsonPath('data.qr_image_url', null)
            ->assertJsonPath('data.service_fee', 0)
            ->assertJsonPath('data.total_amount', 100000)
            ->assertJsonPath('data.transaction_status', 'pending');

        $gift = WeddingGift::where('order_id', $response->json('data.order_id'))->firstOrFail();
        $this->assertSame('98765', $gift->midtrans_transaction_id);

        Http::assertSent(function (Request $request) use ($gift) {
            $body = $request->data();
            $expectedSignature = hash_hmac(
                'sha256',
                'POST:'.self::VA.':'.hash('sha256', $request->body()).':'.self::API_KEY,
                self::API_KEY
            );

            return $request->url() === 'https://sandbox.ipaymu.com/api/v2/payment/direct'
                && $request->header('va')[0] === self::VA
                && $request->header('signature')[0] === $expectedSignature
                && $body['paymentMethod'] === 'qris'
                && $body['paymentChannel'] === 'mpm'
                && $body['feeDirection'] === 'MERCHANT'
                && $body['amount'] === 100000
                && $body['referenceId'] === $gift->order_id
                && str_ends_with($body['notifyUrl'], '/api/ipaymu/notify')
                && $body['product'] === ["Wedding Gift Wira & Ayu (undangan #{$gift->invitation_id})"]
                && $body['qty'] === [1]
                && $body['price'] === [100000]
                && str_contains($body['comments'], '/u/undangan-wira-ayu')
                && $body['name'] === 'Komang'
                && filter_var($body['email'], FILTER_VALIDATE_EMAIL) !== false
                && $body['phone'] !== '';
        });
    }

    public function test_notification_alone_never_marks_a_gift_paid(): void
    {
        $invitation = $this->invitationWithGift();
        $gift = $this->pendingGift($invitation);
        Http::fake([
            'https://sandbox.ipaymu.com/api/v2/transaction' => Http::sequence()
                ->push(['Status' => 200, 'Data' => ['TransactionId' => 98765, 'ReferenceId' => $gift->order_id, 'Status' => 0, 'Amount' => 100000]])
                ->push(['Status' => 200, 'Data' => ['TransactionId' => 98765, 'ReferenceId' => $gift->order_id, 'Status' => 1, 'Amount' => 100000]])
                ->push(['Status' => 200, 'Data' => ['TransactionId' => 98765, 'ReferenceId' => $gift->order_id, 'Status' => 1, 'Amount' => 100000]]),
        ]);
        $claimPaid = ['trx_id' => 98765, 'reference_id' => $gift->order_id, 'status' => 'berhasil', 'status_code' => 1];

        // iPaymu itself still says pending, so the claim in the request is ignored.
        $this->post('/api/ipaymu/notify', $claimPaid)->assertOk();
        $this->assertSame('pending', $gift->fresh()->transaction_status);

        $this->post('/api/ipaymu/notify', $claimPaid)->assertOk();
        $this->assertSame('paid', $gift->fresh()->transaction_status);
        $this->assertNotNull($gift->fresh()->paid_at);

        // A repeated notification stays idempotent and notifies the owner once.
        $this->post('/api/ipaymu/notify', $claimPaid)->assertOk();
        $this->assertSame(1, $invitation->socialNotifications()->where('type', 'wedding_gift_paid')->count());
    }

    public function test_notification_for_another_reference_or_lower_amount_is_ignored(): void
    {
        $invitation = $this->invitationWithGift();
        $gift = $this->pendingGift($invitation);
        Http::fake([
            'https://sandbox.ipaymu.com/api/v2/transaction' => Http::sequence()
                ->push(['Status' => 200, 'Data' => ['TransactionId' => 555, 'ReferenceId' => 'WGIFT-OTHER', 'Status' => 1, 'Amount' => 100000]])
                ->push(['Status' => 200, 'Data' => ['TransactionId' => 98765, 'ReferenceId' => $gift->order_id, 'Status' => 1, 'Amount' => 1000]]),
        ]);

        $this->post('/api/ipaymu/notify', ['trx_id' => 555, 'reference_id' => $gift->order_id])->assertOk();
        $this->post('/api/ipaymu/notify', ['trx_id' => 98765, 'reference_id' => $gift->order_id])->assertOk();
        $this->post('/api/ipaymu/notify', ['trx_id' => 1, 'reference_id' => 'WGIFT-UNKNOWN'])->assertOk()->assertSee('IGNORED');

        $this->assertSame('pending', $gift->fresh()->transaction_status);
    }

    public function test_status_check_reads_the_result_from_ipaymu(): void
    {
        $invitation = $this->invitationWithGift();
        $gift = $this->pendingGift($invitation);
        Http::fake([
            'https://sandbox.ipaymu.com/api/v2/transaction' => Http::sequence()
                ->push(['Status' => 200, 'Data' => ['TransactionId' => 98765, 'ReferenceId' => $gift->order_id, 'Status' => 6, 'Amount' => 100000, 'SuccessDate' => '2026-10-09 07:33:56']])
                ->push(['Status' => 200, 'Data' => ['TransactionId' => 98765, 'ReferenceId' => $gift->order_id, 'Status' => -2, 'Amount' => 100000]]),
        ]);

        $this->getJson("/api/public/wedding-gift/{$gift->order_id}/status")->assertOk()
            ->assertJsonPath('data.transaction_status', 'paid');
        // iPaymu's SuccessDate is WIB; it is stored one hour later in the app's WITA clock.
        $this->assertSame('2026-10-09 08:33:56', $gift->fresh()->paid_at->format('Y-m-d H:i:s'));

        // A later "expired" answer never takes back a payment that was already confirmed.
        $this->post('/api/ipaymu/notify', ['trx_id' => 98765, 'reference_id' => $gift->order_id])->assertOk();
        $this->assertSame('paid', $gift->fresh()->transaction_status);
    }

    public function test_gateway_failure_is_reported_and_retried_without_marking_paid(): void
    {
        $invitation = $this->invitationWithGift();
        Http::fake([
            'https://sandbox.ipaymu.com/api/v2/payment/direct' => Http::response(['Status' => 401, 'Message' => 'unauthorized signature'], 401),
            'https://sandbox.ipaymu.com/api/v2/transaction' => Http::response(['Status' => 500, 'Message' => 'error'], 500),
        ]);

        $this->postJson("/api/public/invitations/{$invitation->slug}/wedding-gift/create", [
            'guest_name' => 'Komang',
            'gift_amount' => 50000,
        ])->assertStatus(502);
        $this->assertSame('failure', WeddingGift::latest('id')->firstOrFail()->transaction_status);

        $gift = $this->pendingGift($invitation);
        $this->post('/api/ipaymu/notify', ['trx_id' => 98765, 'reference_id' => $gift->order_id])->assertStatus(500);
        $this->assertSame('pending', $gift->fresh()->transaction_status);
    }

    public function test_missing_credentials_fail_cleanly(): void
    {
        config(['services.ipaymu.api_key' => null]);
        $invitation = $this->invitationWithGift();
        Http::fake();

        $this->postJson("/api/public/invitations/{$invitation->slug}/wedding-gift/create", [
            'guest_name' => 'Komang',
            'gift_amount' => 50000,
        ])->assertStatus(502);

        Http::assertNothingSent();
    }

    public function test_admin_can_trace_each_gift_to_its_invitation_and_owner(): void
    {
        $invitation = $this->invitationWithGift();
        $gift = $this->pendingGift($invitation);
        $gift->update(['transaction_status' => 'paid', 'paid_at' => now()]);
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->get('/admin/gifts')->assertOk()
            ->assertSee($gift->order_id)
            ->assertSee('Wira &amp; Ayu', false)
            ->assertSee('pemilik@example.com')
            ->assertSee('iPaymu')
            ->assertSee('ID 98765')
            ->assertSee('Rp100.000');

        $this->actingAs($admin, 'web')->get('/admin/gifts?status=pending')->assertOk()->assertDontSee($gift->order_id);
        $this->actingAs($admin, 'web')->get('/admin/gifts?status=all&q=98765')->assertOk()->assertSee($gift->order_id);
        $this->actingAs($admin, 'web')->get('/admin/gifts?status=all&q=tidak-ada')->assertOk()->assertDontSee($gift->order_id);

        $this->actingAs($invitation->user, 'web')->get('/admin/gifts')->assertForbidden();
    }

    private function invitationWithGift(): Invitation
    {
        $this->seed();
        $template = InvitationTemplate::firstOrFail();
        $token = $this->postJson('/api/register', [
            'name' => 'Pemilik',
            'email' => 'pemilik@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated()->json('token');
        $id = $this->withToken($token)->postJson('/api/invitations', [
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
            'gift_data' => [
                'is_active' => true,
                'receiver_name' => 'Wira & Ayu',
                'minimum_amount' => 10000,
                'show_amount_public' => false,
                'allow_message' => true,
            ],
        ])->assertCreated()->json('data.id');
        $this->withToken($token)->postJson("/api/invitations/{$id}/publish")->assertOk();
        $this->app['auth']->forgetGuards();

        return Invitation::findOrFail($id);
    }

    private function pendingGift(Invitation $invitation): WeddingGift
    {
        return $invitation->weddingGifts()->create([
            'guest_name' => 'Komang',
            'gift_amount' => 100000,
            'service_fee' => 0,
            'total_amount' => 100000,
            'order_id' => "WGIFT-{$invitation->id}-TEST-IPAYMU",
            'midtrans_transaction_id' => '98765',
            'payment_type' => 'ipaymu_qris',
            'transaction_status' => 'pending',
        ]);
    }
}
