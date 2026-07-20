<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GiftPayoutAccountController;
use App\Http\Controllers\Api\GiftPayoutRequestController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\MomentController;
use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\Api\PublicWeddingGiftController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\WeddingGiftDashboardController;
use App\Http\Controllers\Api\WeddingGiftSettingController;
use App\Http\Controllers\Api\XenditWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'google']);

Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/templates/{template}', [TemplateController::class, 'show']);
Route::get('/musics', [MusicController::class, 'index']);
Route::get('/moments', [MomentController::class, 'index']);
Route::get('/moments/{invitation}', [MomentController::class, 'show']);
Route::post('/moments/{invitation}/request-invitation', [MomentController::class, 'requestInvitation'])->middleware('throttle:3,10');
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
    Route::put('/invitations/{invitation}/feed-visibility', [SocialController::class, 'setFeedVisibility']);
    Route::get('/invitations/{invitation}/invitation-requests', [SocialController::class, 'requests']);
    Route::put('/invitations/{invitation}/invitation-requests/{invitationRequest}/shared', [SocialController::class, 'markRequestShared']);
    Route::get('/invitations/{invitation}/moments', [SocialController::class, 'invitationMoments']);
    Route::post('/invitations/{invitation}/moments', [SocialController::class, 'storeInvitationMoment']);
    Route::delete('/invitations/{invitation}/moments/{moment}', [SocialController::class, 'deleteInvitationMoment']);
    Route::get('/invitations/{invitation}/gift-setting', [WeddingGiftSettingController::class, 'show']);
    Route::post('/invitations/{invitation}/gift-setting', [WeddingGiftSettingController::class, 'store']);
    Route::get('/invitations/{invitation}/gifts', [WeddingGiftDashboardController::class, 'index']);
    Route::get('/invitations/{invitation}/payout-requests', [GiftPayoutRequestController::class, 'index']);
    Route::post('/invitations/{invitation}/payout-requests', [GiftPayoutRequestController::class, 'store']);
    Route::post('/moments/{invitation}/reaction', [SocialController::class, 'react'])->middleware('throttle:20,1');
    Route::delete('/moments/{invitation}/reaction', [SocialController::class, 'removeReaction']);
    Route::post('/moments/{invitation}/comments', [SocialController::class, 'comment'])->middleware('throttle:6,1');
    Route::get('/social/notifications', [SocialController::class, 'notifications']);
    Route::put('/social/notifications/{notification}/read', [SocialController::class, 'readNotification']);
    Route::apiResource('invitations', InvitationController::class);
});
