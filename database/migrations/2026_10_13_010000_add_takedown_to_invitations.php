<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Set by an admin only: the public page, gift page and feed entry stay off until an admin restores it.
            $table->timestamp('taken_down_at')->nullable()->after('archived_at');
            $table->string('takedown_note', 500)->nullable()->after('taken_down_at');
            // Unlike is_hidden_from_feed, which the owner toggles, this keeps an admin's feed removal in place.
            $table->timestamp('feed_blocked_at')->nullable()->after('is_hidden_from_feed');

            $table->index('taken_down_at');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex(['taken_down_at']);
            $table->dropColumn(['taken_down_at', 'takedown_note', 'feed_blocked_at']);
        });
    }
};
