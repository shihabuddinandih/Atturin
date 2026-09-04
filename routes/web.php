<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\AdminFinanceController;
use App\Http\Controllers\AdminMemberController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\PlayerJoinController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SuperAdminPaymentReminderController;
use App\Http\Controllers\SuperAdminPlayerContactRequestController;
use App\Http\Controllers\SuperAdminWaitlistReminderController;
use App\Http\Controllers\SuperAdminWithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->homePath());
    }
    return view('landing');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', function () {
        return redirect()->route('login');
    })->name('login');

    Route::middleware(['auth', 'admin.only'])->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::patch('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::view('notifications', 'admin.notifications.index')->name('notifications.index');
        Route::view('faq', 'admin.faq.index')->name('faq.index');

        // Events (resource + custom actions)
        Route::resource('events', AdminEventController::class);
        Route::get('events/{event}/live', [AdminEventController::class, 'live'])->name('events.live');
        Route::post('events/{event}/join-visibility', [AdminEventController::class, 'updateJoinVisibility'])->name('events.updateJoinVisibility');
        Route::post('events/{event}/attendance/{player}', [AdminEventController::class, 'updateAttendance'])->name('events.updateAttendance');
        Route::post('events/{event}/status/{player}', [AdminEventController::class, 'updateStatus'])->name('events.updateStatus');
        Route::post('events/{event}/payment/{player}', [AdminEventController::class, 'updatePayment'])->name('events.updatePayment');
        Route::post('events/{event}/contact/{player}', [AdminEventController::class, 'requestContact'])->name('events.requestContact');

        // Members (dedicated controller)
        Route::get('members', [AdminMemberController::class, 'index'])->name('members.index');

        // Finances (dedicated controller)
        Route::get('finances', [AdminFinanceController::class, 'index'])->name('finances.index');
        Route::post('finances/withdraw', [AdminFinanceController::class, 'withdraw'])->name('finances.withdraw');
        Route::post('finances/withdrawals/{withdrawal}/process', [AdminFinanceController::class, 'processWithdrawal'])->name('finances.withdrawals.process');

        // Subscription packages
        Route::view('subscriptions', 'admin.subscriptions.index')->name('subscriptions.index');
    });
});

Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('withdrawals', [SuperAdminWithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals/{withdrawal}/approve', [SuperAdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('withdrawals/{withdrawal}/reject', [SuperAdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');

    Route::get('waitlist-reminders', [SuperAdminWaitlistReminderController::class, 'index'])->name('waitlist-reminders.index');
    Route::get('waitlist-reminders/{waitlist}/remind', [SuperAdminWaitlistReminderController::class, 'remind'])->name('waitlist-reminders.remind');

    Route::get('payment-reminders', [SuperAdminPaymentReminderController::class, 'index'])->name('payment-reminders.index');
    Route::get('payment-reminders/{event}/{player}/remind', [SuperAdminPaymentReminderController::class, 'remind'])->name('payment-reminders.remind');

    Route::get('registration-confirmations', [\App\Http\Controllers\SuperAdminRegistrationConfirmationController::class, 'index'])->name('registration-confirmations.index');
    Route::get('registration-confirmations/{event}/{player}/send', [\App\Http\Controllers\SuperAdminRegistrationConfirmationController::class, 'send'])->name('registration-confirmations.send');

    Route::get('player-contact-requests', [SuperAdminPlayerContactRequestController::class, 'index'])->name('player-contact-requests.index');
    Route::get('player-contact-requests/{contactRequest}/send', [SuperAdminPlayerContactRequestController::class, 'send'])->name('player-contact-requests.send');
});

Route::get('/join/{slug}', [PlayerJoinController::class, 'show'])->name('player.join.show');
Route::post('/join/{slug}', [PlayerJoinController::class, 'store'])->name('player.join.store');
Route::get('/join/{slug}/success', [PlayerJoinController::class, 'success'])->name('player.join.success');
Route::post('/join/{slug}/simulate-payment', [PlayerJoinController::class, 'simulateOnlinePayment'])->name('player.join.simulatePayment');
Route::post('/join/{slug}/midtrans/token', [PlayerJoinController::class, 'midtransToken'])->name('player.join.midtrans.token');
Route::post('/join/{slug}/midtrans/finish', [PlayerJoinController::class, 'midtransFinish'])->name('player.join.midtrans.finish');
Route::post('/join/{slug}/midtrans/status', [PlayerJoinController::class, 'midtransStatus'])->name('player.join.midtrans.status');
Route::post('/join/{slug}/cancel', [PlayerJoinController::class, 'cancel'])->name('player.join.cancel');

// Waiting list claim (public)
Route::get('/waitlist/claim/{token}', [\App\Http\Controllers\WaitlistController::class, 'claim'])->name('waitlist.claim');

// Durable, device-independent registration link (shareable via WhatsApp)
Route::get('/r/{token}', [RegistrationController::class, 'show'])->name('registration.show');
Route::post('/r/{token}/midtrans/token', [RegistrationController::class, 'midtransToken'])->name('registration.midtrans.token');
Route::post('/r/{token}/midtrans/finish', [RegistrationController::class, 'midtransFinish'])->name('registration.midtrans.finish');
Route::post('/r/{token}/midtrans/status', [RegistrationController::class, 'midtransStatus'])->name('registration.midtrans.status');
Route::post('/r/{token}/simulate-payment', [RegistrationController::class, 'simulatePayment'])->name('registration.simulatePayment');
Route::post('/r/{token}/cancel', [RegistrationController::class, 'cancel'])->name('registration.cancel');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
