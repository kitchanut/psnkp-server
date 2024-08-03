<?php

namespace App\Http\Controllers;

use App\Models\Transition_jobtechnician;
use Illuminate\Http\Request;

class TransitionJobtechnicianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return response()->json(Transition_jobtechnician::with(['cars', 'car_lift', 'user', 'repair'])->get());
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
     * @param  \App\Models\Transition_jobtechnician  $transition_jobtechnician
     * @return \Illuminate\Http\Response
     */
    public function show(Transition_jobtechnician $transition_jobtechnician)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transition_jobtechnician  $transition_jobtechnician
     * @return \Illuminate\Http\Response
     */
    public function edit(Transition_jobtechnician $transition_jobtechnician)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transition_jobtechnician  $transition_jobtechnician
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transition_jobtechnician $transition_jobtechnician)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transition_jobtechnician  $transition_jobtechnician
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transition_jobtechnician $transition_jobtechnician)
    {
        //
    }


    public function where_job($idJob)
    {
        // return response()->json($request);
        $data = Transition_jobtechnician::with(['cars', 'car_lift', 'user', 'repair'])->where('transition_jobtechnicians.job_technician_id', $idJob)->get();
        return response()->json($data);
    }
}
