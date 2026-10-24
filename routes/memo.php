<?php

use App\Http\Controllers\MemoController;
use Illuminate\Support\Facades\Route;

Route::prefix('memo')->middleware('auth')->group(function () {
    Route::get('/', [MemoController::class, 'index'])
        ->middleware('permission:memo.index')
        ->name('memo.index');

    Route::post('/store', [MemoController::class, 'store'])
        ->middleware('permission:memo.store')
        ->name('memo.store');

    Route::post('/create/to-close/store', [MemoController::class, 'storeClose'])
        ->middleware('permission:memo.close')
        ->name('memo.store.close');

    Route::get('/create/to-close/{memo}/{letter_id}', [MemoController::class, 'createClose'])
        ->middleware('permission:memo.close')
        ->name('memo.create.close');

    Route::get('/create/{recipient}/{key}', [MemoController::class, 'create'])
        ->middleware('permission:memo.create')
        ->name('memo.create');

    Route::patch('/update-read-at/{memo}/{letter_id}', [MemoController::class, 'update'])
        ->middleware('permission:memo.show')
        ->name('memo.update');

    Route::get('/{recipient}/{letter_id}', [MemoController::class, 'show'])
        ->middleware('permission:memo.show')
        ->name('memo.show');
});
