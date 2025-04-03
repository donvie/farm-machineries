<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{

    public function index()
    {
        $users = User::with(['rentals.user', 'rentals.machinery', 'loans.user', 'maintainances.user'])->get(); // Or paginate
        return Inertia::render('User', [
            'users' => [
                'data' => $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at,
                        'rentals' => $user->rentals, // Include rentals if needed in the table
                        'loans' => $user->loans, // Include rentals if needed in the table
                        'maintainances' => $user->maintainances, // Include rentals if needed in the table
                    ];
                })->toArray(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            // 'machine_name' => 'required|string',
            // 'type' => 'required|string',
            // 'unique_id' => 'required|string|unique:machineries',
            // 'brand' => 'required|string',
            // 'year_acquired' => 'required|integer',
            // 'capacity' => 'required|string',
            // 'serial_no' => 'nullable|string',
            // 'stocks' => 'required|integer',
            // 'status' => 'required|in:Available,In Use,Under Maintenance',
            // 'last_maintenance_date' => 'nullable|date',
            // 'next_scheduled_maintenance' => 'nullable|date',
        ]);

        User::create($request->all());

        return back()->with('success', 'User added successfully!');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            // 'status' => 'required|in:Available,In Use,Under Maintenance',
            // 'next_scheduled_maintenance' => 'nullable|date',
        ]);

        $user->update($request->all());

        return back()->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted successfully!');
    }
}