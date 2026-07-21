<?php

namespace Tests\Feature;

use App\Jobs\CheckExpoPushReceipts;
use App\Jobs\SendExpoPushNotification;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\PushToken;
use App\Models\User;
use App\Services\ExpoPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_register_and_remove_a_device_token(): void
    {
        $user = User::factory()->create();
        $token = 'ExponentPushToken[Test_device-123]';

        $this->actingAs($user, 'sanctum')->postJson('/api/push-tokens', [
            'token' => $token,
            'platform' => 'android',
            'device_name' => 'Samsung Test',
            'app_version' => '1.0.11',
        ])->assertOk();

        $this->assertDatabaseHas('push_tokens', [
            'user_id' => $user->id,
            'token' => $token,
            'platform' => 'android',
            'disabled_at' => null,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/push-tokens', ['token' => $token])
            ->assertOk();

        $this->assertDatabaseMissing('push_tokens', ['token' => $token]);
    }

    public function test_invitation_request_queues_push_for_the_owner(): void
    {
        Queue::fake();
        $invitation = $this->publishedInvitation();
        PushToken::create([
            'user_id' => $invitation->user_id,
            'token' => 'ExponentPushToken[Owner_device-123]',
            'platform' => 'android',
        ]);

        $this->postJson('/api/moments/'.$invitation->id.'/request-invitation', [
            'requester_name' => 'Komang Tamu',
            'requester_whatsapp' => '081234567890',
        ])->assertCreated();

        Queue::assertPushed(SendExpoPushNotification::class, fn ($job) => $job->userId === $invitation->user_id
            && $job->invitationId === $invitation->id
            && $job->type === 'invitation_request'
        );
    }

    public function test_expo_ticket_is_saved_and_receipt_check_is_queued(): void
    {
        Queue::fake();
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response([
                'data' => [['status' => 'ok', 'id' => 'ticket-id-123']],
            ]),
        ]);
        $user = User::factory()->create();
        $pushToken = PushToken::create([
            'user_id' => $user->id,
            'token' => 'ExponentPushToken[Receipt_device-123]',
            'platform' => 'android',
        ]);

        app(ExpoPushService::class)->sendToUser($user->id, 'Judul', 'Isi', [
            'screen' => 'Notifications',
        ]);

        $this->assertDatabaseHas('push_tokens', [
            'id' => $pushToken->id,
            'last_ticket_id' => 'ticket-id-123',
            'last_error' => null,
        ]);
        Queue::assertPushed(CheckExpoPushReceipts::class, fn ($job) => ($job->receipts['ticket-id-123'] ?? null) === $pushToken->id
        );
        Http::assertSent(fn ($request) => $request->url() === 'https://exp.host/--/api/v2/push/send'
            && $request[0]['to'] === $pushToken->token
            && $request[0]['channelId'] === 'social'
        );
    }

    public function test_unregistered_device_ticket_disables_token(): void
    {
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response([
                'data' => [[
                    'status' => 'error',
                    'message' => 'Device is not registered.',
                    'details' => ['error' => 'DeviceNotRegistered'],
                ]],
            ]),
        ]);
        $user = User::factory()->create();
        $pushToken = PushToken::create([
            'user_id' => $user->id,
            'token' => 'ExponentPushToken[Old_device-123]',
            'platform' => 'android',
        ]);

        app(ExpoPushService::class)->sendToUser($user->id, 'Judul', 'Isi');

        $this->assertNotNull($pushToken->fresh()->disabled_at);
        $this->assertStringContainsString('DeviceNotRegistered', $pushToken->fresh()->last_error);
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
            'slug' => 'wira-ayu-push',
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
