<?php

namespace App\Http\Controllers;

use App\Models\Car_serie_sub;
use Illuminate\Http\Request;

class CarSerieSubController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Car_serie_sub::with(['carSerie'])->where([['del', 1]])->get());
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
        Car_serie_sub::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Car_serie_sub  $Car_serie_sub
     * @return \Illuminate\Http\Response
     */
    public function show(Car_serie_sub $Car_serie_sub)
    {
        return response()->json($Car_serie_sub);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Car_serie_sub  $Car_serie_sub
     * @return \Illuminate\Http\Response
     */
    public function edit(Car_serie_sub $Car_serie_sub)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Car_serie_sub  $Car_serie_sub
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Car_serie_sub $Car_serie_sub)
    {
        $Car_serie_sub->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Car_serie_sub  $Car_serie_sub
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car_serie_sub $Car_serie_sub, Request $request)
    {
        // $Car_serie_sub->delete();
        $Car_serie_sub->car_serie_sub_active = 0;
        $Car_serie_sub->del = 0;
        $Car_serie_sub->user_id = $request->user()->id;
        $Car_serie_sub->save();
    }


    public function SelectOnCarSerieSubs()
    {
        // return response()->json(Car_serie_sub::where('car_serie_sub_active', 1)->get());

        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            $allow_query = Car_serie_sub::where('car_serie_sub_active', 1)->get();
            return response()->json($allow_query);
        } else {
            if ($host == $allow_host) {
                $allow_query = Car_serie_sub::where('car_serie_sub_active', 1)->get();
                return response()->json($allow_query);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }

    public function SelectOnCarSerieSub($id)
    {
        return response()->json(Car_serie_sub::where([['car_serie_id', $id], ['car_serie_sub_active', 1]])->get());
    }
}
