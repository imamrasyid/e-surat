<?php

use App\Http\Controllers\MasterData\FollowupController;
use App\Http\Controllers\MasterData\KodeKlasifikasiController;
use App\Http\Controllers\MasterData\PositionController;
use App\Http\Controllers\MasterData\RoleController;
use App\Http\Controllers\MasterData\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('master-data')->middleware('auth')->group(function () {
    Route::get('/', function () {
        return Inertia::render('master/Index', [
            'breadcrumbItems' => [
                ['title' => 'Master Data', 'route' => 'ref.master-data'],
            ],
        ]);
    })
        ->middleware('wpermission:master.*')
        ->name('ref.master-data');

    Route::prefix('users')->middleware('permission:master.pengguna')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->name('ref.user.index');

        Route::get('create', [UserController::class, 'create'])
            ->name('ref.user.create');

        Route::post('store', [UserController::class, 'store'])
            ->name('ref.user.store');

        Route::get('edit/{user}', [UserController::class, 'edit'])
            ->name('ref.user.edit');

        Route::patch('update/{user}', [UserController::class, 'update'])
            ->name('ref.user.update');

        Route::get('edit/position/{user}', [UserController::class, 'editPosition'])
            ->name('ref.user.editPosition');

        Route::post('update/position/{user}', [UserController::class, 'updatePosition'])
            ->name('ref.user.updatePosition');

        Route::get('edit/role/{user_id}', [UserController::class, 'editRole'])
            ->name('ref.user.editRole');

        Route::post('update/role/{user}', [UserController::class, 'updateRole'])
            ->name('ref.user.updateRole');

        Route::post('update/spesial-position/{user}', [UserController::class, 'updateSpecialDisposition'])
            ->name('ref.user.updateDisposition');
    });

    Route::prefix('positions')->middleware('permission:master.jabatan')->group(function () {
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

    Route::prefix('classification')->middleware('permission:master.klasifikasi')->group(function () {
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

    Route::prefix('followup')->middleware('permission:master.followup')->group(function () {
        Route::get('/', [FollowupController::class, 'index'])
            ->name('ref.followup.index');

        Route::get('create', [FollowupController::class, 'create'])
            ->name('ref.followup.create');

        Route::post('store', [FollowupController::class, 'store'])
            ->name('ref.followup.store');

        Route::get('edit/{followup}', [FollowupController::class, 'edit'])
            ->name('ref.followup.edit');

        Route::patch('update/{followup}', [FollowupController::class, 'update'])
            ->name('ref.followup.update');
    });

    Route::prefix('roles')->middleware('permission:master.pengguna')->group(function () {
        Route::get('/', [RoleController::class, 'index'])
            ->name('ref.role.index');

        Route::get('create', [RoleController::class, 'create'])
            ->name('ref.role.create');

        Route::post('store', [RoleController::class, 'store'])
            ->name('ref.role.store');

        Route::get('edit/{role}', [RoleController::class, 'edit'])
            ->name('ref.role.edit');

        Route::patch('update/{role}', [RoleController::class, 'update'])
            ->name('ref.role.update');

        Route::patch('update/permissions/{role}', [RoleController::class, 'updatePermission'])
            ->name('ref.role.updatePermission');
    });
});
