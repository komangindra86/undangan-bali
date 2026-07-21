<?php

namespace App\Services;

use App\Models\PushToken;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class FirebasePushService
{
    private ?array $credentials = null;

    public function sendToUser(int $userId, string $title, string $body, array $data = []): void
    {
        $accessToken = $this->accessToken();
        $projectId = $this->projectId();
        $url = sprintf(config('services.firebase.send_url'), $projectId);

        PushToken::query()
            ->where('user_id', $userId)
            ->whereNull('disabled_at')
            ->eachById(function (PushToken $token) use ($accessToken, $url, $title, $body, $data) {
                $response = Http::acceptJson()
                    ->asJson()
                    ->withToken($accessToken)
                    ->timeout(15)
                    ->post($url, [
                        'message' => [
                            'token' => $token->token,
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'data' => $this->stringifyData($data),
                            'android' => [
                                'priority' => 'HIGH',
                                'ttl' => '86400s',
                                'notification' => [
                                    'channel_id' => 'social',
                                    'sound' => 'default',
                                    'icon' => 'notification_icon',
                                    'color' => '#c59b50',
                                ],
                            ],
                        ],
                    ]);

                if ($response->successful()) {
                    $token->update([
                        'last_message_id' => $response->json('name'),
                        'last_error' => null,
                    ]);

                    return;
                }

                if ($response->status() === 429 || $response->serverError()) {
                    $response->throw();
                }

                $this->recordError($token, $response);
            });
    }

    protected function accessToken(): string
    {
        $credentials = $this->credentials();
        $cacheKey = 'firebase-access-token:'.sha1($credentials['client_email'].'|'.$this->projectId());

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($credentials) {
            $now = time();
            $tokenUri = $credentials['token_uri'] ?? config('services.firebase.token_uri');
            $assertion = $this->signedJwt([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ], $credentials['private_key']);

            $response = Http::asForm()->acceptJson()->timeout(15)->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ])->throw();

            $accessToken = $response->json('access_token');
            if (! is_string($accessToken) || $accessToken === '') {
                throw new RuntimeException('Firebase tidak mengembalikan OAuth access token.');
            }

            return $accessToken;
        });
    }

    private function signedJwt(array $claims, string $privateKey): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR));
        $unsignedToken = $header.'.'.$payload;

        if (! openssl_sign($unsignedToken, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Firebase service account private key tidak valid.');
        }

        return $unsignedToken.'.'.$this->base64UrlEncode($signature);
    }

    private function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $path = config('services.firebase.credentials');
        if (! is_string($path) || $path === '' || ! is_file($path)) {
            throw new RuntimeException('File Firebase service account belum dikonfigurasi.');
        }

        $credentials = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        foreach (['project_id', 'client_email', 'private_key'] as $key) {
            if (empty($credentials[$key])) {
                throw new RuntimeException("Firebase service account tidak memiliki {$key}.");
            }
        }

        return $this->credentials = $credentials;
    }

    protected function projectId(): string
    {
        return config('services.firebase.project_id') ?: $this->credentials()['project_id'];
    }

    private function stringifyData(array $data): array
    {
        return collect($data)->mapWithKeys(function ($value, $key) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_THROW_ON_ERROR);
            } elseif ($value === null) {
                $value = '';
            }

            return [(string) $key => (string) $value];
        })->all();
    }

    private function recordError(PushToken $token, Response $response): void
    {
        $fcmDetail = collect($response->json('error.details', []))
            ->first(fn ($detail) => isset($detail['errorCode']));
        $errorCode = $fcmDetail['errorCode']
            ?? $response->json('error.status')
            ?? 'FCM_ERROR';
        $message = $response->json('error.message', 'Firebase menolak push notification.');

        $token->update([
            'last_error' => Str::limit($errorCode.': '.$message, 255, ''),
            'disabled_at' => $errorCode === 'UNREGISTERED' ? now() : $token->disabled_at,
        ]);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
