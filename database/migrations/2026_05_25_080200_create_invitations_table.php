<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->constrained('invitation_templates');
            $table->foreignId('music_id')->nullable()->constrained('musics')->nullOnDelete();
            $table->string('slug')->nullable()->unique();
            $table->string('status')->default('draft')->index();
            $table->string('groom_full_name')->nullable();
            $table->string('groom_nickname')->nullable();
            $table->string('groom_father_name')->nullable();
            $table->string('groom_mother_name')->nullable();
            $table->string('groom_child_order')->nullable();
            $table->string('groom_photo')->nullable();
            $table->string('bride_full_name')->nullable();
            $table->string('bride_nickname')->nullable();
            $table->string('bride_father_name')->nullable();
            $table->string('bride_mother_name')->nullable();
            $table->string('bride_child_order')->nullable();
            $table->string('bride_photo')->nullable();
            $table->text('opening_quote')->nullable();
            $table->string('event_type')->nullable();
            $table->date('event_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue_name')->nullable();
            $table->text('venue_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('google_maps_url')->nullable();
            $table->string('music_type')->default('none');
            $table->string('music_file')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
