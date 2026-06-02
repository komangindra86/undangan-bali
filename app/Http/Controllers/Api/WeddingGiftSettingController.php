<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWeddingGiftSettingRequest;
use App\Models\Invitation;
use App\Models\WeddingGiftSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeddingGiftSettingController extends Controller
{
    public function show(Request $request, Invitation $invitation): JsonResponse
    {
        $this->ensureOwner($request, $invitation);

        return response()->json(['data' => $invitation->giftSetting ?: $this->defaults($invitation)]);
    }

    public function store(UpdateWeddingGiftSettingRequest $request, Invitation $invitation): JsonResponse
    {
        $this->ensureOwner($request, $invitation);

        if ($request->boolean('is_active') && $invitation->status !== 'published') {
            return response()->json([
                'message' => 'Publish undangan terlebih dahulu sebelum mengaktifkan Wedding Gift.',
            ], 422);
        }

        $setting = $invitation->giftSetting()->updateOrCreate([], [
            ...$request->validated(),
            'fee_type' => config('wedding_gift.fee.type'),
            'fee_value' => config('wedding_gift.fee.value'),
        ]);

        return response()->json([
            'message' => 'Pengaturan Wedding Gift berhasil disimpan.',
            'data' => $setting,
        ]);
    }

    private function defaults(Invitation $invitation): WeddingGiftSetting
    {
        return new WeddingGiftSetting([
            'invitation_id' => $invitation->id,
            'is_active' => false,
            'fee_type' => config('wedding_gift.fee.type'),
            'fee_value' => config('wedding_gift.fee.value'),
            'minimum_amount' => config('wedding_gift.minimum_amount'),
            'show_amount_public' => false,
            'allow_message' => true,
        ]);
    }

    private function ensureOwner(Request $request, Invitation $invitation): void
    {
        abort_unless($invitation->user_id === $request->user()->id, 404);
    }
}
