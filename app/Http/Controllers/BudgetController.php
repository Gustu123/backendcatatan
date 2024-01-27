<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $budgets = Budget::with('details')->where('user_id', $request->user()->id)->get();

        // return response()->json(["data"=> $budgets]);

        // $query = Budget::with(['details' => function (Builder $query){
        //     $query->transaction->sum('amount');
        // }])->where('user_id', $request->user()->id);

        $query =  Budget::with('details')->where('user_id', $request->user()->id);
        if ($request->year) {
            $query = $query->whereYear('expride_date', $request->year);
        }
        if ($request->month) {
            $query = $query->whereMonth('expride_date', $request->month);
        }
        if ($request->search) {
            $query = $query->where('name', 'like', "%{$request->search}%");
        }

        $budgets = $query->get();
        $totalBudget = $budgets->sum('amount');


        return response()->json([
            "data" => [
                'budgets' => $budgets,
                'totalbudget' => $totalBudget
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
            "expride_date"   => ['required', 'date_format:Y-m-d'],
            "details.*.name" => ['required']
        ]);


        $budgetData = new Budget();
        $budgetData->name          = $validated['name'];
        $budgetData->amount        = $validated['amount'];
        $budgetData->expride_date  = $validated['expride_date'];

        $user = User::find($request->user_id);
        $budgetData->user()->associate($user);
        $budgetData->save();
        $budgetData->refresh();


        $budgetData->details()->createMany($validated['details']);

        // for ($i = 0; $i < count($budgetDetailData); $i++){
        //     $budgetData->details[$i]->name = $budgetDetailData[$i]['name'];
        // }

        return response()->json([], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $budget = Budget::find($id);

        return response()->json(["data" => $budget]);
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
        Budget::destroy($id);

        return response()->json();
    }
}
