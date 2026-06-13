<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GiftPayoutAccountController;
use App\Http\Controllers\Api\GiftPayoutRequestController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\Api\PublicWeddingGiftController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\WeddingGiftDashboardController;
use App\Http\Controllers\Api\WeddingGiftSettingController;
use App\Http\Controllers\Api\XenditWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/templates/{template}', [TemplateController::class, 'show']);
Route::get('/musics', [MusicController::class, 'index']);
Route::post('/public/invitations/{slug}/wedding-gift/create', [PublicWeddingGiftController::class, 'store']);
Route::get('/public/wedding-gift/{orderId}/status', [PublicWeddingGiftController::class, 'status']);
Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle']);
Route::post('/xendit/webhook', [XenditWebhookController::class, 'handle']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/payout-account', [GiftPayoutAccountController::class, 'show']);
    Route::post('/payout-account', [GiftPayoutAccountController::class, 'store']);

    Route::post('/invitations/sync-local-draft', [InvitationController::class, 'syncLocalDraft']);
    Route::post('/invitations/{invitation}/publish', [InvitationController::class, 'publish']);
    Route::get('/invitations/{invitation}/gift-setting', [WeddingGiftSettingController::class, 'show']);
    Route::post('/invitations/{invitation}/gift-setting', [WeddingGiftSettingController::class, 'store']);
    Route::get('/invitations/{invitation}/gifts', [WeddingGiftDashboardController::class, 'index']);
    Route::get('/invitations/{invitation}/payout-requests', [GiftPayoutRequestController::class, 'index']);
    Route::post('/invitations/{invitation}/payout-requests', [GiftPayoutRequestController::class, 'store']);
    Route::apiResource('invitations', InvitationController::class);
});
