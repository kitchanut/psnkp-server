<?php

namespace App\Http\Controllers;

use App\Models\UserLine;
use Illuminate\Http\Request;

class UserLineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = UserLine::with('user.branch.branch_team')->get();
        return response()->json($output);
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
        UserLine::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserLine  $userLine
     * @return \Illuminate\Http\Response
     */
    public function show(UserLine $userLine)
    {
        return response()->json($userLine);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserLine  $userLine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserLine $userLine)
    {
        $userLine->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserLine  $userLine
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserLine $userLine)
    {
        $userLine->delete();
    }
}
