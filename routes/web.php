<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\AdminFinanceController;
use App\Http\Controllers\AdminMemberController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\PlayerJoinController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|*/

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', function () {
        return redirect()->route('login');
    })->name('login');

    Route::middleware('auth')->group(function () {
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

Route::get('/join/{slug}', [PlayerJoinController::class, 'show'])->name('player.join.show');
Route::post('/join/{slug}', [PlayerJoinController::class, 'store'])->name('player.join.store');
Route::get('/join/{slug}/success', [PlayerJoinController::class, 'success'])->name('player.join.success');
Route::post('/join/{slug}/simulate-payment', [PlayerJoinController::class, 'simulateOnlinePayment'])->name('player.join.simulatePayment');
Route::post('/join/{slug}/midtrans/token', [PlayerJoinController::class, 'midtransToken'])->name('player.join.midtrans.token');
Route::post('/join/{slug}/midtrans/finish', [PlayerJoinController::class, 'midtransFinish'])->name('player.join.midtrans.finish');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
