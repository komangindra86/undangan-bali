<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use App\Services\TestLabRequestDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PushTokenController extends Controller
{
    public function store(Request $request, TestLabRequestDetector $testLab): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_:\-]+$/'],
            'platform' => ['required', Rule::in(['android', 'ios'])],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:30'],
        ], [
            'token.regex' => 'Format push token tidak valid.',
        ]);

        if ($testLab->matches($request)) {
            return response()->json([
                'message' => 'Token perangkat pengujian tidak disimpan.',
                'data' => ['id' => null],
                'test_lab' => true,
            ]);
        }

        $pushToken = PushToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'],
                'device_name' => $data['device_name'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'last_seen_at' => now(),
                'disabled_at' => null,
                'last_error' => null,
            ]
        );

        return response()->json([
            'message' => 'Perangkat siap menerima notifikasi.',
            'data' => ['id' => $pushToken->id],
        ]);
    }

    public function destroy(Request $request, TestLabRequestDetector $testLab): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        if ($testLab->matches($request)) {
            return response()->json(['message' => 'Token perangkat pengujian tidak disimpan.', 'test_lab' => true]);
        }

        $request->user()->pushTokens()->where('token', $data['token'])->delete();

        return response()->json(['message' => 'Push token perangkat dihapus.']);
    }
}
