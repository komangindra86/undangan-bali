<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Secret part of the link a custom order's customer opens to see the gifts received.
            $table->string('monitor_token', 64)->nullable()->unique()->after('order_details');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropUnique(['monitor_token']);
            $table->dropColumn('monitor_token');
        });
    }
};
