<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use App\Models\Supply;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loans = Loan::with(['user'])->get();
        // $loans = Loan::all(); // Load all data
        $users = User::all(); // Load all users
        $supplies = Supply::all(); // Load all users

        return Inertia::render('Loan', [
            'loans' => ['data' => $loans],
            'supplies' => ['data' => $supplies],
            'users' => $users,]);
        }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            // 'amount' => 'required|numeric',
            // 'purpose' => 'required|string',
            // 'loans' => 'nullable|array', // 👈 add this for validation
            // add other validations if needed
        ]);
    
        $data = $request->all();

        if ($request->has('loans')) {
            $data['loans'] = json_decode($request->loans, true); // convert JSON string to array
        }

        
        if ($request->has('histories')) {
            $data['histories'] = json_decode($request->histories, true); // convert JSON string to array
        }
    
        // Convert 'loans' array to JSON if it exists
        // if ($request->has('loans') && is_array($request->loans)) {
            // $data['loans'] = json_encode($request->loans);
        // }
    
        Loan::create($data);
    
        return back()->with('success', 'Loan added successfully!');
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
    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            // 'name' => 'required|string',
            'user_id' => 'required|numeric',
            // 'amount' => 'required|numeric',
            // 'purpose' => 'required|string',
            // 'loans.*' => 'string',
        ]);

        $loan->update($request->all());

        if ($request->has('loans')) {
            // Ensure it's JSON before saving
            $data['loans'] = is_array($request->loans)
                ? json_encode($request->loans)
                : $request->loans;
        }

        
        if ($request->has('histories')) {
            // Ensure it's JSON before saving
            $data['histories'] = is_array($request->histories)
                ? json_encode($request->histories)
                : $request->histories;
        }
    
    

        return back()->with('success', 'Loan updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        $loan->delete();
        return back()->with('success', 'Loan deleted successfully!');
    }
}
