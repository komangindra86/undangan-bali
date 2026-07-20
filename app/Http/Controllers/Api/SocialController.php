<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationMoment;
use App\Models\InvitationReaction;
use App\Models\InvitationRequest;
use App\Models\SocialNotification;
use App\Services\SocialNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SocialController extends Controller
{
    public function react(Request $request, Invitation $invitation, SocialNotificationService $notifications): JsonResponse
    {
        $this->published($invitation);
        $data = $request->validate(['type' => ['required', Rule::in(['like', 'love'])]]);
        $reaction = InvitationReaction::firstOrNew([
            'invitation_id' => $invitation->id,
            'user_id' => $request->user()->id,
        ]);
        $isNew = ! $reaction->exists;
        $reaction->type = $data['type'];
        $reaction->save();

        if ($isNew && $invitation->user_id !== $request->user()->id) {
            $notifications->send($invitation, 'reaction', [
                'actor_name' => $request->user()->name,
                'reaction' => $reaction->type,
                'message' => $request->user()->name.' memberi '.($reaction->type === 'love' ? 'love' : 'like').' pada Moment Anda.',
            ]);
        }

        return response()->json(['message' => 'Reaksi disimpan.', 'data' => ['type' => $reaction->type]]);
    }

    public function removeReaction(Request $request, Invitation $invitation): JsonResponse
    {
        InvitationReaction::where('invitation_id', $invitation->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Reaksi dihapus.']);
    }

    public function comment(Request $request, Invitation $invitation, SocialNotificationService $notifications): JsonResponse
    {
        $this->published($invitation);
        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:500', 'not_regex:/[<>]/'],
        ], ['body.not_regex' => 'Komentar tidak boleh mengandung karakter < atau >.']);
        $comment = $invitation->comments()->create([
            'user_id' => $request->user()->id,
            'body' => trim(preg_replace('/\s+/', ' ', $data['body'])),
        ]);

        if ($invitation->user_id !== $request->user()->id) {
            $notifications->send($invitation, 'comment', [
                'comment_id' => $comment->id,
                'actor_name' => $request->user()->name,
                'message' => $request->user()->name.' mengomentari Moment Anda.',
            ]);
        }

        return response()->json([
            'message' => 'Komentar terkirim.',
            'data' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => $comment->created_at->toISOString(),
                'user' => ['id' => $request->user()->id, 'name' => $request->user()->name],
            ],
        ], 201);
    }

    public function requests(Request $request, Invitation $invitation): JsonResponse
    {
        $this->owner($request, $invitation);
        $requests = $invitation->invitationRequests()->latest()->paginate(30);

        return response()->json($requests);
    }

    public function markRequestShared(Request $request, Invitation $invitation, InvitationRequest $invitationRequest): JsonResponse
    {
        $this->owner($request, $invitation);
        abort_unless($invitationRequest->invitation_id === $invitation->id, 404);

        $invitationRequest->update(['status' => 'shared', 'shared_at' => now()]);
        $message = 'Kepada Yth. '.$invitationRequest->requester_name.', kami mengundang untuk hadir di acara pernikahan kami. Buka undangan: '.route('invitations.public', $invitation->slug);

        return response()->json([
            'message' => 'Permintaan ditandai sudah dibagikan.',
            'data' => $invitationRequest->fresh(),
            'whatsapp_url' => 'https://wa.me/'.$invitationRequest->requester_whatsapp.'?text='.rawurlencode($message),
        ]);
    }

    public function invitationMoments(Request $request, Invitation $invitation): JsonResponse
    {
        $this->owner($request, $invitation);

        return response()->json(['data' => $invitation->moments()->latest('occurred_at')->latest()->get()]);
    }

    public function storeInvitationMoment(Request $request, Invitation $invitation): JsonResponse
    {
        $this->owner($request, $invitation);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100', 'not_regex:/[<>]/'],
            'body' => ['nullable', 'string', 'max:500', 'not_regex:/[<>]/'],
            'occurred_at' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);
        $data['photo_path'] = $request->hasFile('photo')
            ? $request->file('photo')->store('invitations/moments', 'public')
            : null;
        unset($data['photo']);

        $moment = $invitation->moments()->create($data);

        return response()->json(['message' => 'Moment berhasil ditambahkan.', 'data' => $moment], 201);
    }

    public function deleteInvitationMoment(Request $request, Invitation $invitation, InvitationMoment $moment): JsonResponse
    {
        $this->owner($request, $invitation);
        abort_unless($moment->invitation_id === $invitation->id, 404);
        if ($moment->photo_path) {
            Storage::disk('public')->delete($moment->photo_path);
        }
        $moment->delete();

        return response()->json(['message' => 'Moment berhasil dihapus.']);
    }

    public function setFeedVisibility(Request $request, Invitation $invitation): JsonResponse
    {
        $this->owner($request, $invitation);
        $data = $request->validate(['is_hidden_from_feed' => ['required', 'boolean']]);
        $invitation->update($data);

        return response()->json([
            'message' => $invitation->is_hidden_from_feed ? 'Undangan disembunyikan dari feed.' : 'Undangan kembali tampil di feed.',
            'data' => ['is_hidden_from_feed' => $invitation->is_hidden_from_feed],
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $notifications = $request->user()->socialNotifications()->latest()->paginate(30);

        return response()->json([
            'unread_count' => $request->user()->socialNotifications()->whereNull('read_at')->count(),
            ...$notifications->toArray(),
        ]);
    }

    public function readNotification(Request $request, SocialNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);
        $notification->update(['read_at' => $notification->read_at ?: now()]);

        return response()->json(['message' => 'Notifikasi sudah dibaca.']);
    }

    private function owner(Request $request, Invitation $invitation): void
    {
        abort_unless($invitation->user_id === $request->user()->id, 404);
    }

    private function published(Invitation $invitation): void
    {
        abort_unless(
            $invitation->status === 'published'
            && ! $invitation->is_hidden_from_feed
            && ! $invitation->archived_at
            && ! $invitation->media_deleted_at,
            404
        );
    }
}
