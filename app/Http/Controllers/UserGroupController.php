<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_group;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = User_group::where([['del', 1]])->get();
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
        // $credentials['id'] = $credentials['id'] = null;
        User_group::create($credentials);
        // return response()->json(User_group::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User_group  $user_group
     * @return \Illuminate\Http\Response
     */
    public function show(User_group $user_group)
    {
        return response()->json($user_group);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User_group  $user_group
     * @return \Illuminate\Http\Response
     */
    public function edit(User_group $user_group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User_group  $user_group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User_group $user_group)
    {
        $user_group->update($request->except(['updated_at']));

        // return response()->json([
        //     'status' => true,
        // ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User_group  $user_group
     * @return \Illuminate\Http\Response
     */
    public function destroy(User_group $user_group, Request $request)
    {
        // $user_group->delete();
        // $check_User = User::where([['user_group_id', $user_group->id]])->get();
        // if (count($check_User) == 0) {
        //     $user_group->delete();
        // }
        // return response()->json($user_group->delete());

        $user_group->user_group_active = 0;
        $user_group->del = 0;
        $user_group->user_id = $request->user()->id;
        $user_group->save();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function SelectOnUserGroups()
    {
        return response()->json(User_group::where([['user_group_active', 1]])->get());
    }
}
