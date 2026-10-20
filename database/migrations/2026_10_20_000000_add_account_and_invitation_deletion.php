<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // A deleted account keeps an anonymous row so the gift and payout records that point to it stay intact.
            $table->timestamp('account_deleted_at')->nullable()->after('is_test_account');
        });

        Schema::table('invitations', function (Blueprint $table) {
            // An invitation its owner deleted while it still had gift records: hidden everywhere, records kept.
            $table->timestamp('owner_deleted_at')->nullable()->after('taken_down_at');
        });

        Schema::create('account_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('note', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_deletion_requests');
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('owner_deleted_at');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account_deleted_at');
        });
    }
};
