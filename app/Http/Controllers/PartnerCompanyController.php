<?php

namespace App\Http\Controllers;

use App\Models\Partner_company;
use Illuminate\Http\Request;

class PartnerCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = Partner_company::with(['province'])->where([['del', 1]])->get();
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
        $credentials = $request->except(['id', 'zip_code']);
        Partner_company::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Partner_company  $partner_company
     * @return \Illuminate\Http\Response
     */
    public function show(Partner_company $partner_company)
    {

        return response()->json($partner_company);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Partner_company  $partner_company
     * @return \Illuminate\Http\Response
     */
    public function edit(Partner_company $partner_company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Partner_company  $partner_company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Partner_company $partner_company)
    {
        $partner_company->update($request->except(['updated_at', 'zip_code']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Partner_company  $partner_company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Partner_company $partner_company, Request $request,)
    {
        // $partner_company->delete();
        $partner_company->partner_companie_active = 0;
        $partner_company->del = 0;
        $partner_company->user_id = $request->user()->id;
        $partner_company->save();
    }

    public function selectOnPartnerCompany()
    {
        return response()->json(Partner_company::where('partner_companie_active', 1)->get());
    }
}
