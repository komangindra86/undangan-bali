<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftPayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GiftPayoutController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        $status = $request->string('status')->toString();
        $payoutQuery = GiftPayoutRequest::query();
        if ($status && in_array($status, ['pending', 'approved', 'processing', 'paid', 'rejected'], true)) {
            $payoutQuery->where('status', $status);
        }

        $payouts = $payoutQuery
            ->with(['user', 'invitation', 'payoutAccount', 'items.weddingGift'])
            ->latest('requested_at')
            ->latest('id')
            ->paginate(30);

        return view('admin.payouts.index', [
            'payouts' => $payouts,
            'activeStatus' => $status,
            'summary' => [
                'pending_amount' => GiftPayoutRequest::whereIn('status', ['pending', 'approved', 'processing'])->sum('amount'),
                'pending_count' => GiftPayoutRequest::whereIn('status', ['pending', 'approved', 'processing'])->count(),
                'paid_count' => GiftPayoutRequest::where('status', 'paid')->count(),
                'paid' => GiftPayoutRequest::where('status', 'paid')->sum('amount'),
                'requests' => GiftPayoutRequest::count(),
                'today_amount' => GiftPayoutRequest::whereDate('requested_at', today())->sum('amount'),
                'today_count' => GiftPayoutRequest::whereDate('requested_at', today())->count(),
            ],
        ]);
    }

    public function update(Request $request, GiftPayoutRequest $payout): RedirectResponse
    {
        $this->ensureAdmin($request);
        abort_if(in_array($payout->status, ['paid', 'rejected'], true), 422, 'Pengajuan final tidak dapat diubah.');

        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'processing', 'paid', 'rejected'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'transfer_reference' => ['nullable', 'required_if:status,paid', 'string', 'max:255'],
        ]);

        $payout->update([
            ...$data,
            'processed_at' => now(),
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);

        if (in_array($data['status'], ['approved', 'processing', 'paid'], true)) {
            $account = $payout->payoutAccount;
            $matchesRequestedDestination = $account
                && $account->bank_code === $payout->bank_code
                && $account->bank_name === $payout->bank_name
                && $account->account_number === $payout->account_number
                && $account->account_holder_name === $payout->account_holder_name;

            if ($matchesRequestedDestination) {
                $account->update(['is_verified' => true]);
            }
        }

        return back()->with('message', 'Status pencairan berhasil diperbarui.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user('web')?->isAdmin(), 403);
    }
}
