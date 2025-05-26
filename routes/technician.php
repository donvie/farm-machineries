<?php

use App\Http\Controllers\TechnicianController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('technician', 'technician');

    Route::get('technician', [TechnicianController::class, 'index'])->name('technician.list');
    Route::post('technician', [TechnicianController::class, 'store'])->name('technician.store');
    Route::patch('technician/{technician}', [TechnicianController::class, 'update'])->name('technician.update');
    Route::delete('technician/{technician}', [TechnicianController::class, 'destroy'])->name('technician.destroy');
});
