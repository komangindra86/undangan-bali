<?php

namespace Database\Seeders;

use App\Models\InvitationTemplate;
use Illuminate\Database\Seeder;

class InvitationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $currentPuspaKencana = InvitationTemplate::where('slug', 'puspa-kencana')->first();
        $legacyBaliHeritage = InvitationTemplate::where('slug', 'bali-heritage')->first();

        if ($legacyBaliHeritage && ! $currentPuspaKencana) {
            $legacyBaliHeritage->update(['slug' => 'puspa-kencana']);
        } elseif ($legacyBaliHeritage && $currentPuspaKencana) {
            $legacyBaliHeritage->update(['is_active' => false]);
        }

        $templates = [
            [
                'name' => 'Bali Classic',
                'slug' => 'bali-classic',
                'thumbnail' => 'templates/bali-preview/hero-couple.jpg',
                'preview_image' => 'templates/bali-preview/hero-couple.jpg',
                'blade_view' => 'invitations.templates.bali-experience',
            ],
            [
                'name' => 'Pura Sunset',
                'slug' => 'pura-sunset',
                'thumbnail' => 'templates/bali-preview/gallery-evening.jpg',
                'preview_image' => 'templates/bali-preview/gallery-evening.jpg',
                'blade_view' => 'invitations.templates.pura-sunset',
            ],
            [
                'name' => 'Ubud Garden',
                'slug' => 'ubud-garden',
                'thumbnail' => 'templates/bali-preview/gallery-pavilion.jpg',
                'preview_image' => 'templates/bali-preview/gallery-pavilion.jpg',
                'blade_view' => 'invitations.templates.ubud-garden',
            ],
            [
                'name' => 'Royal Kamasan',
                'slug' => 'royal-kamasan',
                'thumbnail' => 'templates/bali-preview/gallery-details.jpg',
                'preview_image' => 'templates/bali-preview/gallery-details.jpg',
                'blade_view' => 'invitations.templates.royal-kamasan',
            ],
            [
                'name' => 'Puspa Kencana',
                'slug' => 'puspa-kencana',
                'thumbnail' => 'templates/bali-heritage/bali-heritage-frame.jpg',
                'preview_image' => 'templates/bali-heritage/bali-heritage-frame.jpg',
                'blade_view' => 'invitations.templates.bali-heritage',
            ],
            [
                'name' => 'Jepun Kirana',
                'slug' => 'jepun-kirana',
                'thumbnail' => 'templates/bali-preview/gallery-details.jpg',
                'preview_image' => 'templates/bali-preview/gallery-details.jpg',
                'blade_view' => 'invitations.templates.pernikahan.jepun-kirana',
            ],
            [
                'name' => 'Carik Asri',
                'slug' => 'carik-asri',
                'thumbnail' => 'templates/bali-preview/gallery-pavilion.jpg',
                'preview_image' => 'templates/bali-preview/gallery-pavilion.jpg',
                'blade_view' => 'invitations.templates.pernikahan.carik-asri',
            ],
            [
                'name' => 'Ukir Kencana',
                'slug' => 'ukir-kencana',
                'thumbnail' => 'templates/bali-preview/gallery-evening.jpg',
                'preview_image' => 'templates/bali-preview/gallery-evening.jpg',
                'blade_view' => 'invitations.templates.pernikahan.ukir-kencana',
            ],
        ];

        foreach ($templates as $template) {
            InvitationTemplate::updateOrCreate(['slug' => $template['slug']], $template + [
                'is_active' => true,
                'is_premium' => false,
                'invitation_type' => 'wedding',
            ]);
        }

        // Premium designs are made for custom orders: hidden from the catalogue unless the account was given access.
        foreach (['wiwaha-agung' => 'Wiwaha Agung', 'taman-tirta' => 'Taman Tirta', 'lamak-prada' => 'Lamak Prada'] as $slug => $name) {
            InvitationTemplate::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'invitation_type' => 'wedding',
                'thumbnail' => 'templates/bali-preview/hero-couple.jpg',
                'preview_image' => 'templates/bali-preview/hero-couple.jpg',
                'blade_view' => 'invitations.templates.premium.'.$slug,
                'is_active' => true,
                'is_premium' => true,
            ]);
        }

        foreach (['ceria-confetti' => 'Ceria Confetti', 'ruang-putih' => 'Ruang Putih', 'bali-pradnyan' => 'Bali Pradnyan'] as $slug => $name) {
            InvitationTemplate::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'invitation_type' => 'birthday',
                'thumbnail' => null,
                'preview_image' => null,
                'blade_view' => 'invitations.templates.birthday.'.$slug,
                'is_active' => true,
                'is_premium' => false,
            ]);
        }

        foreach (['garbha-kencana' => 'Garbha Kencana', 'padma-sari' => 'Padma Sari', 'tirta-hening' => 'Tirta Hening'] as $slug => $name) {
            InvitationTemplate::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'invitation_type' => 'megedong',
                'thumbnail' => null,
                'preview_image' => null,
                'blade_view' => 'invitations.templates.megedong.'.$slug,
                'is_active' => true,
                'is_premium' => false,
            ]);
        }

        foreach (['rare-kencana' => 'Rare Kencana', 'sekar-jepun' => 'Sekar Jepun', 'langit-kumara' => 'Langit Kumara'] as $slug => $name) {
            InvitationTemplate::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'invitation_type' => 'pitung_dina',
                'thumbnail' => null,
                'preview_image' => null,
                'blade_view' => 'invitations.templates.upacara-anak.'.$slug,
                'is_active' => true,
                'is_premium' => false,
            ]);
        }

        // Tigang sasih gets three designs of its own and also offers the three abulan pitung dina designs.
        // A template row belongs to one invitation type, so the shared designs get their own rows and slugs.
        $tigangSasih = [
            'nyambutin-kencana' => ['Nyambutin Kencana', 'nyambutin-kencana'],
            'tunjung-petak' => ['Tunjung Petak', 'tunjung-petak'],
            'surya-alit' => ['Surya Alit', 'surya-alit'],
            'tigang-sasih-rare-kencana' => ['Rare Kencana', 'rare-kencana'],
            'tigang-sasih-sekar-jepun' => ['Sekar Jepun', 'sekar-jepun'],
            'tigang-sasih-langit-kumara' => ['Langit Kumara', 'langit-kumara'],
        ];
        foreach ($tigangSasih as $slug => [$name, $view]) {
            InvitationTemplate::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'invitation_type' => 'tigang_sasih',
                'thumbnail' => null,
                'preview_image' => null,
                'blade_view' => 'invitations.templates.upacara-anak.'.$view,
                'is_active' => true,
                'is_premium' => false,
            ]);
        }

        // Otonan: three designs of its own, then every earlier child ceremony design.
        $otonan = [
            'oton-kencana' => ['Oton Kencana', 'oton-kencana'],
            'sekar-cempaka' => ['Sekar Cempaka', 'sekar-cempaka'],
            'purnama-alit' => ['Purnama Alit', 'purnama-alit'],
            'otonan-nyambutin-kencana' => ['Nyambutin Kencana', 'nyambutin-kencana'],
            'otonan-tunjung-petak' => ['Tunjung Petak', 'tunjung-petak'],
            'otonan-surya-alit' => ['Surya Alit', 'surya-alit'],
            'otonan-rare-kencana' => ['Rare Kencana', 'rare-kencana'],
            'otonan-sekar-jepun' => ['Sekar Jepun', 'sekar-jepun'],
            'otonan-langit-kumara' => ['Langit Kumara', 'langit-kumara'],
        ];
        foreach ($otonan as $slug => [$name, $view]) {
            InvitationTemplate::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'invitation_type' => 'otonan',
                'thumbnail' => null,
                'preview_image' => null,
                'blade_view' => 'invitations.templates.upacara-anak.'.$view,
                'is_active' => true,
                'is_premium' => false,
            ]);
        }

        // Mepandes has designs of its own, drawn for young adults rather than babies; they use the ceremony frame.
        foreach (['bale-gading' => 'Bale Gading', 'sekar-emas' => 'Sekar Emas', 'wastra-songket' => 'Wastra Songket'] as $slug => $name) {
            InvitationTemplate::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'invitation_type' => 'mepandes',
                'thumbnail' => null,
                'preview_image' => null,
                'blade_view' => 'invitations.templates.upacara-anak.'.$slug,
                'is_active' => true,
                'is_premium' => false,
            ]);
        }
    }
}
