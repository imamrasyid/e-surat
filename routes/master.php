<?php

use App\Http\Controllers\MasterData\KodeKlasifikasiController;
use App\Http\Controllers\MasterData\PositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('master-data')->middleware('auth')->group(function () {
    Route::prefix('jabatan')->group(function () {
        Route::get('/', [PositionController::class, 'index'])
            ->name('ref.position.index');
        Route::get('create', [PositionController::class, 'create'])
            ->name('ref.position.create');
        Route::post('store', [PositionController::class, 'store'])
            ->name('ref.position.store');
        Route::get('edit/{position}', [PositionController::class, 'edit'])
            ->name('ref.position.edit');
        Route::patch('update/{position}', [PositionController::class, 'update'])
            ->name('ref.position.update');
    });

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
