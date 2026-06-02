<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_gift_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(false);
            $table->string('receiver_name')->nullable();
            $table->text('receiver_note')->nullable();
            $table->enum('fee_type', ['flat', 'percent'])->default('flat');
            $table->decimal('fee_value', 12, 2)->default(2000);
            $table->unsignedBigInteger('minimum_amount')->default(10000);
            $table->boolean('show_amount_public')->default(false);
            $table->boolean('allow_message')->default(true);
            $table->timestamps();
        });

        Schema::create('wedding_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('guest_phone')->nullable();
            $table->text('message')->nullable();
            $table->unsignedBigInteger('gift_amount');
            $table->unsignedBigInteger('service_fee');
            $table->unsignedBigInteger('total_amount');
            $table->string('order_id')->unique();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('payment_type')->default('qris');
            $table->text('qr_string')->nullable();
            $table->text('qr_image_url')->nullable();
            $table->string('transaction_status')->default('pending')->index();
            $table->string('fraud_status')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['invitation_id', 'transaction_status']);
        });

        Schema::create('wedding_gift_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_gift_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['pending', 'earned', 'refunded'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_gift_fees');
        Schema::dropIfExists('wedding_gifts');
        Schema::dropIfExists('wedding_gift_settings');
    }
};
