<?php

use App\Http\Controllers\RentalController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('rental', 'rental');

    Route::get('rental', [RentalController::class, 'index'])->name('rental.list');
    Route::post('rental', [RentalController::class, 'store'])->name('rental.store');
    Route::patch('rental/{rental}', [RentalController::class, 'update'])->name('rental.update');
    Route::delete('rental/{rental}', [RentalController::class, 'destroy'])->name('rental.destroy');
});
