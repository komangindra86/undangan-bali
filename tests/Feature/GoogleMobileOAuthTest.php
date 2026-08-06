<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleMobileOAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_ids' => [],
            'services.google.web_client_id' => 'new-web-client.apps.googleusercontent.com',
            'services.google.web_client_secret' => 'test-client-secret',
            'services.google.web_redirect_uri' => 'https://undangan.example/auth/google/mobile/callback',
            'services.google.mobile_redirect_uri' => 'undanganbali://auth/google',
        ]);
    }

    public function test_mobile_oauth_redirect_uses_web_client_and_stores_one_time_state(): void
    {
        $response = $this->get('/auth/google/mobile');

        $response->assertRedirect();
        $query = $this->queryFromUrl($response->headers->get('Location'));

        $this->assertSame('new-web-client.apps.googleusercontent.com', $query['client_id']);
        $this->assertSame('https://undangan.example/auth/google/mobile/callback', $query['redirect_uri']);
        $this->assertSame('code', $query['response_type']);
        $this->assertSame(64, strlen($query['state']));
    }

    public function test_callback_creates_user_and_code_can_only_be_exchanged_once(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'id_token' => 'verified-web-id-token',
            ]),
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'new-web-client.apps.googleusercontent.com',
                'sub' => 'google-web-user-1',
                'email' => 'ayu@example.com',
                'email_verified' => true,
                'name' => 'Ayu Lestari',
            ]),
        ]);

        $start = $this->get('/auth/google/mobile');
        $state = $this->queryFromUrl($start->headers->get('Location'))['state'];

        $callback = $this->get('/auth/google/mobile/callback?'.http_build_query([
            'state' => $state,
            'code' => 'google-authorization-code',
        ]));

        $callback->assertRedirect();
        $location = $callback->headers->get('Location');
        $this->assertStringStartsWith('undanganbali://auth/google?', $location);
        $exchangeCode = $this->queryFromUrl($location)['code'];

        $this->postJson('/api/auth/google/exchange', ['code' => $exchangeCode])
            ->assertOk()
            ->assertJsonPath('user.email', 'ayu@example.com')
            ->assertJsonStructure(['token']);

        $this->postJson('/api/auth/google/exchange', ['code' => $exchangeCode])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->assertDatabaseHas('users', [
            'email' => 'ayu@example.com',
            'role' => 'user',
        ]);
    }

    public function test_callback_rejects_unknown_or_replayed_state(): void
    {
        $callback = $this->get('/auth/google/mobile/callback?'.http_build_query([
            'state' => str_repeat('x', 64),
            'code' => 'unused-code',
        ]));

        $callback->assertRedirect('undanganbali://auth/google?error=invalid_state');

        $this->get('/auth/google/mobile/callback?state=short&code=unused-code')
            ->assertRedirect('undanganbali://auth/google?error=invalid_state');

        Http::assertNothingSent();
    }

    public function test_callback_does_not_allow_admin_account(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'id_token' => 'verified-admin-id-token',
            ]),
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'new-web-client.apps.googleusercontent.com',
                'sub' => 'google-admin',
                'email' => 'admin@example.com',
                'email_verified' => true,
                'name' => 'Admin',
            ]),
        ]);

        $start = $this->get('/auth/google/mobile');
        $state = $this->queryFromUrl($start->headers->get('Location'))['state'];

        $this->get('/auth/google/mobile/callback?'.http_build_query([
            'state' => $state,
            'code' => 'admin-code',
        ]))->assertRedirect('undanganbali://auth/google?error=account_not_allowed');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_google_cancellation_returns_to_mobile_without_login_code(): void
    {
        $this->get('/auth/google/mobile/callback?error=access_denied')
            ->assertRedirect('undanganbali://auth/google?error=cancelled');

        Http::assertNothingSent();
    }

    private function queryFromUrl(string $url): array
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return $query;
    }
}
