<?php

namespace App\Http\Controllers;

use App\Models\ContentReport;
use App\Models\Invitation;
use App\Services\TestLabRequestDetector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicReportController extends Controller
{
    public function create(string $slug): View
    {
        return view('invitations.report', [
            'invitation' => $this->reportable($slug),
            'reasons' => ContentReport::GUEST_REASONS,
        ]);
    }

    public function store(Request $request, string $slug, TestLabRequestDetector $testLab): RedirectResponse
    {
        $invitation = $this->reportable($slug);

        $data = $request->validate([
            'reason' => ['required', Rule::in(array_keys(ContentReport::GUEST_REASONS))],
            'note' => ['required', 'string', 'min:10', 'max:500', 'not_regex:/[<>]/'],
            'reporter_contact' => ['nullable', 'string', 'max:80', 'not_regex:/[<>]/'],
        ], [
            'note.min' => 'Ceritakan singkat alasannya, minimal 10 karakter.',
            '*.not_regex' => ':attribute tidak boleh mengandung karakter < atau >.',
        ], [
            'reason' => 'alasan',
            'note' => 'penjelasan',
            'reporter_contact' => 'kontak',
        ]);

        // The "website" field is hidden from people; only scripts fill it in.
        if (! $request->filled('website') && ! $testLab->matches($request)) {
            // One open report per visitor address per invitation; sending again updates it.
            ContentReport::updateOrCreate([
                'reporter_user_id' => null,
                'reporter_ip' => $request->ip(),
                'invitation_id' => $invitation->id,
                'comment_id' => null,
                'status' => 'open',
            ], [
                'reported_user_id' => $invitation->user_id,
                'reason' => $data['reason'],
                'note' => $data['note'],
                'reporter_contact' => $data['reporter_contact'] ?? null,
            ]);
        }

        return redirect()->route('invitations.report', $slug)->with('reported', true);
    }

    private function reportable(string $slug): Invitation
    {
        $invitation = Invitation::where('slug', $slug)->where('status', 'published')->firstOrFail();
        abort_if($invitation->isTakenDown() || $invitation->isRetentionExempt(), 404);

        return $invitation;
    }
}
