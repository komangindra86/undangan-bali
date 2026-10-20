<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_sign_up_and_sign_in_are_gone(): void
    {
        User::factory()->create(['email' => 'pemilik@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(410)->assertJsonPath('message', fn ($message) => str_contains($message, 'Google'));
        $this->postJson('/api/login', ['email' => 'pemilik@example.com', 'password' => 'password'])
            ->assertStatus(410)->assertJsonMissingPath('token');

        $this->assertSame(0, User::where('email', 'baru@example.com')->count());
    }
}
