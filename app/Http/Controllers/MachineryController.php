<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machinery;
use Inertia\Inertia;

class MachineryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     // $machineries = Machinery::paginate(10); // Paginated data
    //     // $machineries = Machinery::all(); // Load all data
    //     $machineries = Machinery::with('maintainances.user')->get();
    //     return Inertia::render('Machinery', ['machineries' => ['data' => $machineries]]);
    // }

    public function index()
    {
        $machineries = Machinery::with(['maintainances.user', 'rentals.user', 'rentals.operator', 'rentals.machinery'])->get();
    
        return Inertia::render('Machinery', [
            'machineries' => [
                'data' => $machineries
            ]
        ]);
    }    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'machine_name' => 'required|string',
            // 'type' => 'required|string',
            'status' => 'required|string',
            'brand' => 'required|string',
            // 'costPerMachine' => 'required|string',
            'serial' => 'required|string',
            'capacity' => 'required|string',
            // 'accessories' => 'required|string',
            'supplier' => 'required|string',
            'branchAddress' => 'required|string',
            'year_acquired' => 'required|date',
            'last_maintenance_date' => 'nullable|date',
            'next_scheduled_maintenance' => 'nullable|date',
            'isDeactivate' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validate image
        ]);
    
        $data = $request->all();
    
        // Handle Image Upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('machinery_images', 'public');
            $data['machine_name'] = $imagePath;
        }
    
        Machinery::create($data);

        return back()->with('success', 'Machinery added successfully!');
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
    public function update(Request $request, Machinery $machinery)
    {
        $request->validate([
            // 'status' => 'required|in:Available,In Use,Under Maintenance',
            'next_scheduled_maintenance' => 'nullable|date',
        ]);

        $machinery->update($request->all());

        return back()->with('success', 'Machinery updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the machinery record by ID
        $machinery = Machinery::findOrFail($id);
    
        // Delete the machinery record
        $machinery->delete();
    
        // Redirect back with success message
        return back()->with('success', 'Machinery deleted successfully!');
    }
    
}
