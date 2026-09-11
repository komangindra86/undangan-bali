<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_payout_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('platform_fee')->default(0)->after('amount');
            $table->unsignedBigInteger('net_amount')->default(0)->after('platform_fee');
        });

        DB::table('gift_payout_requests')->orderBy('id')->eachById(function ($payout) {
            DB::table('gift_payout_requests')->where('id', $payout->id)->update([
                'net_amount' => $payout->amount,
            ]);
        });

        $unpaidGiftIds = DB::table('wedding_gifts')
            ->whereNotIn('transaction_status', ['paid', 'refunded'])
            ->pluck('id');

        if ($unpaidGiftIds->isNotEmpty()) {
            DB::table('wedding_gift_fees')->whereIn('wedding_gift_id', $unpaidGiftIds)->delete();
            DB::table('wedding_gifts')->whereIn('id', $unpaidGiftIds)->update([
                'service_fee' => 0,
                'total_amount' => DB::raw('gift_amount'),
            ]);
        }

        DB::table('wedding_gift_settings')->update([
            'fee_type' => 'percent',
            'fee_value' => 1,
        ]);
    }

    public function down(): void
    {
        Schema::table('gift_payout_requests', function (Blueprint $table) {
            $table->dropColumn(['platform_fee', 'net_amount']);
        });
    }
};
