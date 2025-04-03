<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rental;
use App\Models\User;
use App\Models\Machinery;
use Inertia\Inertia;

class RentalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rentals = Rental::with(['user', 'machinery'])->get();

        // Convert machinery_id to an integer
        // $rentals = $rentals->map(function ($rental) {
        //     $rental->machinery_id = (int) $rental->machinery_id;
        //     return $rental;
        // });
        
        $users = User::all(); // Load all users
        $machineries = Machinery::all(); // Load all users
    
        return Inertia::render('Rental', [
            'rentals' => ['data' => $rentals],
            'users' => $users, // Add users to the response
            'machineries' => $machineries, // Add users to the response
        ]);
        // $rentals = Rental::all(); // Load all data
        // return Inertia::render('Rental', ['rentals' => ['data' => $rentals]]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            // 'name' => 'required|string',
            'user_id' => 'required|numeric',
            'machinery_id' => 'required|numeric',
            'status' => 'required|string',
            // 'date_of_rent' => 'required|date',
            'remarks'  => 'required|string',
        ]);

        Rental::create($request->all());

        return back()->with('success', 'Rental added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rental $rental)
    {
        $request->validate([
            // 'user_id' => 'sometimes|required|numeric',
            // 'machinery_id' => 'sometimes|required|numeric',
            // 'status' => 'sometimes|required|string',
            // 'remarks' => 'sometimes|required|string',
            // Add other validation rules as needed for updating a rental
        ]);
    
        $rental->update($request->all());
    
        return back()->with('success', 'Rental updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rental $rental)
    {
        $rental->delete();
        return back()->with('success', 'Rental deleted successfully!');
    }
}
