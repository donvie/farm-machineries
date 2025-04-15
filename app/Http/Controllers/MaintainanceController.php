<?php

namespace App\Http\Controllers;

use App\Models\Maintainance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Machinery;
use App\Models\User;

class MaintainanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $maintainances = Maintainance::with(['user', 'machinery'])->get();
        $users = User::all(); // Load all users
        $machineries = Machinery::all(); // Load all users

        return Inertia::render('Maintainance', [
            'maintainances' => ['data' => $maintainances],
            'machineries' => $machineries, // Add users to the response
            'users' => $users, // Add users to the response
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'machinery_id' => 'required|numeric',
            'status' => 'nullable|string',
            'maintainance_date' => 'nullable|date',
            // 'date_of_rent' => 'required|date',
            'remarks'  => 'nullable|string',
        ]);

        Maintainance::create($request->all());

        return back()->with('success', 'Maintainance added successfully!');
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
    public function update(Request $request, Maintainance $maintainance)
    {
        $request->validate([
            // 'status' => 'required|in:Available,In Use,Under Maintenance',
            'completed_date' => 'nullable|date',
        ]);

        $maintainance->update($request->all());

        return back()->with('success', 'Maintainance updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Maintainance $maintainance)
    {
        $maintainance->delete();
        return back()->with('success', 'Maintainance deleted successfully!');
    }
}
