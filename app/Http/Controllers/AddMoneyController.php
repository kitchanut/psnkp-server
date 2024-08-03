<?php

namespace App\Http\Controllers;

use App\Models\Add_money;
use App\Models\Add_money_detail;
use App\Models\Branch;
use Illuminate\Http\Request;

class AddMoneyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output =  Add_money::with('user')->get();
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
        $list = $request->except(['branch_details', 'id']);
        $listCreate = Add_money::create($list);

        $listDetails = $request->branch_details;
        foreach ($listDetails as $key => $listDetail) {
            $listDetail['add_money_id'] = $listCreate->id;
            $Add_money_detail = Add_money_detail::create($listDetail);
            $Branch = Branch::find($Add_money_detail->branch_id);
            $sum = $Branch->branch_money + $Add_money_detail->branch_money;
            $Branch->branch_money = $sum;
            // $Branch->branch_money =  $Add_money_detail->branch_money;
            $Branch->save();
        }

        $list['add_money_id'] = $listCreate->id;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Add_money  $add_money
     * @return \Illuminate\Http\Response
     */
    public function show(Add_money $add_money)
    {
        $add_money->branch_details;
        return response()->json($add_money);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Add_money  $add_money
     * @return \Illuminate\Http\Response
     */
    public function edit(Add_money $add_money)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Add_money  $add_money
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Add_money $add_money)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Add_money  $add_money
     * @return \Illuminate\Http\Response
     */
    public function destroy(Add_money $add_money)
    {
        Add_money_detail::where('add_money_id', $add_money->id)->delete();
        $add_money->delete();
    }

    public function get_report_addmoney(Request $request)
    {
        $outputAdd_money = Add_money::where([['money_month', $request->money_month], ['money_year', $request->money_year]])
            ->first();
        $map = [];
        if (!empty($outputAdd_money)) {
            $outputAdd_money_detail = Add_money_detail::with('branch')
                ->where([['add_money_id', $outputAdd_money->id]])
                ->get();

            $map = $outputAdd_money_detail->map(function ($items) {
                $items['Add_money'] = Add_money::with('user')->find($items['add_money_id']);
                return $items;
            });
        }
        return response()->json($map);
    }
}
