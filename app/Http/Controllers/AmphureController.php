<?php

namespace App\Http\Controllers;

use App\Models\Amphure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AmphureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Amphure::with(['province'])->get());
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
        // $credentials['id'] = $credentials['id'] = null;
        Amphure::create($credentials);
        // return response()->json($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Amphure  $amphure
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $amphure = DB::table('amphures')->where('id', $id)->first();
        return response()->json($amphure);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Amphure  $amphure
     * @return \Illuminate\Http\Response
     */
    public function edit(Amphure $amphure)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Amphure  $amphure
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Amphure $amphure)
    {
        $amphure = Amphure::find($request->id);
        $amphure->name_th = $request->name_th;
        $amphure->code = $request->code;
        $amphure->province_id = $request->province_id;
        $amphure->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Amphure  $amphure
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $amphure = Amphure::find($id);
        $amphure->delete();
    }

    public function selectOnAmphures()
    {
        return response()->json(Amphure::all());
    }
}
