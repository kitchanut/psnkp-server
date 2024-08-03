<?php

namespace App\Http\Controllers;

use App\Models\Customer_visit;
use Illuminate\Http\Request;

class CustomerVisitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Customer_visit::with(['customer', 'car_types', 'car_models', 'car_series', 'car_serie_sub', 'user', 'branch'])->get());
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
        foreach ($request->car_details as $key => $car_detail) {
            // $credentials = $request->except(['id']);

            Customer_visit::create([
                'car_types_id' => $car_detail['car_types_id'],
                'car_models_id' => $car_detail['car_models_id'],
                'car_serie_id' => $car_detail['car_serie_id']['id'],
                'car_serie_sub_id' => $car_detail['car_serie_sub_id'],
                'car_year' => $car_detail['car_year'],
                'amount_down_id' => $car_detail['amount_down_id'],
                'amount_slacken_id' => $car_detail['amount_slacken_id'],
                'customer_bath_start' => $car_detail['customer_bath_start'],
                'customer_bath_end' => $car_detail['customer_bath_end'],
                'know_type' => $car_detail['know_type'],


                'customer_id' => $request->customer_id,
                'user_id' => $request->user_id,
                'branch_id' => $request->branch_id,
            ]);

            // Customer_visit::create($credentials);
        }
        // return response()->json($car_detail['car_serie_id']['id']);

        // return response()->json($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer_visit  $customer_visit
     * @return \Illuminate\Http\Response
     */
    public function show(Customer_visit $customer_visit)
    {
        return response()->json($customer_visit);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer_visit  $customer_visit
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer_visit $customer_visit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer_visit  $customer_visit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer_visit $customer_visit)
    {
        $customer_visit->update($request->except(['updated_at', 'branch_id', 'user_id']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer_visit  $customer_visit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer_visit $customer_visit)
    {
        $customer_visit->delete();
    }

    public function customerVisitWhere(Request $request)
    {
        // return response()->json($request);
        if ($request->branch_id == 0) {
            $data = Customer_visit::with(['customer', 'car_types', 'car_models', 'car_series', 'car_serie_sub', 'user', 'branch', 'amount_down', 'amount_slacken'])
                ->where([['customer_visits.created_at', '>', $request->timeStart], ['customer_visits.created_at', '<', $request->timeEnd]])
                ->get();
        } else {
            $data =   Customer_visit::with(['customer', 'car_types', 'car_models', 'car_series', 'car_serie_sub', 'user', 'branch', 'amount_down', 'amount_slacken'])
                ->where([['customer_visits.branch_id', '=', $request->branch_id], ['customer_visits.created_at', '>', $request->timeStart], ['customer_visits.created_at', '<', $request->timeEnd]])
                ->get();
        }
        return response()->json($data);
    }
}
