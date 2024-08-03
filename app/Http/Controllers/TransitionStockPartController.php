<?php

namespace App\Http\Controllers;

use App\Models\Transition_stock_part;
use Illuminate\Http\Request;

class TransitionStockPartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $branch_id = $request->input('branch_id');

        $output = Transition_stock_part::with('car_part', 'branch')
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->where([['created_at', '>', $request->timeStart], ['created_at', '<', $request->timeEnd]])
            ->get();

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
     * @param  \App\Models\Transition_stock_part  $transition_stock_part
     * @return \Illuminate\Http\Response
     */
    public function show(Transition_stock_part $transition_stock_part)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transition_stock_part  $transition_stock_part
     * @return \Illuminate\Http\Response
     */
    public function edit(Transition_stock_part $transition_stock_part)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transition_stock_part  $transition_stock_part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transition_stock_part $transition_stock_part)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transition_stock_part  $transition_stock_part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transition_stock_part $transition_stock_part)
    {
        //
    }

    public function where_car_part($idPart)
    {
        // return response()->json($request);
        $data = Transition_stock_part::with('car_part', 'branch')->where('transition_stock_parts.car_part_id', $idPart)->get();
        return response()->json($data);
    }
}
