<?php

namespace App\Http\Controllers;

use App\Models\Amount_slacken;
use Illuminate\Http\Request;

class AmountSlackenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Amount_slacken::all());
        $output = Amount_slacken::where('del', 1)->get();
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
        Amount_slacken::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Amount_slacken  $amount_slacken
     * @return \Illuminate\Http\Response
     */
    public function show(Amount_slacken $amount_slacken)
    {
        return response()->json($amount_slacken);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Amount_slacken  $amount_slacken
     * @return \Illuminate\Http\Response
     */
    public function edit(Amount_slacken $amount_slacken)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Amount_slacken  $amount_slacken
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Amount_slacken $amount_slacken)
    {
        $amount_slacken->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Amount_slacken  $amount_slacken
     * @return \Illuminate\Http\Response
     */
    public function destroy(Amount_slacken $amount_slacken, Request $request)
    {
        // $amount_slacken->delete();
        $amount_slacken->amount_slacken_active = 0;
        $amount_slacken->del = 0;
        $amount_slacken->user_id = $request->user()->id;
        $amount_slacken->save();
    }

    public function selectOnAmountSlacken()
    {
        // return response()->json(Amount_slacken::where([['amount_slacken_active', 1]])->get());

        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            $allow_query = Amount_slacken::where([['amount_slacken_active', 1]])->get();
            return response()->json($allow_query);
        } else {
            if ($host == $allow_host) {
                $allow_query = Amount_slacken::where([['amount_slacken_active', 1]])->get();
                return response()->json($allow_query);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }
}
