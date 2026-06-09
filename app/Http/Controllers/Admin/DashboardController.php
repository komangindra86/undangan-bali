<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftPayoutRequest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\InvitationView;
use App\Models\User;
use App\Models\WeddingGift;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user('web')?->isAdmin(), 403);

        $published = Invitation::query()->where('status', 'published');
        $today = today();

        return view('admin.dashboard', [
            'summary' => [
                'users' => User::where('role', 'user')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'invitations' => Invitation::count(),
                'drafts' => Invitation::where('status', 'draft')->count(),
                'published' => (clone $published)->count(),
                'live' => (clone $published)->whereDate('event_date', '>=', $today)->count(),
                'expired' => (clone $published)->whereDate('event_date', '<', $today)->count(),
                'views' => InvitationView::count(),
                'gift_paid' => WeddingGift::where('transaction_status', 'paid')->sum('gift_amount'),
                'payout_pending' => GiftPayoutRequest::whereIn('status', ['pending', 'approved', 'processing'])->sum('amount'),
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
        ]);
    }
}
