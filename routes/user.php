<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('user', 'user');

    Route::get('user', [UserController::class, 'index'])->name('user.list');
    Route::post('user', [UserController::class, 'store'])->name('user.store');
    Route::patch('user/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('user/{user}', [UserController::class, 'destroy'])->name('user.destroy');
});
