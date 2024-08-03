<?php

namespace App\Http\Controllers;

use App\Models\Car_part_type;
use Illuminate\Http\Request;

class CarPartTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Car_part_type::all());
        $output = Car_part_type::where([['del', 1]])->get();
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
        // $credentials['id'] = $credentials['id'] = null;
        Car_part_type::create($credentials);
        // return response()->json($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Car_part_type  $car_part_type
     * @return \Illuminate\Http\Response
     */
    public function show(Car_part_type $car_part_type)
    {
        return response()->json($car_part_type);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Car_part_type  $car_part_type
     * @return \Illuminate\Http\Response
     */
    public function edit(Car_part_type $car_part_type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Car_part_type  $car_part_type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Car_part_type $car_part_type)
    {
        $car_part_type->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Car_part_type  $car_part_type
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car_part_type $car_part_type, Request $request)
    {
        // $car_part_type->delete();
        $car_part_type->car_part_type_active = 0;
        $car_part_type->del = 0;
        $car_part_type->user_id = $request->user()->id;
        $car_part_type->save();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function SelectOnCarPartType()
    {
        return response()->json(Car_part_type::where('car_part_type_active', 1)->get());
    }
}
