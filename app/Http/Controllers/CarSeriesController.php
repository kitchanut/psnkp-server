<?php

namespace App\Http\Controllers;

use App\Models\Car_series;
use Illuminate\Http\Request;

class CarSeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Car_series::with(['carType', 'carModel'])->where([['del', 1]])->get());
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
        Car_series::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Car_series  $car_series
     * @return \Illuminate\Http\Response
     */
    public function show(Car_series $car_series)
    {
        return response()->json($car_series);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Car_series  $car_series
     * @return \Illuminate\Http\Response
     */
    public function edit(Car_series $car_series)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Car_series  $car_series
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Car_series $car_series)
    {
        $car_series->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Car_series  $car_series
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car_series $car_series, Request $request)
    {
        // $car_series->delete();
        $car_series->car_series_active = 0;
        $car_series->del = 0;
        $car_series->user_id = $request->user()->id;
        $car_series->save();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function SelectOnCarSeries()
    {
        // return response()->json(Car_series::where('car_series_active', 1)->get());

        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            $allow_query = Car_series::where('car_series_active', 1)->get();
            return response()->json($allow_query);
        } else {
            if ($host == $allow_host) {
                $allow_query = Car_series::where('car_series_active', 1)->get();
                return response()->json($allow_query);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }

    public function SelectOnCarSerie($car_types_id, $car_models_id)
    {
        // return response()->json(Car_series::where([['car_type_id', $car_types_id], ['car_model_id', $car_models_id]])->get());
        $output = Car_series::where('car_model_id', $car_models_id)
            ->when($car_types_id, function ($query) use ($car_types_id) {
                return $query->where('car_type_id', $car_types_id);
            })
            ->get();
        return response()->json($output);
    }

    public function SelectOnCarSerieOnly($car_models_id)
    {
        return response()->json(Car_series::where([['car_model_id', $car_models_id]])->get());
    }
}
