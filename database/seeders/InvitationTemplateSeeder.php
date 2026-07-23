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
        ];

        foreach ($templates as $template) {
            InvitationTemplate::updateOrCreate(['slug' => $template['slug']], $template + [
                'is_active' => true,
                'is_premium' => false,
            ]);
        }
    }
}
