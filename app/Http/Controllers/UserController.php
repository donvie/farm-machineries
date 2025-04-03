<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10); // Paginated data
        return Inertia::render('User', ['users' => $users]);
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