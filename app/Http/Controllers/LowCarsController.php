<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Car_model;
use App\Models\Car_serie_sub;
use App\Models\Car_series;
use App\Models\LowCars;
use Illuminate\Http\Request;

class LowCarsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = LowCars::with('car_models', 'car_series', 'car_serie_sub')->get();
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
        $credentials = $request->cars;

        for ($i = 0; $i < count($credentials); $i++) {
            unset($credentials[$i]['car_year']);
            unset($credentials[$i]['data_car_serice']);
            unset($credentials[$i]['data_car_sub_serice']);
            LowCars::create($credentials[$i]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LowCars  $lowCars
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $new_array = [];
        $LowCars = LowCars::find($id);
        $max = date('Y');
        $min = (int)$max - 50;
        $car_year = [];
        $data_car_serice = Car_series::where('car_model_id', $LowCars->car_models_id)->get();
        $data_car_sub_serice = Car_serie_sub::where('car_serie_id', $LowCars->car_serie_id)->get();
        $LowCars->data_car_serice = $data_car_serice;
        $LowCars->data_car_sub_serice = $data_car_sub_serice;

        for ($i = $min; $i < $max; $i++) {
            array_push($car_year, $i);
        }
        $LowCars->car_year = $car_year;
        array_push($new_array, $LowCars);
        return response()->json(['cars' => $new_array]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LowCars  $lowCars
     * @return \Illuminate\Http\Response
     */
    public function edit(LowCars $lowCars)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LowCars  $lowCars
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LowCars $lowCars)
    {
        // $lowCars->update($request->except(['updated_at']));
        // return response()->json($request);
        $credentials = $request->cars;

        for ($i = 0; $i < count($credentials); $i++) {
            unset($credentials[$i]['car_year']);
            unset($credentials[$i]['data_car_serice']);
            unset($credentials[$i]['data_car_sub_serice']);
            LowCars::find($credentials[$i]['id'])->update($credentials[$i]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LowCars  $lowCars
     * @return \Illuminate\Http\Response
     */
    // public function destroy(LowCars $lowCars)
    public function destroy($id)
    {
        LowCars::find($id)->delete();
    }

    public function showLowCars()
    {
        $lowCars = LowCars::select('car_models_id', 'car_serie_id', 'car_serie_sub_id', 'years', 'number')
            ->where([['active', 1]])
            ->get();
        $new_array = [];
        for ($i = 0; $i < count($lowCars); $i++) {
            $car = Car::select('car_models_id', 'car_serie_id', 'car_serie_sub_id', 'car_year')
                ->where([
                    ['car_models_id', $lowCars[$i]->car_models_id],
                    ['car_serie_id', $lowCars[$i]->car_serie_id],
                    ['car_serie_sub_id', $lowCars[$i]->car_serie_sub_id],
                    ['car_year', $lowCars[$i]->years],
                    ['car_sale_date', NULL]
                ])
                ->count();
            if ($lowCars[$i]->number <= $car) {
                $model_name = Car_model::select('id', 'car_model_name')->find($lowCars[$i]->car_models_id);
                $serie_name = Car_series::select('id', 'car_series_name')->find($lowCars[$i]->car_serie_id);
                $serie_sub_name = Car_serie_sub::select('id', 'car_serie_sub_name')->find($lowCars[$i]->car_serie_sub_id);

                array_push($new_array, (object)[
                    'model_name' => $model_name->car_model_name,
                    'serie_name' => $serie_name->car_series_name,
                    'serie_sub_name' => $serie_sub_name->car_serie_sub_name,
                    'years' => $lowCars[$i]->years,
                    'number' => $car,
                ]);
            }
        }
        return response()->json($new_array);
    }
}
