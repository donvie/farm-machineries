<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;
use Inertia\Inertia;

class SupplyController extends Controller
{
   
    public function index()
    {
        $supplies = Supply::paginate(10); // Paginated data
        return Inertia::render('Supply', ['supplies' => $supplies]);
    }    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item' => 'required|string',
            'stocks' => 'required|string',
        ]);
    
        $data = $request->all();
    
        // Handle Image Upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('Supply_images', 'public');
            $data['machine_name'] = $imagePath;
        }
    
        Supply::create($data);

        return back()->with('success', 'Supply added successfully!');
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
    public function update(Request $request, Supply $Supply)
    {
        $request->validate([
            // 'status' => 'required|in:Available,In Use,Under Maintenance',
            // 'next_scheduled_maintenance' => 'nullable|date',
        ]);

        $Supply->update($request->all());

        return back()->with('success', 'Supply updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the Supply record by ID
        $Supply = Supply::findOrFail($id);
    
        // Delete the Supply record
        $Supply->delete();
    
        // Redirect back with success message
        return back()->with('success', 'Supply deleted successfully!');
    }
    
}
