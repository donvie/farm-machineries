<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
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

        return Inertia::render('Loan', [
            'loans' => ['data' => $loans],
            'users' => $users,]);
        }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'status' => 'required|string',
            'remarks'  => 'required|string',
            'amount' => 'required|numeric',
            'purpose' => 'required|string',
            'loanDate' => 'required|date',
            'repaymentDate' => 'required|date',
            'remarks' => 'required|string',
        ]);

        Loan::create($request->all());

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
            'name' => 'required|string',
        ]);

        $loan->update($request->all());

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
