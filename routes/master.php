<?php

use App\Http\Controllers\MasterData\KodeKlasifikasiController;
use Illuminate\Support\Facades\Route;

Route::prefix('master-data')->middleware('auth')->group(function () {
    Route::prefix('klasifikasi-arsip')->group(function () {
        Route::get('/', [KodeKlasifikasiController::class, 'index'])
            ->name('ref.klasifikasi.index');
        Route::get('create', [KodeKlasifikasiController::class, 'create'])
            ->name('ref.klasifikasi.create');
        Route::post('store', [KodeKlasifikasiController::class, 'store'])
            ->name('ref.klasifikasi.store');
        Route::get('edit/{kode}', [KodeKlasifikasiController::class, 'edit'])
            ->name('ref.klasifikasi.edit');
        Route::patch('update/{kode}', [KodeKlasifikasiController::class, 'update'])
            ->name('ref.klasifikasi.update');
    });
});
