<?php

namespace App\Http\Controllers;

use App\Models\Stock_part;
use App\Models\Transition_stock_part;
use App\Models\Transition_withdraw_part;
use App\Models\Withdraw_part;
use Illuminate\Http\Request;

class WithdrawPartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Withdraw_part::all());
        // return response()->json(Withdraw_part::with(['part', 'user', 'branch', 'car'])->where('withdraw_part_status', '=', 1)->get());
        // return response()->json(Withdraw_part::with(['part', 'user', 'branch', 'car'])->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $credentials = $request->except(['id']);
        $createWithdraw_part = Withdraw_part::create($credentials);

        Transition_withdraw_part::create($credentials + ['withdraw_parts_id' => $createWithdraw_part->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Withdraw_part  $withdraw_part
     * @return \Illuminate\Http\Response
     */
    public function show(Withdraw_part $withdraw_part)
    {
        return response()->json($withdraw_part);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Withdraw_part  $withdraw_part
     * @return \Illuminate\Http\Response
     */
    public function edit(Withdraw_part $withdraw_part)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Withdraw_part  $withdraw_part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Withdraw_part $withdraw_part)
    {

        $queryWithdraw_part = Withdraw_part::find($request->id);
        $queryWithdraw_part->car_part_id = $request->car_part_id;

        if ($request->balance_part_amount != 0) {
            $queryWithdraw_part->car_part_amount = $request->balance_part_amount;
            $queryWithdraw_part->withdraw_part_amount = 0;
            $queryWithdraw_part->balance_part_amount = 0;
        } else {
            $queryWithdraw_part->car_part_amount = $request->car_part_amount;
            $queryWithdraw_part->withdraw_part_amount = $request->withdraw_part_amount;
            $queryWithdraw_part->balance_part_amount = $request->balance_part_amount;
        }

        $queryWithdraw_part->user_id = $request->user_id;
        $queryWithdraw_part->branch_id = $request->branch_id;
        if ($queryWithdraw_part->withdraw_part_status == 3) {
            $queryWithdraw_part->withdraw_part_status = 4;
        } else {
            $queryWithdraw_part->withdraw_part_status = $request->balance_part_amount == 0 ? 2 : 1;
        }
        $queryWithdraw_part->save();

        Transition_withdraw_part::create([
            'job_technician_id' => $request->job_technician_id,
            'car_id' => $request->car_id,
            'withdraw_parts_id' => $request->id,
            'car_part_id' => $request->car_part_id,
            'car_part_amount' => $queryWithdraw_part->car_part_amount,
            'withdraw_part_amount' =>  $queryWithdraw_part->withdraw_part_amount,
            'balance_part_amount' => $queryWithdraw_part->balance_part_amount,
            'user_id' => $request->user_id,
            'branch_id' => $request->branch_id,
            'withdraw_part_status' => $queryWithdraw_part->withdraw_part_status == 3 ? 4 : $queryWithdraw_part->withdraw_part_status
        ]);

        if ($request->withdraw_part_status != 3) {
            $queryStock = Stock_part::where([['car_part_id', 9], ['branch_id', $request->branch_id]])
                ->first();

            if ($request->balance_part_amount != 0) {
                $queryStock->car_part_amount = $queryStock->car_part_amount - $request->withdraw_part_amount;
            } else {
                $queryStock->car_part_amount = $queryStock->car_part_amount - $request->car_part_amount;
            }
            $queryStock->save();

            Transition_stock_part::create([
                'stock_part_id' => $queryStock['id'],
                'car_part_id' => $queryStock['car_part_id'],
                'car_part_amount_before' => $request->balance_part_amount != 0 ?
                    $queryStock->car_part_amount + $request->withdraw_part_amount :
                    $queryStock->car_part_amount + $request->car_part_amount,

                'car_part_amount' => $request->balance_part_amount != 0 ? $request->withdraw_part_amount : $request->car_part_amount,

                'car_part_balance' => $queryStock->car_part_amount,

                'branch_id' => $request->branch_id,
                'stock_type' => 2
            ]);
        }


        // $queryStock = Stock_part::where([['car_part_id', $request->car_part_id], ['branch_id', $request->branch_id]])
        //     ->first();

        // $queryStock->car_part_amount = $queryStock->car_part_amount - $request->car_part_amount;
        // $queryStock->save();

        // return response()->json($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Withdraw_part  $withdraw_part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Withdraw_part $withdraw_part)
    {
        //
    }


    public function withdrawWhere(Request $request)
    {
        // return response()->json($request);
        if ($request->branch_id == 0) {
            $data = Withdraw_part::with(['part', 'user', 'branch', 'car'])
                ->where([['withdraw_parts.withdraw_part_status', '=', 1]])
                ->get();
        } else {
            $data =  Withdraw_part::with(['part', 'user', 'branch', 'car'])
                ->where([['withdraw_parts.withdraw_part_status', '=', 1], ['withdraw_parts.branch_id', '=', $request->branch_id]])
                ->get();
        }
        return response()->json($data);
    }
}
