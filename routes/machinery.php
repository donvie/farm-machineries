<?php

use App\Http\Controllers\MachineryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('machinery', 'machinery');

    Route::get('machinery', [MachineryController::class, 'index'])->name('machinery.list');
    Route::post('machinery', [MachineryController::class, 'store'])->name('machinery.store');
    Route::patch('machinery/{machinery}', [MachineryController::class, 'update'])->name('machinery.update');
    Route::delete('machinery/{machinery}', [MachineryController::class, 'destroy'])->name('machinery.destroy');
});
