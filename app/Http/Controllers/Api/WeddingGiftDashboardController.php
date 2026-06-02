<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\GiftPayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeddingGiftDashboardController extends Controller
{
    public function index(Request $request, Invitation $invitation, GiftPayoutService $payouts): JsonResponse
    {
        abort_unless($invitation->user_id === $request->user()->id, 404);

        $paid = $invitation->weddingGifts()->where('transaction_status', 'paid');
        $gifts = $invitation->weddingGifts()->latest()->paginate(30);

        return response()->json([
            'summary' => [
                'total_gift_paid' => (int) (clone $paid)->sum('gift_amount'),
                'total_service_fee' => (int) (clone $paid)->sum('service_fee'),
                'giver_count' => (clone $paid)->count(),
                ...$payouts->summary($invitation),
            ],
            'data' => $gifts->items(),
            'payout_account' => $request->user()->payoutAccount,
            'payout_requests' => $invitation->payoutRequests()->with('payoutAccount')->latest('requested_at')->limit(10)->get(),
            'meta' => [
                'current_page' => $gifts->currentPage(),
                'last_page' => $gifts->lastPage(),
            ],
        ]);
    }
}
