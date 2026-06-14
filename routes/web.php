<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GiftPayoutController;
use App\Http\Controllers\PublicInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');

Route::get('/u/{slug}', [PublicInvitationController::class, 'show'])
    ->name('invitations.public');

Route::get('/demo/wedding-gift-xendit', [PublicInvitationController::class, 'paymentDemo'])
    ->name('demo.wedding-gift-xendit');

Route::get('/preview/templates/{template:slug}', [PublicInvitationController::class, 'preview'])
    ->name('templates.preview');

Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store');
Route::middleware('auth:web')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard.index');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
    Route::get('/payout', [GiftPayoutController::class, 'index'])->name('payout.index');
    Route::get('/payouts', [GiftPayoutController::class, 'index'])->name('payouts.index');
    Route::put('/payouts/{payout}', [GiftPayoutController::class, 'update'])->name('payouts.update');
});
