<?php

namespace App\Http\Controllers;

use App\Models\Car_type;
use Illuminate\Http\Request;

class CarTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = Car_type::where([['del', 1]])->get();
        return response()->json($output);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json(Car_type::all());
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
        Car_type::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Car_type  $car_type
     * @return \Illuminate\Http\Response
     */
    public function show(Car_type $car_type)
    {
        return response()->json($car_type);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Car_type  $car_type
     * @return \Illuminate\Http\Response
     */
    public function edit(Car_type $car_type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Car_type  $car_type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Car_type $car_type)
    {
        $car_type->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Car_type  $car_type
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car_type $car_type, Request $request)
    {
        // $car_type->delete();
        $car_type->car_type_active = 0;
        $car_type->del = 0;
        $car_type->user_id = $request->user()->id;
        $car_type->save();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function SelectOnCarType()
    {
        // return response()->json(Car_type::where('car_type_active',1)->get());
        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            $allow_query = Car_type::where('car_type_active', 1)->get();
            return response()->json($allow_query);
        } else {
            if ($host == $allow_host) {
                $allow_query = Car_type::where('car_type_active', 1)->get();
                return response()->json($allow_query);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }
}
