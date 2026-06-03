<?php

namespace Database\Seeders;

use App\Models\InvitationTemplate;
use App\Models\Music;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@undanganbali.test'], [
            'name' => 'Admin Undangan Bali',
            'password' => 'password',
            'role' => 'admin',
        ]);

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
        ];

        foreach ($templates as $template) {
            InvitationTemplate::updateOrCreate(['slug' => $template['slug']], $template + [
                'is_active' => true,
                'is_premium' => false,
            ]);
        }

        $musics = [
            ['title' => 'Bali Romantis', 'file_path' => 'musics/bali-romantis.wav'],
            ['title' => 'Janji Suci', 'file_path' => 'musics/janji-suci.wav'],
            ['title' => 'Senja Bahagia', 'file_path' => 'musics/senja-bahagia.wav'],
        ];

        foreach ($musics as $music) {
            Music::updateOrCreate(['title' => $music['title']], $music + ['is_active' => true]);
        }
    }
}
