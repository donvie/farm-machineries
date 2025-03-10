<?php

use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('loan', 'loan');

    Route::get('loan', [LoanController::class, 'index'])->name('loan.list');
    Route::post('loan', [LoanController::class, 'store'])->name('loan.store');
    Route::patch('loan/{loan}', [LoanController::class, 'update'])->name('loan.update');
    Route::delete('loan/{loan}', [LoanController::class, 'destroy'])->name('loan.destroy');
});
