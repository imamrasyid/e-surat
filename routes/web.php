<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('app/compose', function () {
    return Inertia::render('app/Compose');
})->middleware(['auth', 'verified'])->name('app.compose');

require __DIR__.'/master.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
