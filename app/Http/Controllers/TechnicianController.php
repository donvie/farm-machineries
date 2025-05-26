<?php

namespace App\Http\Controllers;
use App\Models\Technician;

use App\Models\Machinery;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TechnicianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $technicians = Technician::paginate(10); // Paginated data
        $machineries = Machinery::all(); // Load all users
        return Inertia::render('Technician', [
            'technicians' => $technicians  ,
            'machineries' => $machineries, // Add users to the response
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            // 'name' => 'nullable|string',
            // 'status' => 'nullable|string',
            // 'fields' => 'nullable|string',
            // 'remarks' => 'nullable|string',
        ]);

        Technician::create($request->all());

        return back()->with('success', 'Technician added successfully!');
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
    public function update(Request $request, Technician $technician)
    {
        $request->validate([
        ]);

        $technician->update($request->all());

        return back()->with('success', 'Technician updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Technician $technician)
    {
        $technician->delete();
        return back()->with('success', 'Technician deleted successfully!');
    }
}
