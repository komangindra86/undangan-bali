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
    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'name' => trim(preg_replace('/\s+/', ' ', (string) $request->input('name'))),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/^[\pL\s.\'-]+$/u'],
            'email' => ['required', 'email:rfc', 'regex:/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, petik, atau tanda hubung.',
            'email.regex' => 'Email harus valid, contoh nama@gmail.com.',
        ], [
            'name' => 'nama',
            'email' => 'email',
            'password' => 'password',
        ]);

        $user = User::create($data + ['role' => 'user']);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => $user,
            'token' => $user->createToken('expo-mobile')->plainTextToken,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'regex:/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i'],
            'password' => ['required', 'string'],
        ], [
            'email.regex' => 'Email harus valid, contoh nama@gmail.com.',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak sesuai.'],
            ]);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $user,
            'token' => $user->createToken('expo-mobile')->plainTextToken,
        ]);
    }

    public function google(Request $request, GoogleAuthService $google): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $googleUser = $google->verifiedUser($data['id_token']);
        $user = User::where('email', $googleUser['email'])->first();

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
