<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WeddingGift;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeddingGiftController extends Controller
{
    private const STATUSES = ['paid', 'pending', 'expired', 'failure', 'cancelled', 'refunded'];

    /**
     * Every gift transaction with its invitation and owner, for matching against the payment gateway's records.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user('web')?->isAdmin(), 403);

        $status = $request->string('status')->toString() ?: 'paid';
        $search = trim($request->string('q')->toString());

        $gifts = WeddingGift::query()
            ->when(in_array($status, self::STATUSES, true), fn ($query) => $query->where('transaction_status', $status))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where('order_id', 'like', $like)
                    ->orWhere('midtrans_transaction_id', 'like', $like)
                    ->orWhere('guest_name', 'like', $like)
                    ->orWhereHas('invitation', fn ($invitation) => $invitation
                        ->where('slug', 'like', $like)
                        ->orWhere('groom_nickname', 'like', $like)
                        ->orWhere('bride_nickname', 'like', $like)
                        ->orWhere('celebrant_nickname', 'like', $like));
            }))
            ->with(['invitation.user:id,name,email'])
            ->latest('id')
            ->paginate(40)
            ->withQueryString();

        $paid = WeddingGift::where('transaction_status', 'paid');

        return view('admin.gifts.index', [
            'gifts' => $gifts,
            'activeStatus' => $status,
            'search' => $search,
            'summary' => [
                'paid_amount' => (clone $paid)->sum('gift_amount'),
                'paid_count' => (clone $paid)->count(),
                'paid_today' => (clone $paid)->whereDate('paid_at', today())->sum('gift_amount'),
                'pending_count' => WeddingGift::where('transaction_status', 'pending')->count(),
            ],
        ]);
    }
}
