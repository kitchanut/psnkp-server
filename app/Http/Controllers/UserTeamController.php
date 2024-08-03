<?php

namespace App\Http\Controllers;

use App\Models\User_team;
use Illuminate\Http\Request;

class UserTeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = User_team::with(['branch'])->where([['del', 1]])->get();
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
        User_team::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User_team  $user_team
     * @return \Illuminate\Http\Response
     */
    public function show(User_team $user_team)
    {
        return response()->json($user_team);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User_team  $user_team
     * @return \Illuminate\Http\Response
     */
    public function edit(User_team $user_team)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User_team  $user_team
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User_team $user_team)
    {
        $user_team->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User_team  $user_team
     * @return \Illuminate\Http\Response
     */
    public function destroy(User_team $user_team, Request $request)
    {
        // $user_team->team_active = 2;
        // $user_team->save();

        $user_team->team_active = 0;
        $user_team->del = 0;
        $user_team->user_id = $request->user()->id;
        $user_team->save();
    }

    public function SelectOnUserTeams()
    {
        return response()->json(User_team::with(['branch'])->where([['team_active', 1]])->get());
    }
}
