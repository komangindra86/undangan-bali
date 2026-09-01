<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('musics', function (Blueprint $table) {
            $table->string('catalog_key')->nullable()->unique();
            $table->string('artist')->nullable();
            $table->string('artist_url')->nullable();
            $table->json('categories')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->string('license_code', 40)->nullable();
            $table->string('license_url')->nullable();
            $table->text('attribution')->nullable();
            $table->text('modifications')->nullable();
            $table->string('preview_file_path')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('file_bytes')->nullable();
            $table->string('audio_sha256', 64)->nullable();
            $table->string('preview_sha256', 64)->nullable();
            $table->string('license_evidence_path')->nullable();
            $table->timestamp('license_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('musics', function (Blueprint $table) {
            $table->dropUnique(['catalog_key']);
            $table->dropColumn([
                'catalog_key', 'artist', 'artist_url', 'categories', 'source_url',
                'license_code', 'license_url', 'attribution', 'modifications',
                'preview_file_path', 'duration_seconds', 'file_bytes', 'audio_sha256',
                'preview_sha256', 'license_evidence_path', 'license_verified_at',
            ]);
        });
    }
};
