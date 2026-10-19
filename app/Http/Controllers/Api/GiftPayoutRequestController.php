<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGiftPayoutRequest as StoreRequest;
use App\Models\GiftPayoutAccount;
use App\Models\Invitation;
use App\Services\GiftPayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GiftPayoutRequestController extends Controller
{
    public function index(Request $request, Invitation $invitation): JsonResponse
    {
        $this->ensureOwner($request, $invitation);

        return response()->json([
            'data' => $invitation->payoutRequests()->with('payoutAccount')->latest('requested_at')->get(),
        ]);
    }

    public function store(StoreRequest $request, Invitation $invitation, GiftPayoutService $payouts): JsonResponse
    {
        $this->ensureOwner($request, $invitation);
        abort_if($invitation->isTakenDown(), 422, 'Pencairan ditahan karena undangan ini diturunkan oleh admin. Hubungi admin bila ini keliru.');
        $account = GiftPayoutAccount::where('id', $request->integer('payout_account_id'))
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $payout = $payouts->createRequest($request->user(), $invitation, $account, $request->integer('amount'));

        return response()->json([
            'message' => 'Pengajuan pencairan berhasil dikirim. Admin akan memeriksa rekening dan melakukan transfer.',
            'data' => $payout,
        ], 201);
    }

    /** The fee and the amount left for a payout the owner is about to request; each gift keeps its own rate. */
    public function quote(Request $request, Invitation $invitation, GiftPayoutService $payouts): JsonResponse
    {
        $this->ensureOwner($request, $invitation);
        $amount = (int) $request->validate(['amount' => ['required', 'integer', 'min:1']])['amount'];

        return response()->json(['data' => $payouts->quote($invitation, $amount)]);
    }

    private function ensureOwner(Request $request, Invitation $invitation): void
    {
        abort_unless($invitation->user_id === $request->user()->id, 404);
    }
}
