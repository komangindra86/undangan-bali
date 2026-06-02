<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGiftPayoutAccountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GiftPayoutAccountController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->payoutAccount]);
    }

    public function store(StoreGiftPayoutAccountRequest $request): JsonResponse
    {
        $account = $request->user()->payoutAccount()->updateOrCreate([], [
            ...$request->validated(),
            'is_verified' => false,
        ]);

        return response()->json([
            'message' => 'Rekening pencairan berhasil disimpan dan menunggu verifikasi admin.',
            'data' => $account,
        ]);
    }
}
