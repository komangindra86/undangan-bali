<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /**
     * An app account with a token, answered like the old email sign-up did. Accounts are now made through Google,
     * which tests cannot reach, so this stands in for it.
     */
    protected function signUp(array $data = []): TestResponse
    {
        $user = User::factory()->create([
            'name' => $data['name'] ?? 'Pengguna Uji',
            'email' => strtolower($data['email'] ?? fake()->unique()->safeEmail()),
            'role' => 'user',
        ]);

        return TestResponse::fromBaseResponse(response()->json([
            'message' => 'Login Google berhasil.',
            'user' => $user->fresh(),
            'token' => $user->createToken('expo-mobile')->plainTextToken,
        ], 201));
    }
}
