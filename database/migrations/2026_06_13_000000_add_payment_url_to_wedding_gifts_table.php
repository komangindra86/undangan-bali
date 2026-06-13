<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_gifts', function (Blueprint $table) {
            $table->text('payment_url')->nullable()->after('qr_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_gifts', function (Blueprint $table) {
            $table->dropColumn('payment_url');
        });
    }
};
