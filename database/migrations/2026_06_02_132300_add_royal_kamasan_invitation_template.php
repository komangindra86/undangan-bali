<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $template = [
            'name' => 'Royal Kamasan',
            'thumbnail' => 'templates/bali-preview/gallery-details.jpg',
            'preview_image' => 'templates/bali-preview/gallery-details.jpg',
            'blade_view' => 'invitations.templates.royal-kamasan',
            'is_active' => true,
            'is_premium' => false,
            'updated_at' => now(),
        ];

        if (DB::table('invitation_templates')->where('slug', 'royal-kamasan')->exists()) {
            DB::table('invitation_templates')->where('slug', 'royal-kamasan')->update($template);
            return;
        }

        DB::table('invitation_templates')->insert($template + [
            'slug' => 'royal-kamasan',
            'created_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('invitation_templates')
            ->where('slug', 'royal-kamasan')
            ->update([
                'name' => 'Royal Kamasan',
                'thumbnail' => 'templates/bali-preview/gallery-details.jpg',
                'preview_image' => 'templates/bali-preview/gallery-details.jpg',
                'blade_view' => 'invitations.templates.royal-kamasan',
                'is_active' => false,
                'is_premium' => false,
                'updated_at' => now(),
            ]);
    }
};
