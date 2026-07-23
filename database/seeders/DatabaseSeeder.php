<?php

namespace Database\Seeders;

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

        $this->call(InvitationTemplateSeeder::class);

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
