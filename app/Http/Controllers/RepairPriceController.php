<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Repair_price;

use Illuminate\Http\Request;

class RepairPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Repair_price::all());
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
     * @param  \App\Models\Repair_price  $repair_price
     * @return \Illuminate\Http\Response
     */
    public function show(Repair_price $repair_price)
    {
        return response()->json($repair_price);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Repair_price  $repair_price
     * @return \Illuminate\Http\Response
     */
    public function edit(Repair_price $repair_price)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Repair_price  $repair_price
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Repair_price $repair_price)
    {
        $repair_price->update($request->except(['updated_at']));

        if ($repair_price->price != null || $repair_price->price  > 0) {
            $car = Car::find($repair_price->car_id);
            $sum_net_price = (float)$car->net_price + (float) $repair_price->price;
            $sum_income = (float)$car->income + (float) $repair_price->price;

            $car->net_price = $sum_net_price;
            $car->income = $sum_income;

            $car->save();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Repair_price  $repair_price
     * @return \Illuminate\Http\Response
     */
    public function destroy(Repair_price $repair_price)
    {
        //
    }

    public function RepairPrice($idCar)
    {
        $data = Repair_price::with(['repair'])
            ->where([['repair_prices.car_id', '=', $idCar]])
            ->get();
        return response()->json($data);
    }
}
