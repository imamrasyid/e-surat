<?php

use App\Http\Controllers\MemoController;
use Illuminate\Support\Facades\Route;

Route::prefix('nota-dinas')->middleware('auth')->group(function () {
    Route::get('/', [MemoController::class, 'index'])
        ->name('memo.index');
    
    Route::post('/store', [MemoController::class, 'store'])
            ->name('memo.store');
        
    Route::get('/create/{recipient}/{key}', [MemoController::class, 'create'])
        ->name('memo.create');

    Route::patch('/update-read-at/{memo}/{letter_id}', [MemoController::class, 'update'])
            ->name('memo.update');
        
    Route::get('/{recipient}/{letter_id}', [MemoController::class, 'show'])
        ->name('memo.show');
});
