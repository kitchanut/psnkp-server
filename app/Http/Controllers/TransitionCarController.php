<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Image_car;
use App\Models\Transition_car;
use Illuminate\Http\Request;

class TransitionCarController extends Controller
{
    private $pathCurrent = 'cars/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $branch_id = $request->input('branch_id');
        $car = Transition_car::with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'branch', 'color'])
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->where([['created_at', '>', $request->timeStart], ['created_at', '<', $request->timeEnd]])
            ->get();
        $map = $car->map(function ($items) {
            if ($items['img_id_first'] != 0) {
                $Image_car =  Image_car::find($items['img_id_first']);
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent . $items['car_id'] . '/' . $Image_car->image_name;
            }
            return $items;
        });


        return response()->json($map);

        // return response()->json($car);
    }

    public function car_regis($branch_id)
    {

        // if ($branch_id == 0) {
        //     $car = Car::orderBy('created_at', 'DESC')->get();
        // } else {
        //     $car = Car::where('branch_id', $branch_id)->orderBy('created_at', 'DESC')->get();
        // }
        $car = Car::when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->orderBy('created_at', 'DESC')->get();

        return response()->json($car);
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
     * @param  \App\Models\transition_car  $transition_car
     * @return \Illuminate\Http\Response
     */
    public function show(transition_car $transition_car)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\transition_car  $transition_car
     * @return \Illuminate\Http\Response
     */
    public function edit(transition_car $transition_car)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\transition_car  $transition_car
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, transition_car $transition_car)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\transition_car  $transition_car
     * @return \Illuminate\Http\Response
     */
    public function destroy(transition_car $transition_car)
    {
        //
    }

    public function where_car($idCar)
    {
        // return response()->json($request);
        $car = Transition_car::with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'branch', 'color'])->where('transition_cars.car_id', $idCar)->get();
        $map = $car->map(function ($items) {
            if ($items['img_id_first'] != 0) {
                $Image_car =  Image_car::find($items['img_id_first']);
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent . $items['car_id'] . '/' . $Image_car->image_name;
            }
            return $items;
        });
        return response()->json($map);
    }
}
