<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Debt;
use App\Models\User;

class DebtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // $debts = Debt::where('user_id', $request->user()->id)->get();
        
        // return response()->json(["data"=> $debts]);

        $query =  Debt::where('user_id', $request->user()->id);
        if($request->year){
            $query = $query->whereYear('due_date', $request->year);
            
        }
        if($request->month){
            $query = $query->whereMonth('due_date', $request->month);
        }
        if($request->search){
            $query = $query->where('name', 'like', "%{$request->search}%");
        }
        
        $debts = $query->get();
        $totalDebt = $debts->sum('amount');

        return response()->json(["data"=> [
            'debt' => $debts,
            'totalDebts' => $totalDebt
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name"           => ['required'],
            "amount"         => ['required'],
            "due_date"       => ['required'],
        ]);

        $debts = new Debt();
        $debts->name          = $validated['name'];
        $debts->amount        = $validated['amount'];
        $debts->due_date      = $validated['due_date'];

        $user = User::find($request->user_id);
        $debts->user()->associate($user);
        $debts->save();

        return response()->json([], 201);
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Debt::destroy($id);

        return response()->json();
    }
}
