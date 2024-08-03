<?php

namespace App\Http\Controllers;

use App\Models\Car_lift;
use Illuminate\Http\Request;

class CarLiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Car_lift::get());
        $output = Car_lift::where([['del', 1]])->get();
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
        $credentials = $request->except(['id']);
        Car_lift::create($credentials);
        // return response()->json($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Car_lift  $car_lift
     * @return \Illuminate\Http\Response
     */
    public function show(Car_lift $car_lift)
    {
        return response()->json($car_lift);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Car_lift  $car_lift
     * @return \Illuminate\Http\Response
     */
    public function edit(Car_lift $car_lift)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Car_lift  $car_lift
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Car_lift $car_lift)
    {
        $car_lift->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Car_lift  $car_lift
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car_lift $car_lift, Request $request)
    {
        // $car_lift->delete();
        $car_lift->carlift_status = 0;
        $car_lift->carlift_active = 0;
        $car_lift->del = 0;
        $car_lift->user_id = $request->user()->id;
        $car_lift->save();
    }

    public function SelectOnCarLift()
    {
        return response()->json(Car_lift::where('carlift_status', 1)->where('carlift_active', 1)->get());
    }
}
