<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Negotiation;
use Illuminate\Http\Request;

class NegotiationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $branch_id = auth()->user()->branch_id;
        $user_group_id = auth()->user()->user_group_id;
        $branch = Branch::find($branch_id);
        $branch_team_id = $branch->branch_team_id;
        $output = Negotiation::with(['working' => function ($query) {
            $query->select('id', 'car_id', 'customer_id');
            $query->with(['cars' => function ($query) {
                $query->select('id', 'car_no');
            }]);
            $query->with('customer');
        }])
            ->whereHas('working', function ($q) use ($branch_team_id, $user_group_id) {
                if ($user_group_id != 1) {
                    $q->where('branch_team_id', $branch_team_id);
                }
            })
            ->get();
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
        Negotiation::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Negotiation  $negotiation
     * @return \Illuminate\Http\Response
     */
    public function show(Negotiation $negotiation)
    {
        return response()->json($negotiation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Negotiation  $negotiation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Negotiation $negotiation)
    {
        $negotiation->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Negotiation  $negotiation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Negotiation $negotiation)
    {
        $negotiation->delete();
    }
}
