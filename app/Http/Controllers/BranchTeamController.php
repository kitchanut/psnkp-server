<?php

namespace App\Http\Controllers;

use App\Models\Branch_team;
use Illuminate\Http\Request;

class BranchTeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = Branch_team::where([['del', 1]])->get();
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
        Branch_team::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Branch_team  $branch_team
     * @return \Illuminate\Http\Response
     */
    public function show(Branch_team $branch_team)
    {
        return response()->json($branch_team);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Branch_team  $branch_team
     * @return \Illuminate\Http\Response
     */
    public function edit(Branch_team $branch_team)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch_team  $branch_team
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Branch_team $branch_team)
    {
        $branch_team->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Branch_team  $branch_team
     * @return \Illuminate\Http\Response
     */
    public function destroy(Branch_team $branch_team, Request $request)
    {
        //
        // $branch_team->branch_team_active = 2;
        // $branch_team->save();
        $branch_team->branch_team_active = 0;
        $branch_team->del = 0;
        $branch_team->user_id = $request->user()->id;
        $branch_team->save();
    }

    public function SelectOnBranchTeams()
    {
        return response()->json(Branch_team::where([['branch_team_active', 1]])->get());
    }
}
