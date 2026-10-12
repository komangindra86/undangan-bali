<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Otonan returns every 210 days, so an invitation may say which one it is ("Otonan ke-3").
        Schema::table('invitations', function (Blueprint $table) {
            $table->unsignedSmallInteger('ceremony_number')->nullable()->after('child_photo');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('ceremony_number');
        });
    }
};
