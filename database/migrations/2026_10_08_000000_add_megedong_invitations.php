<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Megedong-gedongan reuses the bride_* columns for the expectant mother and groom_* for the father.
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('pregnancy_age', 40)->nullable()->after('dress_code');
            $table->string('child_order', 50)->nullable()->after('pregnancy_age');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['pregnancy_age', 'child_order']);
        });
    }
};
