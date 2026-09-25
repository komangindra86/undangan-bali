<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_wrong_passwords_are_throttled_per_email(): void
    {
        User::factory()->create(['email' => 'pemilik@example.com']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/login', ['email' => 'pemilik@example.com', 'password' => 'salah-'.$attempt])
                ->assertUnprocessable();
        }

        $this->postJson('/api/login', ['email' => 'pemilik@example.com', 'password' => 'password'])
            ->assertTooManyRequests();

        $this->postJson('/api/login', ['email' => 'lain@example.com', 'password' => 'salah'])
            ->assertUnprocessable();
    }
}
