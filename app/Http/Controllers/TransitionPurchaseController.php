<?php

namespace App\Http\Controllers;

use App\Models\Transition_purchase;
use Illuminate\Http\Request;

class TransitionPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $branch_id = $request->input('branch_id');

        $output = Transition_purchase::with(['user', 'branch'])
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->where([['created_at', '>', $request->timeStart], ['created_at', '<', $request->timeEnd]])
            ->orderBy('created_at', 'DESC')->get();

        return response()->json($output);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transition_purchase  $transition_purchase
     * @return \Illuminate\Http\Response
     */
    public function show(Transition_purchase $transition_purchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transition_purchase  $transition_purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Transition_purchase $transition_purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transition_purchase  $transition_purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transition_purchase $transition_purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transition_purchase  $transition_purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transition_purchase $transition_purchase)
    {
        //
    }


    public function where_id($id)
    {
        return response()->json(Transition_purchase::with(['user', 'branch'])->where('transition_purchases.purchase_id', $id)->get());
    }
}
