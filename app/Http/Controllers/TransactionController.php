<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\Debt;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;
use Termwind\Components\Raw;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query =  Transaction::where('user_id', $request->user()->id);
        $query = $query->with(['purposable' => function (MorphTo $morphTo) {
            $morphTo->constrain([
                BudgetDetail::class => function ($query) {
                    $query->with('budget');
                }
            ]);
        }]);
        if ($request->day) {
            $query = $query->whereDate('created_at', $request->day);
        }
        if ($request->month) {
            $query = $query->whereMonth('created_at', $request->month);
        }
        if ($request->search) {
            $query = $query->where('name', 'like', "%{$request->search}%");
        }

        $transactions = $query->get();
        $totalCashIn = $transactions->where('cash_out', '0')->sum('amount');
        $totalCashOut = $transactions->where('cash_out', '1')->sum('amount');


        return response()->json([
            "data" => [
                'transactions' => $transactions,
                'totalCashIn' => $totalCashIn,
                'totalCashOut' => $totalCashOut
            ]
        ]);
    }

    /**
     * Display a listing of the resource.
     */

    public function exportToExcel($cashOut = 1)
    {
        $transaction = Transaction::where('cash_out', $cashOut);

        $fileName = 'cash-in.xlsx';

        if ($cashOut) {
            $transaction = $transaction->with(['purposable' => function (MorphTo $morphTo) {
                $morphTo->constrain([
                    BudgetDetail::class => function ($query) {
                        $query->select(['id','budget_id','name'])->with('budget:id,name');
                    },
                    Debt::class => function ($query) {
                        $query->select(['id','name']);
                    }
                ]);
            }]);

            $fileName = 'cash-out.xlsx';
        }

        $transaction = $transaction->get();

        return (new FastExcel($transaction))->download($fileName, function ($transaction) {
            return [
                'name' => $transaction->name,
                'description' => $transaction->description,
                'amount' => $transaction->amount
                // 'purposable' => $transaction->purposable->?
            ];
        });
    }

    public function exportToPdf($cashOut = 1)
    {
        $transaction = Transaction::where('cash_out', $cashOut);

        $fileName = 'cash-in.pdf';

        if ($cashOut) {
            $transaction = $transaction->with(['purposable' => function (MorphTo $morphTo) {
                $morphTo->constrain([
                    BudgetDetail::class => function ($query) {
                        $query->select(['id','budget_id','name'])->with('budget:id,name');
                    },
                    Debt::class => function ($query) {
                        $query->select(['id','name']);
                    }
                ]);
            }]);

            $fileName = 'cash-out.pdf';
        }

        $transaction = $transaction->select(['id','name','deskripsi','amount','purposable_id','purposable_type'])->get();
        return $transaction;
        // $pdf = Pdf::loadHTML('transaction', $transaction->toArray());
        // return $pdf->download($fileName);
    }
    public function persentase(Request $request)
    {
        $query =  Transaction::where('user_id', $request->user()->id)->get();
        $countCashIn = $query->where('cash_out', '0')->count();
        $countCashOut = $query->where('cash_out', '1')->count();
        $totalCashIn = $query->where('cash_out', '0')->sum('amount');
        $totalCashOut = $query->where('cash_out', '1')->sum('amount');
        $totalLingkaran = $countCashIn + $countCashOut;

        $persentaseIn = ($countCashIn / $totalLingkaran) * 100;
        $persentaseOut = ($countCashOut / $totalLingkaran) * 100;

        $cashIn = $query->where('cash_out', '0')->pluck('amount', 'created_at');
        $cashOut = $query->where('cash_out', '1')->pluck('amount', 'created_at');

        // $cash = $query->where('cash_out', '0')->groupBy(DB::raw('MONTH(created_at)'));
        // dd($cash);

        return response()->json([
            "data" => [
                'totalCashIn' => $totalCashIn,
                'totalCashOut' => $totalCashOut,
                'persentaseIn' => $persentaseIn,
                'persentaseOut' => $persentaseOut,
                'cashIn' => $cashIn,
                'cashOut' => $cashOut
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function search(Request $request)
    {
        if ($request->has('search')) {
            $transaction = Transaction::where('name', 'LIKE', '%' . $request->search . '%')->get();
        } else {
            $transaction = Transaction::all();
        }
        return response()->json(["data" => $transaction]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            "name"           => ['required'],
            "amount"         => ['required'],
            "deskripsi"      => ['required'],
            "receipt"        => ['nullable'],
            "cashOut"        => ['required', 'boolean']
        ]);
        //belum validasi file

        $transaction = new Transaction();

        $transaction->name          = $validated['name'];
        $transaction->amount        = $validated['amount'];
        $transaction->deskripsi     = $validated['deskripsi'];
        $transaction->cash_out      = $validated['cashOut'];
        $transaction->receipt       = $validated['receipt'];

        $user = User::find($request->user_id);
        $wallet = Wallet::find($request->wallet_id);

        $transaction->user()->associate($user);
        $transaction->wallet()->associate($wallet);

        if ($request->cashOut) {
            if ($request->purpose == 'debt') {
                $purposable = Debt::find($request->purposeId);

                //menambahkan amount_paid dna ubah status jika sudah lunas
                $this->payDebt($purposable, $validated['amount']);
            } else {
                $purposable = BudgetDetail::find($request->purposeId);
            }

            $this->decreaseMoney($wallet, $validated['amount']);
            $transaction->purposable()->associate($purposable);
            if ($request->hasFile('receipt')){
                $path = $request->file('receipt')->store($transaction->getDirName());
            
                $transaction->receipt = $path;
            }
        } else {
            $this->increaseMoney($wallet, $validated['amount']);
        }

        $transaction->save();
        return response()->json([], 201);
    }

    private function decreaseMoney(Wallet $wallet, $amount)
    {
        $wallet->total_amount -= $amount;
        $wallet->save();
    }
    private function increaseMoney(Wallet $wallet, $amount)
    {
        $wallet->total_amount += $amount;
        $wallet->save();
    }

    private function payDebt(Debt $debt, $amount)
    {

        if ($debt->amount_paid + $amount >= $debt->amount) {
            $debt->status = 'lunas';
        }

        $debt->amount_paid += $amount;
        $debt->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::find($id);

        return response()->json(["data" => $transaction]);
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
        Transaction::destroy($id);

        return response()->json();
    }
}
