<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Customer_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Customer_detail::with(['customer'])->get());

        // return response()->json($request);
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
        $credentials = $request->except(['customer_name', 'customer_nickname', 'customer_tel', 'customer_birthday_year', 'customer_job', 'customer_job_list', 'customer_tel']);
        Customer_detail::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer_detail  $customer_detail
     * @return \Illuminate\Http\Response
     */
    public function show(Customer_detail $customer_detail)
    {

        // $query = DB::table('customer_details')
        //     ->join('customers', 'customer_details.customer_id', '=', 'customers.id')
        //     ->where('customers.id', $customer_detail->id)
        //     ->first();
        $customer_detail->customer();

        return response()->json($customer_detail);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer_detail  $customer_detail
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer_detail $customer_detail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer_detail  $customer_detail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer_detail $customer_detail)
    {
        $customer_detail->update($request->except(['customer_id', 'customer_name', 'customer_nickname', 'customer_tel', 'customer_birthday_year', 'customer_job', 'customer_job_list', 'updated_at']));
        $updateCustomer = Customer::find($request->customer_id);
        $updateCustomer->customer_tel = $request->customer_tel;
        $updateCustomer->customer_birthday_year = $request->customer_birthday_year;
        $updateCustomer->customer_job = $request->customer_job;
        $updateCustomer->save();
        // return response()->json($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer_detail  $customer_detail
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer_detail $customer_detail)
    {
        $customer_detail->delete();
    }


    public function SelectCustomerDetail($id)
    {
        $query = DB::table('customer_details')
            ->join('customers', 'customer_details.customer_id', '=', 'customers.id')
            ->where('customers.id', $id)
            ->first();
        return response()->json($query);
    }
}
