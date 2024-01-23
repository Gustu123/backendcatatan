<?php

namespace App\Http\Controllers;

use App\Models\BudgetDetail;
use Illuminate\Http\Request;

class BudgetDetailController extends Controller
{
    public function index(Request $request)
    {
        $budgets = BudgetDetail::with('details')->where('user_id', $request->user()->id)->get();
        
        return response()->json(["data"=> $budgets]);
    }
}
