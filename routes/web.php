<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ComposeController;
use App\Http\Controllers\SentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('compose', [ComposeController::class, 'create'])
        ->middleware('permission:compose.create')
        ->name('app.compose.create');

    Route::post('compose/store', [ComposeController::class, 'store'])
        ->middleware('permission:compose.store')
        ->name('app.compose.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('sent', [SentController::class, 'index'])
        ->middleware('permission:sent.index')
        ->name('sent.index');

    Route::get('sent/{letter_id}', [SentController::class, 'show'])
        ->middleware('permission:sent.show')
        ->name('sent.show');
});

Route::prefix('api')->group(function () {
    Route::get('timeline/recipient/{recipient}', [ApiController::class, 'recipientTimeline'])
        ->name('api.timeline.recipient');
});

require __DIR__.'/arsip.php';
require __DIR__.'/memo.php';
require __DIR__.'/disposition.php';
require __DIR__.'/inbox.php';
require __DIR__.'/master.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
