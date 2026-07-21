<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LEGACY_ERROR = 'Legacy Expo push token dinonaktifkan setelah migrasi ke FCM.';

    public function up(): void
    {
        DB::table('push_tokens')
            ->where(function ($query) {
                $query->where('token', 'like', 'ExponentPushToken[%')
                    ->orWhere('token', 'like', 'ExpoPushToken[%');
            })
            ->update([
                'last_error' => self::LEGACY_ERROR,
                'disabled_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('push_tokens')
            ->where('last_error', self::LEGACY_ERROR)
            ->update([
                'last_error' => null,
                'disabled_at' => null,
                'updated_at' => now(),
            ]);
    }
};
