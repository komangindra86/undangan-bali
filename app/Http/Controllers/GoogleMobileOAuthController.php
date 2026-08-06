<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleMobileOAuthController extends Controller
{
    private const STATE_TTL_MINUTES = 10;

    private const EXCHANGE_TTL_MINUTES = 2;

    public function redirect(): RedirectResponse
    {
        $this->ensureConfigured();

        $state = Str::random(64);
        Cache::put($this->stateKey($state), true, now()->addMinutes(self::STATE_TTL_MINUTES));

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.web_client_id'),
            'redirect_uri' => config('services.google.web_redirect_uri'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986));
    }

    public function callback(Request $request, GoogleAuthService $google): RedirectResponse
    {
        if ($request->filled('error')) {
            return $this->redirectToMobile(['error' => 'cancelled']);
        }

        $state = $request->string('state')->toString();
        $authorizationCode = $request->string('code')->toString();

        if (strlen($state) !== 64 || $authorizationCode === '' || strlen($authorizationCode) > 4096) {
            return $this->redirectToMobile(['error' => 'invalid_state']);
        }

        if (! Cache::pull($this->stateKey($state))) {
            return $this->redirectToMobile(['error' => 'invalid_state']);
        }

        try {
            $this->ensureConfigured();
            $tokenResponse = Http::asForm()->acceptJson()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.web_client_id'),
                'client_secret' => config('services.google.web_client_secret'),
                'code' => $authorizationCode,
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('services.google.web_redirect_uri'),
            ]);

            if (! $tokenResponse->ok() || ! is_string($tokenResponse->json('id_token'))) {
                return $this->redirectToMobile(['error' => 'token_exchange_failed']);
            }

            $googleUser = $google->verifiedUser($tokenResponse->json('id_token'));
            $user = $this->resolveUser($googleUser);
        } catch (ValidationException) {
            return $this->redirectToMobile(['error' => 'account_not_allowed']);
        } catch (\Throwable $exception) {
            report($exception);

            return $this->redirectToMobile(['error' => 'server_error']);
        }

        $exchangeCode = Str::random(64);
        Cache::put($this->exchangeKey($exchangeCode), $user->id, now()->addMinutes(self::EXCHANGE_TTL_MINUTES));

        return $this->redirectToMobile(['code' => $exchangeCode]);
    }

    public function exchange(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:64'],
        ]);

        $userId = Cache::pull($this->exchangeKey($data['code']));
        $user = $userId ? User::find($userId) : null;

        if (! $user || $user->isAdmin()) {
            throw ValidationException::withMessages([
                'code' => 'Kode login Google tidak valid atau sudah kedaluwarsa. Silakan masuk kembali.',
            ]);
        }

        return response()->json([
            'message' => 'Login Google berhasil.',
            'user' => $user,
            'token' => $user->createToken('expo-mobile')->plainTextToken,
        ]);
    }

    private function resolveUser(array $googleUser): User
    {
        $user = User::where('email', $googleUser['email'])->first();

        if ($user?->isAdmin()) {
            throw ValidationException::withMessages([
                'id_token' => 'Akun admin tidak dapat masuk dari aplikasi mobile.',
            ]);
        }

        return $user ?? User::create([
            'name' => $googleUser['name'] ?: 'User Google',
            'email' => $googleUser['email'],
            'password' => Hash::make(Str::password(32)),
            'role' => 'user',
        ]);
    }

    private function ensureConfigured(): void
    {
        abort_unless(
            config('services.google.web_client_id') &&
            config('services.google.web_client_secret') &&
            config('services.google.web_redirect_uri'),
            503,
            'Google Login belum dikonfigurasi.'
        );
    }

    private function redirectToMobile(array $query): RedirectResponse
    {
        return redirect()->away(config('services.google.mobile_redirect_uri').'?'.http_build_query(
            $query,
            '',
            '&',
            PHP_QUERY_RFC3986
        ));
    }

    private function stateKey(string $state): string
    {
        return 'google-mobile-oauth-state:'.hash('sha256', $state);
    }

    private function exchangeKey(string $code): string
    {
        return 'google-mobile-oauth-exchange:'.hash('sha256', $code);
    }
}
