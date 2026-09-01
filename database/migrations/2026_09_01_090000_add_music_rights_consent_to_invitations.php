<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->timestamp('music_rights_accepted_at')->nullable()->after('music_file');
            $table->string('music_rights_terms_version', 20)->nullable()->after('music_rights_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['music_rights_accepted_at', 'music_rights_terms_version']);
        });
    }
};
