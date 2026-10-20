<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Older app versions still show the email forms; tell them where to go instead. */
    public function register(): JsonResponse
    {
        return response()->json(['message' => 'Pendaftaran dengan email sudah tidak tersedia. Silakan daftar dengan tombol Masuk dengan Google.'], 410);
    }

    public function login(): JsonResponse
    {
        return response()->json(['message' => 'Masuk dengan email sudah tidak tersedia. Silakan gunakan tombol Masuk dengan Google dengan email yang sama.'], 410);
    }

    public function google(Request $request, GoogleAuthService $google): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $googleUser = $google->verifiedUser($data['id_token']);
        $user = User::where('email', $googleUser['email'])->whereNull('account_deleted_at')->first();

        if ($user?->isAdmin()) {
            throw ValidationException::withMessages([
                'id_token' => 'Akun admin tidak dapat masuk dari aplikasi mobile.',
            ]);
        }

        $user ??= User::create([
            'name' => $googleUser['name'] ?: 'User Google',
            'email' => $googleUser['email'],
            'password' => Hash::make(Str::password(32)),
            'role' => 'user',
        ]);

        return response()->json([
            'message' => 'Login Google berhasil.',
            'user' => $user,
            'token' => $user->createToken('expo-mobile')->plainTextToken,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
