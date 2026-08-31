<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitation_templates', function (Blueprint $table) {
            $table->string('invitation_type', 20)->default('wedding')->index();
        });
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('invitation_type', 20)->default('wedding')->index();
            $table->string('celebrant_full_name', 80)->nullable();
            $table->string('celebrant_nickname', 18)->nullable();
            $table->unsignedSmallInteger('celebrant_age')->nullable();
            $table->string('celebrant_photo')->nullable();
            $table->string('host_name', 80)->nullable();
            $table->string('event_title', 120)->nullable();
            $table->string('dress_code', 80)->nullable();
            $table->timestamp('feed_consent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['invitation_type', 'celebrant_full_name', 'celebrant_nickname', 'celebrant_age', 'celebrant_photo', 'host_name', 'event_title', 'dress_code', 'feed_consent_at']);
        });
        Schema::table('invitation_templates', fn (Blueprint $table) => $table->dropColumn('invitation_type'));
    }
};
