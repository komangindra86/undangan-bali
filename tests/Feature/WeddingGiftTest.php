<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\WeddingGift;
use App\Models\WeddingGiftSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeddingGiftTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.midtrans.server_key' => 'SB-Mid-server-sandbox-key',
            'services.midtrans.is_production' => false,
            'services.xendit.payment_provider' => 'midtrans',
            'wedding_gift.fee.type' => 'flat',
            'wedding_gift.fee.value' => 2000,
        ]);
    }

    public function test_owner_can_enable_gift_and_guest_can_create_qris_with_transparent_fee(): void
    {
        [$invitation, $token] = $this->publishedInvitation();

        $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/gift-setting", [
            'is_active' => true,
            'receiver_name' => 'Wira dan Ayu',
            'receiver_note' => 'Matur suksma.',
            'minimum_amount' => 10000,
            'show_amount_public' => false,
            'allow_message' => true,
            'fee_type' => 'percent',
            'fee_value' => 99,
        ])->assertOk()
            ->assertJsonPath('data.fee_type', 'flat')
            ->assertJsonPath('data.fee_value', '2000.00');

        $this->get("/u/{$invitation->slug}")
            ->assertOk()
            ->assertSee('Wedding Gift')
            ->assertSee('Buat QRIS untuk Bayar')
            ->assertSee('Biaya Layanan');

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'transaction_id' => 'midtrans-qris-1',
                'transaction_status' => 'pending',
                'qr_string' => '000201-qris',
                'actions' => [
                    ['name' => 'generate-qr-code', 'url' => 'https://api.sandbox.midtrans.com/qris/qr.png'],
                ],
            ]),
        ]);

        $response = $this->postJson("/api/public/invitations/{$invitation->slug}/wedding-gift/create", [
            'guest_name' => 'Komang',
            'guest_phone' => '08123456789',
            'gift_amount' => 100000,
            'message' => 'Selamat berbahagia.',
        ])->assertCreated()
            ->assertJsonPath('data.gift_amount', 100000)
            ->assertJsonPath('data.service_fee', 2000)
            ->assertJsonPath('data.total_amount', 102000)
            ->assertJsonPath('data.transaction_status', 'pending');

        $orderId = $response->json('data.order_id');
        $this->assertStringStartsWith("WGIFT-{$invitation->id}-", $orderId);
        $this->assertDatabaseHas('wedding_gifts', [
            'order_id' => $orderId,
            'gift_amount' => 100000,
            'service_fee' => 2000,
            'total_amount' => 102000,
            'transaction_status' => 'pending',
        ]);
        $this->assertDatabaseHas('wedding_gift_fees', ['amount' => 2000, 'status' => 'pending']);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.sandbox.midtrans.com/v2/charge'
            && $request['transaction_details']['gross_amount'] === 102000
            && $request['custom_field1'] === (string) $invitation->id
            && $request['custom_field2'] === '100000'
            && $request['custom_field3'] === '2000');

        $this->postJson("/api/public/invitations/{$invitation->slug}/wedding-gift/create", [
            'guest_name' => 'Kadek',
            'guest_phone' => '08987654321',
            'gift_amount' => 150000,
            'message' => 'Rahajeng.',
        ])->assertCreated()
            ->assertJsonPath('data.gift_amount', 150000)
            ->assertJsonPath('data.service_fee', 3000)
            ->assertJsonPath('data.total_amount', 153000);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.sandbox.midtrans.com/v2/charge'
            && $request['transaction_details']['gross_amount'] === 153000
            && $request['custom_field2'] === '150000'
            && $request['custom_field3'] === '3000');
    }

    public function test_service_fee_uses_flat_then_percent_tier(): void
    {
        $setting = new WeddingGiftSetting;

        $this->assertSame(2000, $setting->serviceFeeFor(50000));
        $this->assertSame(2000, $setting->serviceFeeFor(100000));
        $this->assertSame(3000, $setting->serviceFeeFor(150000));
    }

    public function test_gift_selected_in_mobile_draft_is_available_after_publish(): void
    {
        $this->seed();
        $template = InvitationTemplate::firstOrFail();
        $register = $this->postJson('/api/register', [
            'name' => 'Draft Gift',
            'email' => 'draft-gift@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();
        $token = $register->json('token');

        $draft = $this->withToken($token)->postJson('/api/invitations/sync-local-draft', [
            'template_id' => $template->id,
            'groom_full_name' => 'I Made Wira',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => '2026-08-18',
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Ubud, Bali',
            'gift_data' => [
                'is_active' => true,
                'receiver_name' => 'Wira & Ayu',
                'receiver_note' => 'Matur suksma.',
                'minimum_amount' => 25000,
                'show_amount_public' => false,
                'allow_message' => true,
                'fee_type' => 'percent',
                'fee_value' => 100,
            ],
        ])->assertCreated()
            ->assertJsonPath('data.gift_setting.is_active', true)
            ->assertJsonPath('data.gift_setting.fee_type', 'flat')
            ->assertJsonPath('data.gift_setting.fee_value', '2000.00');

        $invitationId = $draft->json('data.id');
        $published = $this->withToken($token)->postJson("/api/invitations/{$invitationId}/publish")
            ->assertOk();

        $this->get('/u/'.$published->json('data.slug'))
            ->assertOk()
            ->assertSee('Wedding Gift')
            ->assertSee('Minimal Rp25.000');
    }

    public function test_webhook_requires_valid_signature_and_paid_update_is_idempotent(): void
    {
        [$invitation, $token] = $this->publishedInvitation();
        $gift = $this->pendingGift($invitation);
        $payload = [
            'order_id' => $gift->order_id,
            'status_code' => '200',
            'gross_amount' => '102000.00',
            'transaction_id' => 'transaction-paid',
            'payment_type' => 'qris',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ];

        $this->postJson('/api/midtrans/webhook', $payload + ['signature_key' => 'invalid'])
            ->assertForbidden();
        $this->assertDatabaseHas('wedding_gifts', ['id' => $gift->id, 'transaction_status' => 'pending']);

        $payload['signature_key'] = $this->signature($payload);
        $this->postJson('/api/midtrans/webhook', $payload)
            ->assertOk()
            ->assertJsonPath('transaction_status', 'paid');
        $this->postJson('/api/midtrans/webhook', $payload)
            ->assertOk()
            ->assertJsonPath('transaction_status', 'paid');

        $this->assertDatabaseHas('wedding_gifts', ['id' => $gift->id, 'transaction_status' => 'paid']);
        $this->assertDatabaseHas('wedding_gift_fees', ['wedding_gift_id' => $gift->id, 'status' => 'earned']);
        $this->assertDatabaseCount('wedding_gift_fees', 1);
        $this->withToken($token)->getJson("/api/invitations/{$invitation->id}/gifts")
            ->assertOk()
            ->assertJsonPath('summary.total_gift_paid', 100000)
            ->assertJsonPath('summary.total_service_fee', 2000)
            ->assertJsonPath('summary.giver_count', 1);
    }

    public function test_public_status_check_refreshes_pending_transaction_from_midtrans_api(): void
    {
        [$invitation] = $this->publishedInvitation();
        $gift = $this->pendingGift($invitation);

        Http::fake([
            "https://api.sandbox.midtrans.com/v2/{$gift->order_id}/status" => Http::response([
                'order_id' => $gift->order_id,
                'gross_amount' => '102000.00',
                'transaction_id' => 'status-paid',
                'transaction_status' => 'settlement',
                'payment_type' => 'qris',
                'settlement_time' => '2026-05-27 14:00:00',
            ]),
        ]);

        $this->getJson("/api/public/wedding-gift/{$gift->order_id}/status")
            ->assertOk()
            ->assertJsonPath('data.transaction_status', 'paid');

        $this->assertDatabaseHas('wedding_gifts', ['id' => $gift->id, 'transaction_status' => 'paid']);
        $this->assertDatabaseHas('wedding_gift_fees', ['wedding_gift_id' => $gift->id, 'status' => 'earned']);
    }

    public function test_xendit_provider_creates_qris_only_invoice(): void
    {
        config([
            'services.xendit.payment_provider' => 'xendit',
            'services.xendit.secret_key' => 'test-secret-key',
        ]);
        [$invitation, $token] = $this->publishedInvitation();

        $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/gift-setting", [
            'is_active' => true,
            'receiver_name' => 'Wira dan Ayu',
            'receiver_note' => 'Matur suksma.',
            'minimum_amount' => 10000,
            'show_amount_public' => false,
            'allow_message' => true,
        ])->assertOk();

        $this->get("/u/{$invitation->slug}")
            ->assertOk()
            ->assertSee('Buat QRIS untuk Bayar')
            ->assertDontSee('Buat Link Pembayaran');

        Http::fake([
            'https://api.xendit.co/v2/invoices' => Http::response([
                'id' => 'xendit-invoice-1',
                'external_id' => "WGIFT-{$invitation->id}-20260613120000-ABC123",
                'status' => 'PENDING',
                'amount' => 102000,
                'invoice_url' => 'https://checkout.xendit.co/web/xendit-invoice-1',
            ]),
        ]);

        $response = $this->postJson("/api/public/invitations/{$invitation->slug}/wedding-gift/create", [
            'guest_name' => 'Komang',
            'guest_phone' => '08123456789',
            'gift_amount' => 100000,
            'message' => 'Selamat berbahagia.',
        ])->assertCreated()
            ->assertJsonPath('data.payment_type', 'xendit_invoice')
            ->assertJsonPath('data.payment_url', 'https://checkout.xendit.co/web/xendit-invoice-1');

        $orderId = $response->json('data.order_id');
        $this->assertDatabaseHas('wedding_gifts', [
            'order_id' => $orderId,
            'midtrans_transaction_id' => 'xendit-invoice-1',
            'payment_url' => 'https://checkout.xendit.co/web/xendit-invoice-1',
            'payment_type' => 'xendit_invoice',
            'transaction_status' => 'pending',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.xendit.co/v2/invoices'
            && $request['external_id'] === $orderId
            && $request['amount'] === 102000
            && $request['payment_methods'] === ['QRIS']
            && $request['metadata']['invitation_id'] === $invitation->id
            && $request->hasHeader('Authorization'));
    }

    public function test_public_xendit_payment_demo_uses_published_invitation_not_preview_simulation(): void
    {
        config([
            'services.xendit.payment_provider' => 'xendit',
        ]);
        $this->seed();

        $this->get('/demo/wedding-gift-xendit')
            ->assertOk()
            ->assertSee('Demo pembayaran Xendit mode tes')
            ->assertSee('Buat QRIS untuk Bayar')
            ->assertDontSee('Buat Link Pembayaran')
            ->assertSee('data-preview="0"', false);

        $invitation = Invitation::where('slug', 'demo-wedding-gift-xendit')->firstOrFail();

        $this->assertSame('published', $invitation->status);
        $this->assertTrue($invitation->giftSetting->is_active);
        $this->assertSame('Wira & Ayu', $invitation->giftSetting->receiver_name);
    }

    public function test_public_xendit_payment_demo_is_preview_only_in_production(): void
    {
        $this->seed();
        $this->app->detectEnvironment(fn () => 'production');
        config([
            'services.xendit.payment_provider' => 'xendit',
        ]);

        $this->get('/demo/wedding-gift-xendit')
            ->assertOk()
            ->assertDontSee('Demo pembayaran Xendit mode tes')
            ->assertSee('Lihat Simulasi Pembayaran')
            ->assertSee('data-preview="1"', false);
    }

    public function test_xendit_webhook_requires_callback_token_and_marks_invoice_paid(): void
    {
        config([
            'services.xendit.webhook_token' => 'callback-token-test',
        ]);
        [$invitation] = $this->publishedInvitation();
        $gift = $this->pendingGift($invitation);
        $gift->update([
            'midtrans_transaction_id' => 'xendit-invoice-1',
            'payment_type' => 'xendit_invoice',
        ]);
        $payload = [
            'id' => 'xendit-invoice-1',
            'external_id' => $gift->order_id,
            'amount' => 102000,
            'status' => 'PAID',
            'paid_at' => '2026-06-13T12:15:00.000Z',
        ];

        $this->postJson('/api/xendit/webhook', $payload, ['x-callback-token' => 'wrong'])
            ->assertForbidden();
        $this->assertDatabaseHas('wedding_gifts', ['id' => $gift->id, 'transaction_status' => 'pending']);

        $this->postJson('/api/xendit/webhook', $payload, ['x-callback-token' => 'callback-token-test'])
            ->assertOk()
            ->assertJsonPath('transaction_status', 'paid');

        $this->assertDatabaseHas('wedding_gifts', ['id' => $gift->id, 'transaction_status' => 'paid']);
        $this->assertDatabaseHas('wedding_gift_fees', ['wedding_gift_id' => $gift->id, 'status' => 'earned']);
    }

    public function test_xendit_webhook_test_payload_with_unknown_external_id_is_accepted(): void
    {
        config([
            'services.xendit.webhook_token' => 'callback-token-test',
        ]);

        $this->postJson('/api/xendit/webhook', [
            'id' => '579c8d61f23fa4ca35e52da4',
            'external_id' => 'invoice_123124123',
            'amount' => 50000,
            'status' => 'PAID',
        ], ['x-callback-token' => 'callback-token-test'])
            ->assertOk()
            ->assertJsonPath('transaction_status', 'ignored');
    }

    private function publishedInvitation(): array
    {
        $this->seed();
        $template = InvitationTemplate::firstOrFail();
        $register = $this->postJson('/api/register', [
            'name' => 'Pemilik',
            'email' => 'pemilik@example.com',
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
            'event_date' => '2026-08-18',
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar',
            'venue_address' => 'Ubud, Bali',
        ])->assertCreated();
        $invitation = Invitation::findOrFail($draft->json('data.id'));
        $this->withToken($token)->postJson("/api/invitations/{$invitation->id}/publish")->assertOk();

        return [$invitation->fresh(), $token];
    }

    private function pendingGift(Invitation $invitation): WeddingGift
    {
        $gift = $invitation->weddingGifts()->create([
            'guest_name' => 'Komang',
            'gift_amount' => 100000,
            'service_fee' => 2000,
            'total_amount' => 102000,
            'order_id' => "WGIFT-{$invitation->id}-TEST-ABC123",
            'transaction_status' => 'pending',
        ]);
        $gift->fee()->create(['amount' => 2000, 'status' => 'pending']);

        return $gift;
    }

    private function signature(array $payload): string
    {
        return hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'SB-Mid-server-sandbox-key');
    }
}
