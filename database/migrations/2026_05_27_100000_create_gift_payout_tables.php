<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_payout_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('bank_code', 30);
            $table->string('bank_name');
            $table->string('account_number', 50);
            $table->string('account_holder_name');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('gift_payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payout_account_id')->constrained('gift_payout_accounts')->restrictOnDelete();
            $table->string('bank_code', 30);
            $table->string('bank_name');
            $table->string('account_number', 50);
            $table->string('account_holder_name');
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['pending', 'approved', 'processing', 'paid', 'rejected'])->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->string('transfer_reference')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['invitation_id', 'status']);
        });

        Schema::create('gift_payout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_request_id')->constrained('gift_payout_requests')->cascadeOnDelete();
            $table->foreignId('wedding_gift_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->timestamps();

            $table->unique(['payout_request_id', 'wedding_gift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_payout_items');
        Schema::dropIfExists('gift_payout_requests');
        Schema::dropIfExists('gift_payout_accounts');
    }
};
