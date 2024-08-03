<?php

namespace App\Http\Controllers;

use App\Models\Search_term;
use Illuminate\Http\Request;

class SearchTermController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Search_term::all());
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
        Search_term::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Search_term  $search_term
     * @return \Illuminate\Http\Response
     */
    public function show(Search_term $search_term)
    {
        return response()->json($search_term);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Search_term  $search_term
     * @return \Illuminate\Http\Response
     */
    public function edit(Search_term $search_term)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Search_term  $search_term
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Search_term $search_term)
    {
        $search_term->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Search_term  $search_term
     * @return \Illuminate\Http\Response
     */
    public function destroy(Search_term $search_term)
    {
        $search_term->delete();
    }


    public function SelectOnSearch_term()
    {
        $output = Search_term::where([['search_active', 1]])->get();
        return response()->json($output);
    }
}
