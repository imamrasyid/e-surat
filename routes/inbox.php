<?php

use App\Http\Controllers\Inbox\PrimaryController;
use App\Http\Controllers\Inbox\UndanganController;
use Illuminate\Support\Facades\Route;

Route::prefix('primary')->middleware('auth')->group(function () {
    Route::get('/', [PrimaryController::class, 'index'])
        ->middleware('permission:primary.index')
        ->name('inbox.primary.index');

    Route::match(['get', 'patch'], '/{recipient}/{letter_id}', [PrimaryController::class, 'show'])
        ->middleware('permission:primary.show')
        ->name('inbox.primary.show');
});

Route::prefix('invitation-letter')->middleware('auth')->group(function () {
    Route::get('/', [UndanganController::class, 'index'])
        ->middleware('permission:undangan.index')
        ->name('inbox.undangan.index');

    Route::match(['get', 'patch'], '/{recipient}/{letter_id}', [UndanganController::class, 'show'])
        ->middleware('permission:undangan.show')
        ->name('inbox.undangan.show');
});
