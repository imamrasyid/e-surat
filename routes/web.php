<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ComposeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LetterDocController;
use App\Http\Controllers\SentController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Webhook Telegram: tanpa auth, diamankan oleh header secret token.
// CSRF dikecualikan di bootstrap/app.php, feature gate mematikan route
// sepenuhnya pada server yang tidak mengaktifkan COMPANY_FEATURES=telegram.
Route::post('telegram/webhook', TelegramWebhookController::class)
    ->middleware('feature:telegram')
    ->name('telegram.webhook');

Route::get('/about', function () {
    return Inertia::render('about/Index');
})->name('about');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])->name('dashboard');

Route::get('dashboard/chart-data', [DashboardController::class, 'chartData'])
    ->middleware(['auth'])->name('dashboard.chart-data');

Route::get('dashboard/user-chart-data', [DashboardController::class, 'userChartData'])
    ->middleware(['auth'])->name('dashboard.user-chart-data');

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
        ->middleware('permission:sent.update')
        ->name('sent.updateletter');

    Route::get('sent/{letter_id}/edit-recipients', [SentController::class, 'editRecipients'])
        ->middleware('permission:sent.edit')
        ->name('sent.editrecipients');

    Route::put('sent/{letter_id}/edit-recipients', [SentController::class, 'updateRecipients'])
        ->middleware('permission:sent.update')
        ->name('sent.updaterecipients');

    Route::get('sent/{letter_id}/edit-docs', [SentController::class, 'editDocs'])
        ->middleware('permission:sent.edit')
        ->name('sent.editdocs');

    Route::post('sent/{letter_id}/edit-docs', [SentController::class, 'updateDocs'])
        ->middleware('permission:sent.update')
        ->name('sent.updatedocs');

    Route::delete('sent/{letter_id}', [SentController::class, 'destroy'])
        ->middleware('permission:sent.update')
        ->name('sent.destroy');
});

Route::middleware(['auth'])->group(function () {
    // Lampiran privat: akses diperiksa di controller terhadap surat terkait.
    Route::get('letter-docs/{doc}/download', [LetterDocController::class, 'download'])
        ->name('letter.docs.download');

    Route::get('letter-docs/{doc}/preview', [LetterDocController::class, 'preview'])
        ->name('letter.docs.preview');

    Route::get('api/timeline/recipient/{recipient}', [ApiController::class, 'recipientTimeline'])
        ->name('api.timeline.recipient');
});

require __DIR__.'/arsip.php';
require __DIR__.'/memo.php';
require __DIR__.'/disposition.php';
require __DIR__.'/inbox.php';
require __DIR__.'/master.php';
require __DIR__.'/notifications.php';
require __DIR__.'/settings.php';
