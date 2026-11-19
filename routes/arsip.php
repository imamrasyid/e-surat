<?php

use App\Http\Controllers\ArsipController;
use Illuminate\Support\Facades\Route;

Route::prefix('archive')->middleware('auth')->group(function () {
    Route::get('/', [ArsipController::class, 'index'])
        ->middleware('permission:arsip.index')
        ->name('arsip.index');

    Route::get('/{letter_id}', [ArsipController::class, 'show'])
        ->middleware('permission:arsip.show')
        ->name('arsip.show');
});
