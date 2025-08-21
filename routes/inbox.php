<?php

use App\Http\Controllers\Inbox\PrimaryController;
use App\Http\Controllers\Inbox\UndanganController;
use Illuminate\Support\Facades\Route;

Route::prefix('inbox')->middleware('auth')->group(function () {
    Route::prefix('primary')->group(function () {
        Route::get('/', [PrimaryController::class, 'index'])
            ->middleware('permission:primary.index')
            ->name('inbox.primary.index');

        Route::match(['get', 'patch'], '/{recipient}/{letter_id}', [PrimaryController::class, 'show'])
            ->middleware('permission:primary.show')
            ->name('inbox.primary.show');
    });

    Route::prefix('undangan')->group(function () {
        Route::get('/', [UndanganController::class, 'index'])
            ->middleware('permission:undangan.index')
            ->name('inbox.undangan.index');
        
        Route::match(['get', 'patch'], '/{recipient}/{letter_id}', [UndanganController::class, 'show'])
            ->middleware('permission:undangan.show')
            ->name('inbox.undangan.show');
    });
});
