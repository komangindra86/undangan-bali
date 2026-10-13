<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentReport;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvitationController extends Controller
{
    private const FILTERS = ['all', 'published', 'reported', 'taken_down'];

    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        $filter = $request->string('filter')->toString();
        $filter = in_array($filter, self::FILTERS, true) ? $filter : 'all';
        $search = trim($request->string('q')->toString());

        $invitations = Invitation::query()
            ->withoutRetentionExemptions()
            ->when($filter === 'published', fn ($query) => $query->where('status', 'published')->whereNull('taken_down_at'))
            ->when($filter === 'reported', fn ($query) => $query->whereHas('reports', fn ($reports) => $reports->where('status', 'open')))
            ->when($filter === 'taken_down', fn ($query) => $query->whereNotNull('taken_down_at'))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';
                $query->where(function ($query) use ($like) {
                    $query->where('slug', 'like', $like);
                    foreach (['groom_full_name', 'bride_full_name', 'celebrant_full_name', 'child_full_name', 'host_name', 'venue_name'] as $column) {
                        $query->orWhere($column, 'like', $like);
                    }
                    $query->orWhereHas('user', fn ($user) => $user->where('email', 'like', $like)->orWhere('name', 'like', $like));
                });
            })
            ->with(['user:id,name,email', 'template:id,name', 'giftSetting:invitation_id,is_active'])
            ->withCount(['reports as open_reports_count' => fn ($query) => $query->where('status', 'open')])
            ->withSum(['weddingGifts as gift_paid_total' => fn ($query) => $query->where('transaction_status', 'paid')], 'gift_amount')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.invitations.index', [
            'invitations' => $invitations,
            'activeFilter' => $filter,
            'search' => $search,
            'takenDownCount' => Invitation::whereNotNull('taken_down_at')->count(),
        ]);
    }

    public function takeDown(Request $request, Invitation $invitation): RedirectResponse
    {
        $this->ensureAdmin($request);
        abort_if($invitation->isTakenDown(), 422, 'Undangan ini sudah diturunkan.');

        $data = $request->validate([
            'takedown_note' => ['required', 'string', 'max:500'],
        ], [], ['takedown_note' => 'alasan']);

        DB::transaction(function () use ($invitation, $data) {
            $invitation->takeDown($data['takedown_note']);

            ContentReport::where('status', 'open')
                ->where('invitation_id', $invitation->id)
                ->update([
                    'status' => 'resolved',
                    'admin_note' => $data['takedown_note'],
                    'resolved_at' => now(),
                ]);
        });

        return back()->with('message', 'Undangan diturunkan. Link publik, gift, dan feed-nya sudah tidak aktif.');
    }

    public function restore(Request $request, Invitation $invitation): RedirectResponse
    {
        $this->ensureAdmin($request);
        abort_unless($invitation->isTakenDown(), 422, 'Undangan ini tidak sedang diturunkan.');

        $invitation->forceFill(['taken_down_at' => null, 'takedown_note' => null])->save();

        return back()->with('message', 'Undangan dipulihkan dan dapat dibuka kembali.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user('web')?->isAdmin(), 403);
    }
}
