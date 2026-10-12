<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mepandes (potong gigi) is held for one or several people, from one family or several.
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('family_mode', 10)->nullable()->after('ceremony_number');
            $table->json('participants')->nullable()->after('family_mode');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['family_mode', 'participants']);
        });
    }
};
