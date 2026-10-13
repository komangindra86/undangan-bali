<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        $status = $request->string('status')->toString() ?: 'open';
        $reports = ContentReport::query()
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->with(['reporter:id,name,email', 'reportedUser:id,name,email', 'invitation', 'comment'])
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'activeStatus' => $status,
            'openCount' => ContentReport::where('status', 'open')->count(),
        ]);
    }

    public function update(Request $request, ContentReport $report): RedirectResponse
    {
        $this->ensureAdmin($request);
        abort_unless($report->status === 'open', 422, 'Laporan ini sudah ditangani.');

        $data = $request->validate([
            'action' => ['required', Rule::in(['hide_comment', 'hide_moment', 'take_down', 'dismiss'])],
            'admin_note' => ['nullable', 'required_if:action,take_down', 'string', 'max:500'],
        ], [
            'admin_note.required_if' => 'Isi catatan admin sebagai alasan menurunkan undangan.',
        ]);
        abort_if($data['action'] === 'hide_comment' && ! $report->comment_id, 422, 'Laporan ini bukan untuk komentar.');

        DB::transaction(function () use ($report, $data) {
            if ($data['action'] === 'hide_comment') {
                $report->comment?->update(['deleted_at' => now()]);
            }
            if ($data['action'] === 'hide_moment') {
                // feed_blocked_at keeps it out of the feed even if the owner switches the feed back on.
                $report->invitation?->forceFill(['is_hidden_from_feed' => true, 'feed_blocked_at' => now()])->save();
            }
            if ($data['action'] === 'take_down' && ! $report->invitation?->isTakenDown()) {
                $report->invitation?->takeDown($data['admin_note']);
            }

            // Close every open report about the same item so the queue does not repeat it.
            ContentReport::where('status', 'open')
                ->where('invitation_id', $report->invitation_id)
                ->when(
                    in_array($data['action'], ['hide_moment', 'take_down'], true),
                    fn ($query) => $query,
                    fn ($query) => $query->where('comment_id', $report->comment_id)
                )
                ->update([
                    'status' => $data['action'] === 'dismiss' ? 'dismissed' : 'resolved',
                    'admin_note' => $data['admin_note'] ?? null,
                    'resolved_at' => now(),
                ]);
        });

        return back()->with('message', 'Laporan berhasil ditangani.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user('web')?->isAdmin(), 403);
    }
}
