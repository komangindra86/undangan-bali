<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftPayoutRequest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\InvitationView;
use App\Models\User;
use App\Models\WeddingGift;
use App\Models\WeddingGiftFee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user('web')?->isAdmin(), 403);

        $published = Invitation::query()->where('status', 'published');
        $today = today();
        $feeQuery = WeddingGiftFee::query();

        return view('admin.dashboard', [
            'summary' => [
                'users' => User::where('role', 'user')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'invitations' => Invitation::count(),
                'drafts' => Invitation::where('status', 'draft')->count(),
                'published' => (clone $published)->count(),
                'live' => (clone $published)->whereDate('event_date', '>=', $today)->count(),
                'expired' => (clone $published)->whereDate('event_date', '<', $today)->count(),
                'archived' => Invitation::where('status', 'archived')->count(),
                'media_deleted' => Invitation::whereNotNull('media_deleted_at')->count(),
                'views' => InvitationView::count(),
                'gift_paid' => WeddingGift::where('transaction_status', 'paid')->sum('gift_amount'),
                'payout_pending' => GiftPayoutRequest::whereIn('status', ['pending', 'approved', 'processing'])->sum('amount'),
                'platform_fee_earned' => (clone $feeQuery)->where('status', 'earned')->sum('amount'),
                'platform_fee_pending' => (clone $feeQuery)->where('status', 'pending')->sum('amount'),
                'platform_fee_refunded' => (clone $feeQuery)->where('status', 'refunded')->sum('amount'),
                'platform_fee_earned_this_month' => (clone $feeQuery)
                    ->where('status', 'earned')
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->sum('amount'),
                'platform_fee_transactions' => (clone $feeQuery)->where('status', 'earned')->count(),
            ],
            'latestInvitations' => Invitation::with(['user', 'template'])
                ->latest()
                ->limit(8)
                ->get(),
            'latestUsers' => User::where('role', 'user')
                ->latest()
                ->limit(8)
                ->get(),
            'popularTemplates' => InvitationTemplate::orderByDesc('usage_count')
                ->limit(6)
                ->get(),
            'payoutQueue' => GiftPayoutRequest::with(['user', 'invitation'])
                ->whereIn('status', ['pending', 'approved', 'processing'])
                ->latest('requested_at')
                ->limit(5)
                ->get(),
            'recentPlatformFees' => WeddingGiftFee::with(['weddingGift.invitation.user'])
                ->where('status', 'earned')
                ->latest('updated_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
