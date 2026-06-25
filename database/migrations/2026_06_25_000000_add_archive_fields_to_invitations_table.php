<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('published_at');
            $table->timestamp('media_deleted_at')->nullable()->after('archived_at');
            $table->index(['status', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex(['status', 'event_date']);
            $table->dropColumn(['archived_at', 'media_deleted_at']);
        });
    }
};
