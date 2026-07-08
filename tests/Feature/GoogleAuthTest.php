<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.client_ids' => ['android-client-id.apps.googleusercontent.com']]);
    }

    public function test_google_login_creates_user_and_returns_mobile_token(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'android-client-id.apps.googleusercontent.com',
                'sub' => 'google-user-1',
                'email' => 'komang@gmail.com',
                'email_verified' => 'true',
                'name' => 'Komang Indra',
            ]),
        ]);

        $this->postJson('/api/auth/google', ['id_token' => 'valid-google-token'])
            ->assertOk()
            ->assertJsonPath('message', 'Login Google berhasil.')
            ->assertJsonPath('user.email', 'komang@gmail.com')
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', [
            'email' => 'komang@gmail.com',
            'name' => 'Komang Indra',
            'role' => 'user',
        ]);
    }

    public function test_google_login_uses_existing_user_email(): void
    {
        $existing = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'ayu@gmail.com',
            'role' => 'user',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'android-client-id.apps.googleusercontent.com',
                'sub' => 'google-user-2',
                'email' => 'ayu@gmail.com',
                'email_verified' => true,
                'name' => 'Nama Google',
            ]),
        ]);

        $this->postJson('/api/auth/google', ['id_token' => 'valid-google-token'])
            ->assertOk()
            ->assertJsonPath('user.id', $existing->id)
            ->assertJsonPath('user.name', 'Nama Lama');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_google_login_rejects_wrong_client_id(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'other-client-id.apps.googleusercontent.com',
                'email' => 'komang@gmail.com',
                'email_verified' => true,
                'name' => 'Komang Indra',
            ]),
        ]);

        $this->postJson('/api/auth/google', ['id_token' => 'wrong-audience-token'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_token');
    }

    public function test_google_login_rejects_invalid_token_response(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'error' => 'invalid_token',
            ], 400),
        ]);

        $this->postJson('/api/auth/google', ['id_token' => 'expired-google-token'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_token');
    }

    public function test_google_login_requires_configured_client_ids(): void
    {
        config(['services.google.client_ids' => []]);

        $this->postJson('/api/auth/google', ['id_token' => 'valid-google-token'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_token');
    }

    public function test_google_login_does_not_login_admin_email(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'android-client-id.apps.googleusercontent.com',
                'email' => 'admin@example.com',
                'email_verified' => true,
                'name' => 'Admin',
            ]),
        ]);

        $this->postJson('/api/auth/google', ['id_token' => 'admin-google-token'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_token');
    }
}
