<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Car;
use App\Models\Middle_price;
use App\Models\Middle_price_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MiddlePriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = Middle_price::with(['car_serie', 'car_serie_sub'])
            ->orderBy('car_serie_id')
            ->orderBy('car_serie_sub_id')
            ->orderBy('year', 'desc')
            ->get();
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
        // $highMoney = 0;
        // return response()->json($request);

        for ($i = (int)$request->years_start; $i <= (int)$request->years_end; $i++) {
            $create = Middle_price::create([
                'car_serie_id' => $request->car_serie_id,
                'car_serie_sub_id' => $request->car_serie_sub_id,
                'car_gear' => $request->car_gear,
                'selected' => $request->selected,
                'year' => $i,
                'middle_price_active' => $request->middle_price_active
            ]);


            for ($index = 0; $index < count($request->middle_price_details); $index++) {
                $Middle_price_detail =   Middle_price_detail::create([
                    'middle_price_id' => $create->id,
                    'bank_id' => $request->middle_price_details[$index]['id'],
                    'middle_plus' => $request->middle_price_details[$index]['middle_plus'],
                    'middle_multiply' => $request->middle_price_details[$index]['middle_multiply'],
                    'middle_price' => $request->middle_price_details[$index]['middle_price'],
                    'amount_price' => $request->middle_price_details[$index]['amount_price'],
                ]);
                if ($Middle_price_detail->bank_id == $create->selected) {
                    $car = Car::where([
                        ['car_serie_id', $request->car_serie_id],
                        ['car_serie_sub_id', $request->car_serie_sub_id],
                        ['car_gear', $request->car_gear],
                        ['car_year', $i],
                        ['car_stock', '!=', 3]
                    ])->get();
                    // $plus = ((float)$Middle_price_detail->middle_price + ((float)$Middle_price_detail->middle_plus / 100) * (float)$Middle_price_detail->middle_price);
                    // $new_amount_price = (float) $Middle_price_detail->middle_price;
                    // $new_price = ((float)$Middle_price_detail->middle_multiply / 100) * (float)$plus;

                    for ($j = 0; $j < count($car); $j++) {
                        $update = Car::find($car[$j]->id);
                        $update->amount_price = $Middle_price_detail->amount_price;
                        $update->car_price_vat = (float)$Middle_price_detail->amount_price + (((float)$car[$j]->amount_down + 20000));
                        $update->car_price_plus = $Middle_price_detail->middle_plus;
                        $update->car_price_multiply = $Middle_price_detail->middle_multiply;
                        $update->save();
                    }
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Middle_price  $middle_price
     * @return \Illuminate\Http\Response
     */
    public function show(Middle_price $middle_price)
    {
        // $middle_price->middle_price_details;
        $getDetail =  Middle_price_detail::where('middle_price_id', $middle_price->id)->get();
        $getBank =  Bank::all();
        $checkBank = [];
        for ($i = 0; $i < count($getBank); $i++) {
            if (!isset($getDetail[$i])) {
                array_push($checkBank, array(
                    'id' => $getBank[$i]['id'],
                    'bank_nick_name' => $getBank[$i]['bank_nick_name'],
                    'bank_id' => $getBank[$i]['id'],
                    'middle_plus' => 0,
                    'middle_multiply' => 0,
                    'middle_price' => 0,
                    'amount_price' => 0

                ));
                if ($getBank[$i]['id'] == $middle_price->selected) {
                    // $middle_price->selected = $i;
                    $middle_price->selected = $getBank[$i]['id'];
                }
            } else {
                array_push($checkBank, array(
                    'id' => $getDetail[$i]['bank_id'],
                    'bank_nick_name' => $getBank[$i]['bank_nick_name'],
                    'bank_id' => $getDetail[$i]['bank_id'],
                    'middle_plus' => $getDetail[$i]['middle_plus'],
                    'middle_multiply' => $getDetail[$i]['middle_multiply'],
                    'middle_price' => $getDetail[$i]['middle_price'],
                    'amount_price' => $getDetail[$i]['amount_price']
                ));
                if ($getBank[$i]['id'] == $middle_price->selected) {
                    // $middle_price->selected = $i;
                    $middle_price->selected = $getBank[$i]['id'];
                }
            }
        }
        $middle_price->middle_price_details = $checkBank;
        return response()->json($middle_price);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Middle_price  $middle_price
     * @return \Illuminate\Http\Response
     */
    public function edit(Middle_price $middle_price)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Middle_price  $middle_price
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Middle_price $middle_price)
    {

        $middle_price->update($request->except(['updated_at', 'middle_price_details']));
        $amount_price = 0;
        $middle_plus = 0;
        $middle_multiply = 0;

        DB::table('middle_price_details')->where('middle_price_id', $request->id)->delete();
        for ($index = 0; $index < count($request->middle_price_details); $index++) {
            Middle_price_detail::create([
                'middle_price_id' => $request->id,
                'bank_id' => $request->middle_price_details[$index]['bank_id'],
                'middle_plus' => $request->middle_price_details[$index]['middle_plus'],
                'middle_multiply' => $request->middle_price_details[$index]['middle_multiply'],
                'middle_price' => $request->middle_price_details[$index]['middle_price'],
                'amount_price' => $request->middle_price_details[$index]['amount_price'],
            ]);

            if ($request->middle_price_details[$index]['bank_id'] == $middle_price->selected) {
                // $update =   Middle_price::find($middle_price->id);
                // $update->selected = $request->middle_price_details[$index]['bank_id'];
                // $update->save();
                $amount_price =  $request->middle_price_details[$index]['amount_price'];
                $middle_plus = $request->middle_price_details[$index]['middle_plus'];
                $middle_multiply = $request->middle_price_details[$index]['middle_multiply'];
            }
        }

        $car = Car::where([
            ['car_serie_id', $request->car_serie_id],
            ['car_serie_sub_id', $request->car_serie_sub_id],
            ['car_year', $middle_price->year],
            ['car_gear', $request->car_gear],
            ['car_stock', '!=', 3],
        ])->get();

        for ($index = 0; $index < count($car); $index++) {
            $update = Car::find($car[$index]->id);
            $update->car_price_plus = $middle_plus;
            $update->car_price_multiply = $middle_multiply;
            $update->car_price_vat = (float)$amount_price + (float)$car[$index]->amount_down + 20000;
            $update->amount_price = $amount_price;
            $update->save();
        }
        // return response()->json($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Middle_price  $middle_price
     * @return \Illuminate\Http\Response
     */
    public function destroy(Middle_price $middle_price)
    {
        $middle_price->delete();
    }
}
