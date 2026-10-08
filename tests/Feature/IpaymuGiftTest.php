<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
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
                'Data' => ['TransactionId' => 98765, 'QrString' => '000201-ipaymu-qris', 'Total' => 100000, 'Fee' => 700],
            ]),
        ]);

        $this->get("/u/{$invitation->slug}")->assertOk()->assertSee('js/toqr.js');

        $response = $this->postJson("/api/public/invitations/{$invitation->slug}/wedding-gift/create", [
            'guest_name' => 'Komang',
            'gift_amount' => 100000,
        ])->assertCreated()
            ->assertJsonPath('data.payment_type', 'ipaymu_qris')
            ->assertJsonPath('data.qr_string', '000201-ipaymu-qris')
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
                ->push(['Status' => 200, 'Data' => ['TransactionId' => 98765, 'ReferenceId' => $gift->order_id, 'Status' => 6, 'Amount' => 100000]])
                ->push(['Status' => 200, 'Data' => ['TransactionId' => 98765, 'ReferenceId' => $gift->order_id, 'Status' => -2, 'Amount' => 100000]]),
        ]);

        $this->getJson("/api/public/wedding-gift/{$gift->order_id}/status")->assertOk()
            ->assertJsonPath('data.transaction_status', 'paid');

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
