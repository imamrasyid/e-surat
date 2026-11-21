<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');

    // Dideklarasikan sebelum `{notification}` agar tidak tertangkap route dinamis.
    Route::post('push-subscription', [NotificationController::class, 'storePushSubscription'])
        ->name('push-subscription.store');
    Route::delete('push-subscription', [NotificationController::class, 'destroyPushSubscription'])
        ->name('push-subscription.destroy');

    Route::patch('read-all', [NotificationController::class, 'readAll'])->name('readAll');
    Route::patch('{notification}/read', [NotificationController::class, 'read'])->name('read');
    Route::get('{notification}/open', [NotificationController::class, 'read'])->name('open');
    Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroyAll');
});
