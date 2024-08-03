<?php

namespace App\Http\Controllers;

use App\Models\Stock_part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockPartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Stock_part::all());
        return response()->json(Stock_part::with(['branch', 'car_part'])->orderBy('car_part_amount', 'ASC')->orderBy('created_at', 'DESC')->get());
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stock_part  $stock_part
     * @return \Illuminate\Http\Response
     */
    public function show(Stock_part $stock_part)
    {
        return response()->json($stock_part);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stock_part  $stock_part
     * @return \Illuminate\Http\Response
     */
    public function edit(Stock_part $stock_part)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stock_part  $stock_part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Stock_part $stock_part)
    {
        $stock_part->update($request->except(['stock_type', 'updated_at', 'car_part_amount_before']));
        $credentials = $request->except(['id', 'car_part_amount']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stock_part  $stock_part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stock_part $stock_part)
    {
        //
    }

    public function StockOnPart($id)
    {
        $query = DB::table('car_parts')
            ->join('stock_parts', 'stock_parts.car_part_id', '=', 'car_parts.id')
            ->where('stock_parts.branch_id', $id)
            ->get();
        return response()->json($query);
    }
}
