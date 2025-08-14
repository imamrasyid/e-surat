<?php

use App\Http\Controllers\Inbox\PrimaryController;
use App\Http\Controllers\Inbox\UndanganController;
use Illuminate\Support\Facades\Route;

Route::prefix('inbox')->middleware('auth')->group(function () {
    Route::prefix('primary')->group(function () {
        Route::get('/', [PrimaryController::class, 'index'])
            ->name('inbox.primary.index');

        Route::patch('/update-read-at/{letter_recipient_id}/{letter_id}', [PrimaryController::class, 'update'])
            ->name('inbox.primary.update');

        Route::get('/{letter_recipient_id}/{letter_id}', [PrimaryController::class, 'show'])
            ->name('inbox.primary.show');
    });

    Route::prefix('undangan')->group(function () {
        Route::get('/', [UndanganController::class, 'index'])
            ->name('inbox.undangan.index');
        
        Route::patch('/update-read-at/{letter_recipient_id}/{letter_id}', [UndanganController::class, 'update'])
            ->name('inbox.undangan.update');
        
        Route::get('/{letter_recipient_id}/{letter_id}', [UndanganController::class, 'show'])
            ->name('inbox.undangan.show');
    });
});
