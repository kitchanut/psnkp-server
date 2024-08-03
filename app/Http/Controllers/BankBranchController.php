<?php

namespace App\Http\Controllers;

use App\Models\Bank_branch;
use Illuminate\Http\Request;

class BankBranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = Bank_branch::with('bank')->where('del', 1)->get();
        return response()->json($output);
        // return response()->json(Bank_branch::with('bank')->get());
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
        Bank_branch::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bank_branch  $bank_branch
     * @return \Illuminate\Http\Response
     */
    public function show(Bank_branch $bank_branch)
    {
        return response()->json($bank_branch);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bank_branch  $bank_branch
     * @return \Illuminate\Http\Response
     */
    public function edit(Bank_branch $bank_branch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bank_branch  $bank_branch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bank_branch $bank_branch)
    {
        $bank_branch->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bank_branch  $bank_branch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bank_branch $bank_branch, Request $request)
    {
        // $bank_branch->delete();
        // return response()->json($bank_branch);
        $bank_branch->bank_branch_active = 0;
        $bank_branch->del = 0;
        $bank_branch->user_id = $request->user()->id;
        $bank_branch->save();
    }

    public function SelectOnBank_branch()
    {
        return response()->json(Bank_branch::where([['bank_branch_active', 1]])->get());
    }
}
