<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_reports', function (Blueprint $table) {
            // Guests opening an invitation link report without an account, so the reporter may be unknown.
            $table->foreignId('reporter_user_id')->nullable()->change();
            $table->string('reporter_ip', 45)->nullable()->after('reporter_user_id');
            $table->string('reporter_contact', 80)->nullable()->after('reporter_ip');
        });
    }

    public function down(): void
    {
        DB::table('content_reports')->whereNull('reporter_user_id')->delete();

        Schema::table('content_reports', function (Blueprint $table) {
            $table->dropColumn(['reporter_ip', 'reporter_contact']);
            $table->foreignId('reporter_user_id')->nullable(false)->change();
        });
    }
};
