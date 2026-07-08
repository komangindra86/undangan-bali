<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class GoogleAuthService
{
    public function verifiedUser(string $idToken): array
    {
        $allowedAudiences = $this->allowedAudiences();

        if ($allowedAudiences === []) {
            throw ValidationException::withMessages([
                'id_token' => 'Google Login belum dikonfigurasi di server.',
            ]);
        }

        $response = Http::acceptJson()
            ->timeout(10)
            ->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

        if (! $response->ok()) {
            throw ValidationException::withMessages([
                'id_token' => 'Token Google tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        $payload = $response->json();

        if (! in_array($payload['aud'] ?? null, $allowedAudiences, true)) {
            throw ValidationException::withMessages([
                'id_token' => 'Token Google tidak cocok dengan aplikasi ini.',
            ]);
        }

        if (! filter_var($payload['email'] ?? null, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'id_token' => 'Akun Google tidak memiliki email valid.',
            ]);
        }

        if (! in_array($payload['email_verified'] ?? false, [true, 'true', '1', 1], true)) {
            throw ValidationException::withMessages([
                'id_token' => 'Email Google belum terverifikasi.',
            ]);
        }

        return [
            'google_id' => (string) ($payload['sub'] ?? ''),
            'email' => strtolower((string) $payload['email']),
            'name' => trim((string) ($payload['name'] ?? explode('@', (string) $payload['email'])[0])),
        ];
    }

    private function allowedAudiences(): array
    {
        $ids = config('services.google.client_ids', []);

        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        return array_values(array_filter(array_map('trim', $ids)));
    }
}
