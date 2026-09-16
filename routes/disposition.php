<?php

use App\Http\Controllers\DispositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('disposition')->middleware('auth')->group(function () {
    Route::get('/', [DispositionController::class, 'index'])
        ->middleware('permission:disposition.index')
        ->name('dispo.index');

    Route::post('/store', [DispositionController::class, 'store'])
        ->middleware('permission:disposition.store')
        ->name('dispo.store');

    Route::get('/create/{recipient}/{key}', [DispositionController::class, 'create'])
        ->middleware('permission:disposition.create')
        ->name('dispo.create');

    Route::patch('/update-read-at/{disposition}/{letter_id}', [DispositionController::class, 'update'])
        ->middleware('permission:disposition.show')
        ->name('dispo.update');

    Route::get('/{recipient}/{letter_id}', [DispositionController::class, 'show'])
        ->middleware('permission:disposition.show')
        ->name('dispo.show');
});
