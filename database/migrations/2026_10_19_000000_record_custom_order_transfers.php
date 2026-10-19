<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_payout_requests', function (Blueprint $table) {
            // Transfers the admin records for a custom order go to the customer's account, which is not a saved
            // payout account, and keep a photo of the transfer receipt.
            $table->foreignId('payout_account_id')->nullable()->change();
            $table->string('bank_code', 30)->nullable()->change();
            $table->string('proof_path')->nullable()->after('transfer_reference');
        });
    }

    public function down(): void
    {
        Schema::table('gift_payout_requests', function (Blueprint $table) {
            $table->dropColumn('proof_path');
        });
    }
};
