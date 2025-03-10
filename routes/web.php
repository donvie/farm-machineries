<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/machinery_images/{filename}', function ($filename) {
    $path = storage_path("app/public/machinery_images/{$filename}");

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/machinery.php';
require __DIR__.'/rental.php';
require __DIR__.'/maintainance.php';
require __DIR__.'/loan.php';

