<?php

namespace App\Services;

use App\Models\GiftPayoutAccount;
use App\Models\GiftPayoutItem;
use App\Models\GiftPayoutRequest;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GiftPayoutService
{
    public function summary(Invitation $invitation): array
    {
        $totalPaid = (int) $invitation->weddingGifts()
            ->where('transaction_status', 'paid')
            ->sum('gift_amount');
        $reserved = (int) $invitation->payoutRequests()
            ->whereIn('status', GiftPayoutRequest::RESERVED_STATUSES)
            ->sum('amount');

        return [
            'available_balance' => max($totalPaid - $reserved, 0),
            'payout_pending' => (int) $invitation->payoutRequests()
                ->whereIn('status', ['pending', 'approved', 'processing'])
                ->sum('amount'),
            'paid_out' => (int) $invitation->payoutRequests()
                ->where('status', 'paid')
                ->sum('amount'),
            'payout_minimum_amount' => (int) config('wedding_gift.payout_minimum_amount'),
        ];
    }

    public function createRequest(User $user, Invitation $invitation, GiftPayoutAccount $account, int $amount): GiftPayoutRequest
    {
        return DB::transaction(function () use ($user, $invitation, $account, $amount) {
            $gifts = $invitation->weddingGifts()
                ->where('transaction_status', 'paid')
                ->oldest('paid_at')
                ->lockForUpdate()
                ->get();

            $reservedByGift = GiftPayoutItem::query()
                ->whereIn('wedding_gift_id', $gifts->pluck('id'))
                ->whereHas('payoutRequest', fn ($query) => $query->whereIn('status', GiftPayoutRequest::RESERVED_STATUSES))
                ->selectRaw('wedding_gift_id, SUM(amount) as reserved')
                ->groupBy('wedding_gift_id')
                ->pluck('reserved', 'wedding_gift_id');

            $available = $gifts->sum(fn ($gift) => max($gift->gift_amount - (int) ($reservedByGift[$gift->id] ?? 0), 0));

            if ($amount > $available) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo tersedia tidak cukup untuk pencairan ini.',
                ]);
            }

            $payout = GiftPayoutRequest::create([
                'user_id' => $user->id,
                'invitation_id' => $invitation->id,
                'payout_account_id' => $account->id,
                'bank_code' => $account->bank_code,
                'bank_name' => $account->bank_name,
                'account_number' => $account->account_number,
                'account_holder_name' => $account->account_holder_name,
                'amount' => $amount,
                'status' => 'pending',
                'requested_at' => now(),
            ]);
            $remaining = $amount;

            foreach ($gifts as $gift) {
                if ($remaining === 0) {
                    break;
                }
                $giftAvailable = max($gift->gift_amount - (int) ($reservedByGift[$gift->id] ?? 0), 0);
                $allocated = min($remaining, $giftAvailable);
                if ($allocated > 0) {
                    $payout->items()->create([
                        'wedding_gift_id' => $gift->id,
                        'amount' => $allocated,
                    ]);
                    $remaining -= $allocated;
                }
            }

            return $payout->load('payoutAccount');
        });
    }
}
