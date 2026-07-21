<?php

namespace Tests\Feature;

use App\Jobs\SendFirebasePushNotification;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\PushToken;
use App\Models\User;
use App\Services\FirebasePushService;
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
        $token = 'fcm_registration_token:Test-device_123';

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

    public function test_legacy_expo_token_cannot_be_registered_again(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson('/api/push-tokens', [
            'token' => 'ExponentPushToken[Legacy-device_123]',
            'platform' => 'android',
        ])->assertUnprocessable()->assertJsonValidationErrors('token');
    }

    public function test_invitation_request_queues_push_for_the_owner(): void
    {
        Queue::fake();
        $invitation = $this->publishedInvitation();
        PushToken::create([
            'user_id' => $invitation->user_id,
            'token' => 'fcm_owner_token:Owner-device_123',
            'platform' => 'android',
        ]);

        $this->postJson('/api/moments/'.$invitation->id.'/request-invitation', [
            'requester_name' => 'Komang Tamu',
            'requester_whatsapp' => '081234567890',
        ])->assertCreated();

        Queue::assertPushed(SendFirebasePushNotification::class, fn ($job) => $job->userId === $invitation->user_id
            && $job->invitationId === $invitation->id
            && $job->type === 'invitation_request'
        );
    }

    public function test_fcm_message_is_sent_and_message_id_is_saved(): void
    {
        Http::fake([
            'https://fcm.googleapis.com/v1/projects/test-project/messages:send' => Http::response([
                'name' => 'projects/test-project/messages/message-id-123',
            ]),
        ]);
        $user = User::factory()->create();
        $pushToken = PushToken::create([
            'user_id' => $user->id,
            'token' => 'fcm_receipt_token:Receipt-device_123',
            'platform' => 'android',
        ]);

        $this->firebaseService()->sendToUser($user->id, 'Judul', 'Isi', [
            'screen' => 'Notifications',
            'invitation_id' => 123,
        ]);

        $this->assertDatabaseHas('push_tokens', [
            'id' => $pushToken->id,
            'last_message_id' => 'projects/test-project/messages/message-id-123',
            'last_error' => null,
        ]);
        Http::assertSent(fn ($request) => $request->url() === 'https://fcm.googleapis.com/v1/projects/test-project/messages:send'
            && $request->hasHeader('Authorization', 'Bearer test-access-token')
            && $request['message']['token'] === $pushToken->token
            && $request['message']['data']['invitation_id'] === '123'
            && $request['message']['android']['notification']['channel_id'] === 'social'
        );
    }

    public function test_unregistered_fcm_device_disables_token(): void
    {
        Http::fake([
            'https://fcm.googleapis.com/v1/projects/test-project/messages:send' => Http::response([
                'error' => [
                    'code' => 404,
                    'status' => 'NOT_FOUND',
                    'message' => 'Requested entity was not found.',
                    'details' => [[
                        '@type' => 'type.googleapis.com/google.firebase.fcm.v1.FcmError',
                        'errorCode' => 'UNREGISTERED',
                    ]],
                ],
            ], 404),
        ]);
        $user = User::factory()->create();
        $pushToken = PushToken::create([
            'user_id' => $user->id,
            'token' => 'fcm_old_token:Old-device_123',
            'platform' => 'android',
        ]);

        $this->firebaseService()->sendToUser($user->id, 'Judul', 'Isi');

        $this->assertNotNull($pushToken->fresh()->disabled_at);
        $this->assertStringContainsString('UNREGISTERED', $pushToken->fresh()->last_error);
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

    private function firebaseService(): FirebasePushService
    {
        config('services.firebase.send_url', 'https://fcm.googleapis.com/v1/projects/%s/messages:send');

        return new class extends FirebasePushService
        {
            protected function accessToken(): string
            {
                return 'test-access-token';
            }

            protected function projectId(): string
            {
                return 'test-project';
            }
        };
    }
}
