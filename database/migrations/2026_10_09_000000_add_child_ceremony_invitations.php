<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ceremonies held for a baby or child (first: abulan pitung dina). The parents reuse groom_* (father)
        // and bride_* (mother); child_order already exists from megedong-gedongan.
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('child_full_name', 80)->nullable()->after('child_order');
            $table->string('child_nickname', 18)->nullable()->after('child_full_name');
            $table->string('child_gender', 10)->nullable()->after('child_nickname');
            $table->date('child_birth_date')->nullable()->after('child_gender');
            $table->string('child_photo')->nullable()->after('child_birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['child_full_name', 'child_nickname', 'child_gender', 'child_birth_date', 'child_photo']);
        });
    }
};
