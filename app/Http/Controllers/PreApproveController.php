<?php

namespace App\Http\Controllers;

use App\Models\PreApprove;
use Illuminate\Http\Request;

class PreApproveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
        $credentials = $request->all();
        PreApprove::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PreApprove  $preApprove
     * @return \Illuminate\Http\Response
     */
    public function show(PreApprove $preApprove)
    {
        return response()->json($preApprove);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PreApprove  $preApprove
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PreApprove $preApprove)
    {
        $preApprove->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PreApprove  $preApprove
     * @return \Illuminate\Http\Response
     */
    public function destroy(PreApprove $preApprove)
    {
        $preApprove->delete();
    }

    public function checkPreApprove($working_id)
    {
        $preApprove = PreApprove::where('working_id', $working_id)->first();
        return response()->json($preApprove);
    }
}
