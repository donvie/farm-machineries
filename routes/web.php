<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\machinery;
use App\Models\maintainance;
use App\Models\rental;
use App\Models\loan;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\EmailController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard', [
        'machineries' => machinery::count(),
        'machineriesData' => machinery::all(),
        'maintainances' => maintainance::count(),
        'maintainancesData' => maintainance::all(),
        'loans' => loan::count(),
        'loansData' => loan::all(),
        'rentals' => rental::count(),

    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/machinery_images/{filename}', function ($filename) {
    $path = storage_path("app/public/machinery_images/{$filename}");

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});

Route::post('/send-email', [EmailController::class, 'sendEmail']);

Route::post('/send-sms', function (Request $request) {
    $apiKey = '300960592c929da2c6356355223a2f33';

    Log::info('Sending SMS request:', [ //Corrected line.
        'phone' => $request->phone,
        'message' => $request->message,
    ]);

    try {
        $response = Http::post('https://semaphore.co/api/v4/messages', [
            'apikey' => $apiKey,
            'number' => $request->phone,
            'message' => $request->message,
            'sendername' => 'SEMAPHORE',
        ]);

        Log::info('Semaphore API response:', $response->json()); //Corrected line.

        return response()->json($response->json());
    } catch (\Exception $e) {
        Log::error('Error sending SMS:', ['error' => $e->getMessage()]); //Corrected line.
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/machinery.php';
require __DIR__.'/rental.php';
require __DIR__.'/maintainance.php';
require __DIR__.'/loan.php';
require __DIR__.'/supply.php';
require __DIR__.'/user.php';
require __DIR__.'/technician.php';


