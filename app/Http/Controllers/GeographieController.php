<?php

namespace App\Http\Controllers;

use App\Models\Geographie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeographieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Geographie::all());
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
        Geographie::create($credentials);
        // return response()->json($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Geographie  $geographie
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $geographie = DB::table('geographies')->where('id', $id)->first();
        return response()->json($geographie);
        // return response()->json($id);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Geographie  $geographie
     * @return \Illuminate\Http\Response
     */
    public function edit(Geographie $geographie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Geographie  $geographie
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Geographie $geographie)
    {
        $geographie = Geographie::find($request->id);
        $geographie->name = $request->name;
        $geographie->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Geographie  $geographie
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $geographie = Geographie::find($id);
        $geographie->delete();
    }


    public function selectOnGeographies()
    {
        return response()->json(Geographie::all());
    }
}
