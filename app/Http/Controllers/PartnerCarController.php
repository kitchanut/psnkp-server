<?php

namespace App\Http\Controllers;

use App\Models\Partner_car;
use Illuminate\Http\Request;

class PartnerCarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Partner_car::all());
        // return response()->json(Partner_car::with(['province'])->get());
        $output = Partner_car::with(['province'])->where([['del', 1]])->get();
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
        $credentials = $request->except(['id', 'zip_code']);
        Partner_car::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Partner_car  $partner_car
     * @return \Illuminate\Http\Response
     */
    public function show(Partner_car $partner_car)
    {
        return response()->json($partner_car);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Partner_car  $partner_car
     * @return \Illuminate\Http\Response
     */
    public function edit(Partner_car $partner_car)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Partner_car  $partner_car
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Partner_car $partner_car)
    {
        $partner_car->update($request->except(['updated_at', 'zip_code']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Partner_car  $partner_car
     * @return \Illuminate\Http\Response
     */
    public function destroy(Partner_car $partner_car, Request $request)
    {
        // $partner_car->delete();
        $partner_car->partner_car_active = 0;
        $partner_car->del = 0;
        $partner_car->user_id = $request->user()->id;
        $partner_car->save();
    }


    public function selectOnPartnerCar()
    {
        return response()->json(Partner_car::where('partner_car_active', 1)->get());
    }
}
