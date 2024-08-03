<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\Repair_details;
use Illuminate\Http\Request;

class RepairController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Repair::all());
        $output = Repair::where([['del', 1]])->get();
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
        $list = $request->except(['repair_details', 'id']);
        $listCreate = Repair::create($list);
        $listDetails = $request->repair_details;
        foreach ($listDetails as $key => $listDetail) {
            $listDetail['repair_id'] = $listCreate->id;
            Repair_details::create($listDetail);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\Http\Response
     */
    public function show(Repair $repair)
    {
        $repair->repair_details;
        return response()->json($repair);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\Http\Response
     */
    public function edit(Repair $repair)
    {
        // return response()->json($repair);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Repair $repair)
    {
        $repair->update($request->except(['repair_details', 'updated_at']));
        Repair_details::where('repair_id', $repair->id)->delete();

        $repairsDetails = $request->repair_details;

        foreach ($repairsDetails as $key => $repairsDetail) {
            $repairsDetail['repair_id'] = $repair->id;
            Repair_details::create($repairsDetail);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\Http\Response
     */
    public function destroy(Repair $repair, Request $request)
    {
        // $repair->delete();
        // return response()->json();
        $repair->repair_active = 0;
        $repair->del = 0;
        $repair->user_id = $request->user()->id;
        $repair->save();
        // Repair_details::where('repair_id', $repair->id)->delete();
    }

    public function SelectOnRepair()
    {
        return response()->json(Repair::where('repair_active', 1)->get());
    }
}
