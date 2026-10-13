<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Music;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Custom orders: wedding invitations the admin builds on the web for a customer, on any template including the
 * premium ones the app never lists. The admin's own account owns them; the customer receives the finished link.
 */
class CustomInvitationController extends Controller
{
    private const MAX_GALLERY = 6;

    private const MAX_STORY = 6;

    private const ORDER_FIELDS = ['customer_name', 'customer_whatsapp', 'bank_name', 'account_number', 'account_holder', 'note'];

    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        return view('admin.custom.index', [
            'invitations' => Invitation::whereHas('user', fn ($query) => $query->where('role', 'admin'))
                ->with(['template:id,name,is_premium', 'giftSetting:invitation_id,is_active'])
                ->withSum(['weddingGifts as gift_paid_total' => fn ($query) => $query->where('transaction_status', 'paid')], 'gift_amount')
                ->latest()
                ->paginate(30),
            'premiumTemplates' => InvitationTemplate::where('is_premium', true)->where('is_active', true)->orderBy('id')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureAdmin($request);

        return $this->form(new Invitation(['invitation_type' => 'wedding', 'event_type' => 'Pawiwahan']));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $invitation = new Invitation(['invitation_type' => 'wedding', 'status' => 'draft', 'music_type' => 'none']);
        $invitation->user_id = $request->user('web')->id;
        $this->save($request, $invitation);

        return redirect()->route('admin.custom.edit', $invitation)->with('message', $this->savedMessage($invitation));
    }

    public function edit(Request $request, Invitation $invitation): View
    {
        $this->ensureCustomOrder($request, $invitation);

        return $this->form($invitation->load(['giftSetting', 'moments' => fn ($query) => $query->oldest('occurred_at')->oldest('id')]));
    }

    public function update(Request $request, Invitation $invitation): RedirectResponse
    {
        $this->ensureCustomOrder($request, $invitation);
        $this->save($request, $invitation);

        return redirect()->route('admin.custom.edit', $invitation)->with('message', $this->savedMessage($invitation));
    }

    private function form(Invitation $invitation): View
    {
        return view('admin.custom.form', [
            'invitation' => $invitation,
            'templates' => InvitationTemplate::where('is_active', true)->where('invitation_type', 'wedding')
                ->orderByDesc('is_premium')->orderBy('id')->get(),
            'musics' => Music::where('is_active', true)->orderBy('title')->get(['id', 'title', 'artist']),
            'eventTypes' => Invitation::EVENT_TYPES['wedding'],
            'maxGallery' => self::MAX_GALLERY,
            'maxStory' => self::MAX_STORY,
        ]);
    }

    private function save(Request $request, Invitation $invitation): void
    {
        $name = ['required', 'string', 'max:80', 'regex:/^[\pL\s.\'-]+$/u'];
        $nickname = ['required', 'string', 'max:18', 'regex:/^[\pL\s.\'-]+$/u'];
        $parent = ['nullable', 'string', 'max:80', 'regex:/^[\pL\s.\'-]+$/u'];
        $text = fn (int $max) => ['nullable', 'string', 'max:'.$max, 'not_regex:/[<>]/'];
        $photo = ['nullable', 'image', 'max:8192'];

        $data = $request->validate([
            'template_id' => ['required', Rule::exists('invitation_templates', 'id')->where('is_active', true)->where('invitation_type', 'wedding')],
            'groom_full_name' => $name,
            'groom_nickname' => $nickname,
            'groom_father_name' => $parent,
            'groom_mother_name' => $parent,
            'bride_full_name' => $name,
            'bride_nickname' => $nickname,
            'bride_father_name' => $parent,
            'bride_mother_name' => $parent,
            'groom_photo' => $photo,
            'bride_photo' => $photo,
            'opening_quote' => $text(1000),
            'event_type' => ['required', Rule::in(Invitation::EVENT_TYPES['wedding'])],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'venue_name' => ['required', 'string', 'max:120', 'not_regex:/[<>]/'],
            'venue_address' => ['required', 'string', 'max:1000', 'not_regex:/[<>]/'],
            'google_maps_url' => ['nullable', 'url', 'max:2000'],
            'gallery_photos' => ['nullable', 'array', 'max:'.self::MAX_GALLERY],
            'gallery_photos.*' => ['image', 'max:8192'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['string'],
            'music_id' => ['nullable', Rule::exists('musics', 'id')->where('is_active', true)],
            'story' => ['nullable', 'array', 'max:'.self::MAX_STORY],
            'story.*.title' => $text(100),
            'story.*.when' => ['nullable', 'date_format:Y-m'],
            'story.*.body' => $text(600),
            'gift_active' => ['nullable', 'boolean'],
            'gift_receiver_name' => ['nullable', 'required_if:gift_active,1', 'string', 'max:80', 'not_regex:/[<>]/'],
            'gift_minimum_amount' => ['nullable', 'integer', 'min:10000', 'max:100000000'],
            'order' => ['nullable', 'array'],
            'order.*' => $text(300),
            'intent' => ['required', Rule::in(['draft', 'publish'])],
        ], [
            '*.regex' => ':attribute hanya boleh berisi huruf, spasi, titik, petik, atau tanda hubung.',
            '*.not_regex' => ':attribute tidak boleh mengandung karakter < atau >.',
            'gift_receiver_name.required_if' => 'Isi nama penerima bila tanda kasih diaktifkan.',
        ], [
            'template_id' => 'template',
            'groom_full_name' => 'nama lengkap mempelai pria',
            'groom_nickname' => 'nama panggilan mempelai pria',
            'groom_father_name' => 'nama ayah mempelai pria',
            'groom_mother_name' => 'nama ibu mempelai pria',
            'bride_full_name' => 'nama lengkap mempelai wanita',
            'bride_nickname' => 'nama panggilan mempelai wanita',
            'bride_father_name' => 'nama ayah mempelai wanita',
            'bride_mother_name' => 'nama ibu mempelai wanita',
            'groom_photo' => 'foto mempelai pria',
            'bride_photo' => 'foto mempelai wanita',
            'opening_quote' => 'kalimat pembuka',
            'event_type' => 'jenis acara',
            'event_date' => 'tanggal acara',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'venue_name' => 'nama tempat',
            'venue_address' => 'alamat lengkap',
            'google_maps_url' => 'link Google Maps',
            'gallery_photos' => 'foto galeri',
            'gallery_photos.*' => 'foto galeri',
            'music_id' => 'musik',
            'gift_receiver_name' => 'nama penerima tanda kasih',
            'gift_minimum_amount' => 'nominal minimal',
        ]);

        DB::transaction(function () use ($request, $invitation, $data) {
            $firstPublish = $data['intent'] === 'publish' && ! $invitation->slug;

            $invitation->fill(collect($data)->only([
                'template_id', 'groom_full_name', 'groom_nickname', 'groom_father_name', 'groom_mother_name',
                'bride_full_name', 'bride_nickname', 'bride_father_name', 'bride_mother_name',
                'opening_quote', 'event_type', 'event_date', 'start_time', 'end_time',
                'venue_name', 'venue_address', 'google_maps_url',
            ])->all());
            $invitation->music_id = $data['music_id'] ?? null;
            $invitation->music_type = $invitation->music_id ? 'default' : 'none';
            // Made-to-order invitations are handed over by link and stay out of the app's public feed.
            $invitation->is_hidden_from_feed = true;
            $invitation->order_details = array_filter(collect($data['order'] ?? [])->only(self::ORDER_FIELDS)->all()) ?: null;

            foreach (['groom_photo', 'bride_photo'] as $field) {
                if ($request->hasFile($field)) {
                    Storage::disk('public')->delete(array_filter([$invitation->{$field}]));
                    $invitation->{$field} = $request->file($field)->store('invitations/photos', 'public');
                }
            }

            $gallery = collect($invitation->gallery_photos ?? []);
            $removed = $gallery->intersect($data['remove_gallery'] ?? []);
            Storage::disk('public')->delete($removed->all());
            $gallery = $gallery->diff($removed)
                ->concat(collect($request->file('gallery_photos', []))->map(fn ($file) => $file->store('invitations/gallery', 'public')));
            // Uploads beyond the limit are stored first and then dropped, so nothing is left orphaned on disk.
            Storage::disk('public')->delete($gallery->slice(self::MAX_GALLERY)->all());
            $invitation->gallery_photos = $gallery->take(self::MAX_GALLERY)->values()->all() ?: null;

            if ($data['intent'] === 'publish') {
                $invitation->status = 'published';
                $invitation->published_at ??= now();
                $invitation->slug ??= $invitation->uniqueSlug();
            } elseif ($invitation->status !== 'published') {
                $invitation->status = 'draft';
            }
            $invitation->save();

            if ($firstPublish) {
                $invitation->template()->increment('usage_count');
            }

            $invitation->moments()->delete();
            foreach ($data['story'] ?? [] as $chapter) {
                if (filled($chapter['title'] ?? null)) {
                    $invitation->moments()->create([
                        'title' => $chapter['title'],
                        'body' => $chapter['body'] ?? null,
                        'occurred_at' => filled($chapter['when'] ?? null) ? Carbon::createFromFormat('Y-m-d', $chapter['when'].'-01')->startOfDay() : null,
                    ]);
                }
            }

            $invitation->giftSetting()->updateOrCreate([], [
                'is_active' => (bool) ($data['gift_active'] ?? false),
                'receiver_name' => $data['gift_receiver_name'] ?? $invitation->display_name,
                'minimum_amount' => $data['gift_minimum_amount'] ?? config('wedding_gift.minimum_amount'),
                'fee_type' => 'percent',
                'fee_value' => config('wedding_gift.payout_fee_percent'),
            ]);
        });
    }

    private function savedMessage(Invitation $invitation): string
    {
        return $invitation->status === 'published'
            ? 'Undangan tersimpan dan tayang. Link untuk pelanggan ada di bawah.'
            : 'Draft tersimpan. Undangan belum tayang.';
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user('web')?->isAdmin(), 403);
    }

    private function ensureCustomOrder(Request $request, Invitation $invitation): void
    {
        $this->ensureAdmin($request);
        // Invitations people made in the app are theirs to edit; only admin-made orders open here.
        abort_unless($invitation->user?->isAdmin() && $invitation->invitation_type === 'wedding', 404);
    }
}
