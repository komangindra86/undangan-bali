<?php

namespace App\Services;

use App\Models\GiftPayoutAccount;
use App\Models\GiftPayoutItem;
use App\Models\GiftPayoutRequest;
use App\Models\Invitation;
use App\Models\User;
use App\Models\WeddingGift;
use App\Support\GiftFee;
use Illuminate\Support\Carbon;
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
                ->sum('net_amount'),
            'payout_pending_gross' => (int) $invitation->payoutRequests()
                ->whereIn('status', ['pending', 'approved', 'processing'])
                ->sum('amount'),
            'paid_out' => (int) $invitation->payoutRequests()
                ->where('status', 'paid')
                ->sum('net_amount'),
            'platform_fee_pending' => (int) $invitation->payoutRequests()
                ->whereIn('status', ['pending', 'approved', 'processing'])
                ->sum('platform_fee'),
            'platform_fee_paid' => (int) $invitation->payoutRequests()
                ->where('status', 'paid')
                ->sum('platform_fee'),
            'payout_minimum_amount' => (int) config('wedding_gift.payout_minimum_amount'),
            ...GiftFee::toArray(),
        ];
    }

    /** What a payout of this amount would cost and leave, without reserving anything. */
    public function quote(Invitation $invitation, int $amount): array
    {
        $allocations = $this->allocate($invitation, $amount, lock: false);
        $fee = $this->feeFor($allocations, $amount);

        return ['amount' => $amount, 'platform_fee' => $fee, 'net_amount' => $amount - $fee];
    }

    public function createRequest(User $user, Invitation $invitation, GiftPayoutAccount $account, int $amount): GiftPayoutRequest
    {
        return DB::transaction(function () use ($user, $invitation, $account, $amount) {
            $payout = $this->store($invitation, $amount, [
                'user_id' => $user->id,
                'payout_account_id' => $account->id,
                'bank_code' => $account->bank_code,
                'bank_name' => $account->bank_name,
                'account_number' => $account->account_number,
                'account_holder_name' => $account->account_holder_name,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            return $payout->load('payoutAccount');
        });
    }

    /**
     * A transfer the admin already made to a custom order's customer: recorded as a finished payout, so it takes its
     * gifts out of the balance and carries the same fee as any other payout.
     */
    public function recordTransfer(User $admin, Invitation $invitation, array $bank, int $amount, Carbon $transferredAt, string $proofPath, ?string $reference): GiftPayoutRequest
    {
        return DB::transaction(fn () => $this->store($invitation, $amount, [
            'user_id' => $admin->id,
            'payout_account_id' => null,
            'bank_code' => null,
            'bank_name' => $bank['bank_name'],
            'account_number' => $bank['account_number'],
            'account_holder_name' => $bank['account_holder_name'],
            'status' => 'paid',
            'transfer_reference' => $reference,
            'proof_path' => $proofPath,
            'requested_at' => now(),
            'processed_at' => now(),
            'paid_at' => $transferredAt,
        ]));
    }

    private function store(Invitation $invitation, int $amount, array $attributes): GiftPayoutRequest
    {
        $allocations = $this->allocate($invitation, $amount, lock: true);
        $platformFee = $this->feeFor($allocations, $amount);

        $payout = GiftPayoutRequest::create([
            ...$attributes,
            'invitation_id' => $invitation->id,
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'net_amount' => $amount - $platformFee,
        ]);
        foreach ($allocations as $allocation) {
            $payout->items()->create([
                'wedding_gift_id' => $allocation['gift']->id,
                'amount' => $allocation['amount'],
            ]);
        }

        return $payout;
    }

    /**
     * Spreads the amount over the invitation's paid gifts that are still free, oldest first.
     *
     * @return list<array{gift: WeddingGift, amount: int}>
     */
    private function allocate(Invitation $invitation, int $amount, bool $lock): array
    {
        $gifts = $invitation->weddingGifts()
            ->where('transaction_status', 'paid')
            ->oldest('paid_at')
            ->oldest('id')
            ->when($lock, fn ($query) => $query->lockForUpdate())
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

        $allocations = [];
        $remaining = $amount;
        foreach ($gifts as $gift) {
            if ($remaining === 0) {
                break;
            }
            $allocated = min($remaining, max($gift->gift_amount - (int) ($reservedByGift[$gift->id] ?? 0), 0));
            if ($allocated > 0) {
                $allocations[] = ['gift' => $gift, 'amount' => $allocated];
                $remaining -= $allocated;
            }
        }

        return $allocations;
    }

    /** Each part of the payout is charged the rate of the gift it comes from. */
    private function feeFor(array $allocations, int $amount): int
    {
        $fee = array_sum(array_map(
            fn (array $allocation) => $allocation['amount'] * GiftFee::percentFor($allocation['gift']->paid_at) / 100,
            $allocations
        ));

        return min((int) ceil(round($fee, 6)), $amount);
    }
}
