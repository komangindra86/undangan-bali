<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MomentResource;
use App\Models\Invitation;
use App\Services\SocialNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MomentController extends Controller
{
    public function index(): JsonResponse
    {
        $moments = $this->feedQuery()->paginate(15);

        return MomentResource::collection($moments)->response();
    }

    public function show(int $invitation): JsonResponse
    {
        $invitation = $this->feedQuery()
            ->with(['moments' => fn ($query) => $query->latest('occurred_at')->latest()])
            ->findOrFail($invitation);

        $data = (new MomentResource($invitation))->resolve();
        $data['timeline'] = $invitation->moments->map(fn ($moment) => [
            'id' => $moment->id,
            'title' => $moment->title,
            'body' => $moment->body,
            'photo_url' => $moment->photo_path ? url('/storage/'.$moment->photo_path) : null,
            'occurred_at' => $moment->occurred_at?->toISOString(),
        ])->values();
        $data['comments'] = $invitation->comments()
            ->whereNull('deleted_at')
            ->with('user:id,name')
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn ($comment) => [
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => $comment->created_at->toISOString(),
                'user' => ['id' => $comment->user->id, 'name' => $comment->user->name],
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    public function requestInvitation(Request $request, int $invitation, SocialNotificationService $notifications): JsonResponse
    {
        $request->merge([
            'requester_name' => $this->normalizeName($request->input('requester_name')),
            'requester_whatsapp' => $this->normalizeWhatsapp($request->input('requester_whatsapp')),
        ]);
        $data = $request->validate([
            'requester_name' => ['required', 'string', 'max:80', 'regex:/^[\pL\s.\'-]+$/u'],
            'requester_whatsapp' => ['required', 'string', 'regex:/^62\d{8,14}$/'],
        ], [
            'requester_name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, petik, atau tanda hubung.',
            'requester_whatsapp.regex' => 'Nomor WhatsApp harus berupa nomor Indonesia yang valid.',
        ], [
            'requester_name' => 'nama',
            'requester_whatsapp' => 'nomor WhatsApp',
        ]);

        $invitation = $this->feedQuery()->findOrFail($invitation);
        $recentRequest = $invitation->invitationRequests()
            ->where('requester_whatsapp', $data['requester_whatsapp'])
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($recentRequest) {
            throw ValidationException::withMessages([
                'requester_whatsapp' => 'Permintaan untuk nomor ini sudah dikirim. Silakan tunggu pasangan membagikan undangannya.',
            ]);
        }

        $invitationRequest = $invitation->invitationRequests()->create([
            ...$data,
            'status' => 'pending',
        ]);
        $notifications->send($invitation, 'invitation_request', [
            'request_id' => $invitationRequest->id,
            'requester_name' => $invitationRequest->requester_name,
            'message' => $invitationRequest->requester_name.' meminta undangan Anda.',
        ]);

        return response()->json([
            'message' => 'Permintaan undangan sudah dikirim ke pasangan.',
        ], 201);
    }

    private function feedQuery()
    {
        return Invitation::query()
            ->where('status', 'published')
            ->whereNull('archived_at')
            ->whereNull('media_deleted_at')
            ->where('is_hidden_from_feed', false)
            ->whereNotNull('groom_nickname')
            ->whereNotNull('bride_nickname')
            ->with(['template:id,name', 'giftSetting:invitation_id,is_active'])
            ->withCount([
                'reactions as like_reactions_count' => fn ($query) => $query->where('type', 'like'),
                'reactions as love_reactions_count' => fn ($query) => $query->where('type', 'love'),
                'comments as comments_count' => fn ($query) => $query->whereNull('deleted_at'),
            ])
            ->latest('published_at');
    }

    private function normalizeName(mixed $name): string
    {
        return trim(preg_replace('/\s+/', ' ', (string) $name));
    }

    private function normalizeWhatsapp(mixed $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return match (true) {
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
    }
}
