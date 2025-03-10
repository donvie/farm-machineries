<?php

use App\Http\Controllers\MaintainanceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('maintainance', 'maintainance');

    Route::get('maintainance', [MaintainanceController::class, 'index'])->name('maintainance.list');
    Route::post('maintainance', [MaintainanceController::class, 'store'])->name('maintainance.store');
    Route::patch('maintainance/{maintainance}', [MaintainanceController::class, 'update'])->name('maintainance.update');
    Route::delete('maintainance/{maintainance}', [MaintainanceController::class, 'destroy'])->name('maintainance.destroy');
});
