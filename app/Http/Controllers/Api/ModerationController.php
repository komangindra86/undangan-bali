<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentReport;
use App\Models\Invitation;
use App\Models\InvitationComment;
use App\Models\User;
use App\Models\UserBlock;
use App\Services\TestLabRequestDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModerationController extends Controller
{
    public function reportMoment(Request $request, Invitation $invitation, TestLabRequestDetector $testLab): JsonResponse
    {
        abort_unless($invitation->status === 'published', 404);

        return $this->storeReport($request, $invitation, null, $invitation->user_id, $testLab);
    }

    public function reportComment(Request $request, Invitation $invitation, InvitationComment $comment, TestLabRequestDetector $testLab): JsonResponse
    {
        abort_unless($comment->invitation_id === $invitation->id && $comment->deleted_at === null, 404);

        return $this->storeReport($request, $invitation, $comment, $comment->user_id, $testLab);
    }

    public function blockedUsers(Request $request): JsonResponse
    {
        $blocks = UserBlock::with('blocked:id,name')
            ->where('blocker_user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $blocks->filter(fn ($block) => $block->blocked)->map(fn ($block) => [
                'id' => $block->blocked->id,
                'name' => $block->blocked->name,
                'blocked_at' => $block->created_at->toISOString(),
            ])->values(),
        ]);
    }

    public function block(Request $request, User $user): JsonResponse
    {
        abort_if($user->id === $request->user()->id, 422, 'Anda tidak dapat memblokir akun sendiri.');

        UserBlock::firstOrCreate([
            'blocker_user_id' => $request->user()->id,
            'blocked_user_id' => $user->id,
        ]);

        return response()->json([
            'message' => $user->name.' diblokir. Moment dan komentarnya tidak akan tampil untuk Anda.',
        ]);
    }

    public function unblock(Request $request, User $user): JsonResponse
    {
        UserBlock::where('blocker_user_id', $request->user()->id)
            ->where('blocked_user_id', $user->id)
            ->delete();

        return response()->json(['message' => 'Blokir dibuka.']);
    }

    private function storeReport(
        Request $request,
        Invitation $invitation,
        ?InvitationComment $comment,
        ?int $reportedUserId,
        TestLabRequestDetector $testLab
    ): JsonResponse {
        $data = $request->validate([
            'reason' => ['required', Rule::in(array_keys(ContentReport::REASONS))],
            'note' => ['nullable', 'string', 'max:500', 'not_regex:/[<>]/'],
        ], ['note.not_regex' => 'Catatan tidak boleh mengandung karakter < atau >.']);

        abort_if($reportedUserId === $request->user()->id, 422, 'Anda tidak dapat melaporkan konten sendiri.');

        if ($testLab->matches($request)) {
            return response()->json(['message' => 'Laporan simulasi pengujian tidak disimpan.', 'test_lab' => true]);
        }

        // One open report per person per item; repeated taps update it instead of flooding the queue.
        ContentReport::updateOrCreate([
            'reporter_user_id' => $request->user()->id,
            'invitation_id' => $invitation->id,
            'comment_id' => $comment?->id,
            'status' => 'open',
        ], [
            'reported_user_id' => $reportedUserId,
            'reason' => $data['reason'],
            'note' => $data['note'] ?? null,
        ]);

        return response()->json([
            'message' => 'Terima kasih, laporan Anda sudah dikirim dan akan ditinjau admin.',
        ], 201);
    }
}
