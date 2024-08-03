<?php

namespace App\Http\Controllers;

use App\Models\Transition_working;
use App\Models\Working;
use Illuminate\Http\Request;

class TransitionWorkingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $branch_id = $request->input('branch_id');

        $output = Transition_working::with(['car', 'customer', 'user', 'branch'])
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->where([['created_at', '>', $request->timeStart], ['created_at', '<', $request->timeEnd]])
            ->get();

        return response()->json($output);
    }

    public function working_where_id($id)
    {
        return response()->json(Transition_working::with(['car', 'customer', 'user', 'branch'])->where('transition_workings.working_id', $id)->get());
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
     * @param  \App\Models\Transition_working  $transition_working
     * @return \Illuminate\Http\Response
     */
    public function show(Transition_working $transition_working)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transition_working  $transition_working
     * @return \Illuminate\Http\Response
     */
    public function edit(Transition_working $transition_working)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transition_working  $transition_working
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transition_working $transition_working)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transition_working  $transition_working
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transition_working $transition_working)
    {
        //
    }
}
