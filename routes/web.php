<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\GiftPayoutController;
use App\Http\Controllers\PublicInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');

Route::get('/u/{slug}', [PublicInvitationController::class, 'show'])
    ->name('invitations.public');

Route::get('/preview/templates/{template:slug}', [PublicInvitationController::class, 'preview'])
    ->name('templates.preview');

Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store');
Route::middleware('auth:web')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
    Route::get('/payouts', [GiftPayoutController::class, 'index'])->name('payouts.index');
    Route::put('/payouts/{payout}', [GiftPayoutController::class, 'update'])->name('payouts.update');
});
