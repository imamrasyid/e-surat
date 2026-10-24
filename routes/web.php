<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ComposeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/about', function () {
    return Inertia::render('about/Index');
})->name('about');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])->name('dashboard');

Route::get('dashboard/chart-data', [DashboardController::class, 'chartData'])
    ->middleware(['auth'])->name('dashboard.chart-data');

Route::middleware(['auth'])->group(function () {
    Route::get('compose', [ComposeController::class, 'create'])
        ->middleware('permission:compose.create')
        ->name('app.compose.create');

    Route::post('compose/store', [ComposeController::class, 'store'])
        ->middleware('permission:compose.store')
        ->name('app.compose.store');
});

Route::middleware(['auth'])->group(function () {
    Route::get('sent', [SentController::class, 'index'])
        ->middleware('permission:sent.index')
        ->name('sent.index');

    Route::get('sent/{letter_id}', [SentController::class, 'show'])
        ->middleware('permission:sent.show')
        ->name('sent.show');

    Route::get('sent/{letter_id}/edit-letter', [SentController::class, 'editLetter'])
        ->middleware('permission:sent.edit')
        ->name('sent.editletter');

    Route::put('sent/{letter_id}/edit-letter', [SentController::class, 'updateLetter'])
        ->middleware('permission:sent.edit')
        ->name('sent.updateletter');

    Route::get('sent/{letter_id}/edit-recipients', [SentController::class, 'editRecipients'])
        ->middleware('permission:sent.edit')
        ->name('sent.editrecipients');

    Route::put('sent/{letter_id}/edit-recipients', [SentController::class, 'updateRecipients'])
        ->middleware('permission:sent.edit')
        ->name('sent.updaterecipients');

    Route::get('sent/{letter_id}/edit-docs', [SentController::class, 'editDocs'])
        ->middleware('permission:sent.edit')
        ->name('sent.editdocs');

    Route::post('sent/{letter_id}/edit-docs', [SentController::class, 'updateDocs'])
        ->middleware('permission:sent.edit')
        ->name('sent.updatedocs');
});

Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('timeline/recipient/{recipient}', [ApiController::class, 'recipientTimeline'])
        ->name('api.timeline.recipient');
});

require __DIR__.'/arsip.php';
require __DIR__.'/memo.php';
require __DIR__.'/disposition.php';
require __DIR__.'/inbox.php';
require __DIR__.'/master.php';
require __DIR__.'/settings.php';
