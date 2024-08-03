<?php

namespace App\Http\Controllers;

use App\Models\Partner_technician;
use Illuminate\Http\Request;

class PartnerTechnicianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Partner_technician::with('branch')->get());
        $output = Partner_technician::with(['branch'])->where([['del', 1]])
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
        $credentials = $request->except(['id']);
        Partner_technician::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Partner_technician  $partner_technician
     * @return \Illuminate\Http\Response
     */
    public function show(Partner_technician $partner_technician)
    {
        return response()->json($partner_technician);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Partner_technician  $partner_technician
     * @return \Illuminate\Http\Response
     */
    public function edit(Partner_technician $partner_technician)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Partner_technician  $partner_technician
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Partner_technician $partner_technician)
    {
        $partner_technician->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Partner_technician  $partner_technician
     * @return \Illuminate\Http\Response
     */
    public function destroy(Partner_technician $partner_technician, Request $request)
    {
        // $partner_technician->delete();
        $partner_technician->partner_technician_active = 0;
        $partner_technician->del = 0;
        $partner_technician->user_id = $request->user()->id;
        $partner_technician->save();
    }

    public function SelectOnPartnerTech()
    {
        return response()->json(Partner_technician::where([['partner_technician_active', 1]])->get());
    }
}
