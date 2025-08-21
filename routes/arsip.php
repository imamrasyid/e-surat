<?php

use App\Http\Controllers\ArsipController;
use Illuminate\Support\Facades\Route;

Route::prefix('archive')->middleware('auth')->group(function () {
    Route::get('/', [ArsipController::class, 'index'])
        ->name('arsip.index');
});
