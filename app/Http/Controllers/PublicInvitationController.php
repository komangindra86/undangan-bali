<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\WeddingGiftSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicInvitationController extends Controller
{
    private const PAYMENT_DEMO_SLUG = 'demo-wedding-gift-xendit';

    public function show(Request $request, string $slug): View
    {
        $invitation = Invitation::with(['template', 'music', 'giftSetting'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $invitation->views()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'viewed_at' => now(),
        ]);

        $view = $invitation->template->blade_view ?: 'invitations.templates.bali-classic';

        return view($view, ['invitation' => $invitation]);
    }

    public function preview(InvitationTemplate $template): View
    {
        abort_unless($template->is_active, 404);

        $invitation = new Invitation([
            'groom_full_name' => 'I Made Wira Adnyana',
            'groom_nickname' => 'Wira',
            'groom_father_name' => 'I Ketut Darma',
            'groom_mother_name' => 'Ni Luh Sari',
            'bride_full_name' => 'Ni Putu Ayu Lestari',
            'bride_nickname' => 'Ayu',
            'bride_father_name' => 'I Wayan Sudarta',
            'bride_mother_name' => 'Ni Made Rini',
            'opening_quote' => 'Dalam restu keluarga dan kehangatan Bali, kami mengikat janji untuk berjalan bersama.',
            'event_type' => 'Pawiwahan',
            'event_date' => Carbon::parse('2026-08-18'),
            'start_time' => '10:00',
            'end_time' => '13:00',
            'venue_name' => 'Bale Banjar Ubud',
            'venue_address' => 'Jalan Raya Ubud, Gianyar, Bali',
            'google_maps_url' => 'https://maps.google.com/?q=-8.5069,115.2625',
            'music_type' => 'none',
            'slug' => 'preview-'.$template->slug,
        ]);
        $invitation->setRelation('template', $template);
        $invitation->setRelation('giftSetting', new WeddingGiftSetting([
            'is_active' => true,
            'receiver_name' => 'Wira & Ayu',
            'receiver_note' => 'Wedding Gift bersifat opsional. Tanda kasih akan diproses aman melalui QRIS.',
            'fee_type' => config('wedding_gift.fee.type'),
            'fee_value' => config('wedding_gift.fee.value'),
            'minimum_amount' => config('wedding_gift.minimum_amount'),
            'show_amount_public' => false,
            'allow_message' => true,
        ]));

        return view($template->blade_view ?: 'invitations.templates.bali-experience', [
            'invitation' => $invitation,
            'isPreview' => true,
        ]);
    }

    public function paymentDemo(): View
    {
        $template = InvitationTemplate::where('slug', 'bali-classic')->first()
            ?: InvitationTemplate::where('is_active', true)->orderBy('id')->firstOrFail();

        $invitation = Invitation::updateOrCreate(
            ['slug' => self::PAYMENT_DEMO_SLUG],
            [
                'template_id' => $template->id,
                'status' => 'published',
                'groom_full_name' => 'I Made Wira Adnyana',
                'groom_nickname' => 'Wira',
                'groom_father_name' => 'I Ketut Darma',
                'groom_mother_name' => 'Ni Luh Sari',
                'bride_full_name' => 'Ni Putu Ayu Lestari',
                'bride_nickname' => 'Ayu',
                'bride_father_name' => 'I Wayan Sudarta',
                'bride_mother_name' => 'Ni Made Rini',
                'opening_quote' => 'Demo undangan Bali untuk mencoba alur Wedding Gift Xendit mode tes.',
                'event_type' => 'Pawiwahan',
                'event_date' => Carbon::parse('2026-08-18'),
                'start_time' => '10:00',
                'end_time' => '13:00',
                'venue_name' => 'Bale Banjar Ubud',
                'venue_address' => 'Jalan Raya Ubud, Gianyar, Bali',
                'google_maps_url' => 'https://maps.google.com/?q=-8.5069,115.2625',
                'music_type' => 'none',
                'published_at' => now(),
            ]
        );

        $invitation->giftSetting()->updateOrCreate([], [
            'is_active' => true,
            'receiver_name' => 'Wira & Ayu',
            'receiver_note' => 'Demo pembayaran mode tes. Tidak ada uang asli yang masuk.',
            'fee_type' => config('wedding_gift.fee.type'),
            'fee_value' => config('wedding_gift.fee.value'),
            'minimum_amount' => config('wedding_gift.minimum_amount'),
            'show_amount_public' => false,
            'allow_message' => true,
        ]);

        $invitation->load(['template', 'music', 'giftSetting']);

        return view($template->blade_view ?: 'invitations.templates.bali-experience', [
            'invitation' => $invitation,
            'isPaymentDemo' => true,
        ]);
    }
}
