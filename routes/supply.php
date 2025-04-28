<?php

use App\Http\Controllers\SupplyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('supply', 'supply');

    Route::get('supply', [SupplyController::class, 'index'])->name('supply.list');
    Route::post('supply', [SupplyController::class, 'store'])->name('supply.store');
    Route::patch('supply/{supply}', [SupplyController::class, 'update'])->name('supply.update');
    Route::delete('supply/{supply}', [SupplyController::class, 'destroy'])->name('supply.destroy');
});
