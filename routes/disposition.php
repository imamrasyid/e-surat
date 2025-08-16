<?php

use App\Http\Controllers\DispositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('disposition')->middleware('auth')->group(function () {
    Route::get('/', [DispositionController::class, 'index'])
        ->name('dispo.index');
    
    Route::get('/create/{recipient_id}/{key}', [DispositionController::class, 'create'])
        ->name('dispo.create');

    Route::post('/store', [DispositionController::class, 'store'])
        ->name('dispo.store');

    Route::patch('/update-read-at/{disposition}/{letter_id}', [DispositionController::class, 'update'])
            ->name('dispo.update');
        
    Route::get('/{disposition}/{letter_id}', [DispositionController::class, 'show'])
        ->name('dispo.show');
});
