<?php

namespace App\Services;

use App\Models\GiftPayoutRequest;
use App\Models\InvitationComment;
use App\Models\InvitationReaction;
use App\Models\InvitationRequest;
use App\Models\SocialNotification;
use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Deletes a user's account. Their invitations, photos, comments and devices go; gift and payout records stay for
 * bookkeeping, so the account row is kept without a name, email or way to sign in, and any unpaid balance lapses.
 */
class AccountDeletionService
{
    public function __construct(private GiftPayoutService $payouts) {}

    /** What deleting the account would remove, for the confirmation screen. */
    public function summary(User $user): array
    {
        $invitations = $user->invitations()->whereNull('owner_deleted_at')->get();

        return [
            'invitations' => $invitations->count(),
            'invitations_with_gifts' => $invitations->filter->hasGiftTransactions()->count(),
            'unpaid_balance' => (int) $invitations->sum(function ($invitation) {
                $summary = $this->payouts->summary($invitation);

                return $summary['available_balance'] + $summary['payout_pending_gross'];
            }),
        ];
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            foreach ($user->invitations()->whereNull('owner_deleted_at')->get() as $invitation) {
                $invitation->removeByOwner();
            }
            // Payouts still waiting are cancelled; their money lapses with the account.
            $user->payoutRequests()->whereIn('status', ['pending', 'approved', 'processing'])->get()->each(function (GiftPayoutRequest $payout) {
                $payout->update(['status' => 'rejected', 'admin_note' => 'Akun pemilik dihapus atas permintaannya.', 'processed_at' => now()]);
            });
            // Finished payouts keep their own copy of the bank details, so the saved account can go.
            $user->payoutRequests()->update(['payout_account_id' => null]);
            $user->payoutAccount()?->delete();

            InvitationComment::where('user_id', $user->id)->delete();
            InvitationReaction::where('user_id', $user->id)->delete();
            InvitationRequest::where('requester_user_id', $user->id)->delete();
            SocialNotification::where('user_id', $user->id)->delete();
            UserBlock::where('blocker_user_id', $user->id)->orWhere('blocked_user_id', $user->id)->delete();
            $user->pushTokens()->delete();
            $user->tokens()->delete();

            $user->forceFill([
                'name' => 'Akun dihapus',
                'email' => 'dihapus-'.$user->id.'-'.Str::lower(Str::random(8)).'@akun-dihapus.invalid',
                'password' => Hash::make(Str::password(40)),
                'remember_token' => null,
                'account_deleted_at' => now(),
            ])->save();
        });
    }
}
