<?php

namespace App\Http\Controllers;

use App\Models\Amount_down;
use Illuminate\Http\Request;

class AmountDownController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = Amount_down::where('del', 1)->get();
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
        Amount_down::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Amount_down  $amount_down
     * @return \Illuminate\Http\Response
     */
    public function show(Amount_down $amount_down)
    {
        return response()->json($amount_down);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Amount_down  $amount_down
     * @return \Illuminate\Http\Response
     */
    public function edit(Amount_down $amount_down)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Amount_down  $amount_down
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Amount_down $amount_down)
    {
        $amount_down->update($request->except(['updated_at']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Amount_down  $amount_down
     * @return \Illuminate\Http\Response
     */
    public function destroy(Amount_down $amount_down, Request $request)
    {
        // $amount_down->delete();
        $amount_down->amount_down_active = 0;
        $amount_down->del = 0;
        $amount_down->user_id = $request->user()->id;
        $amount_down->save();
    }


    public function selectOnAmountDown()
    {
        // return response()->json(Amount_down::where([['amount_down_active', 1]])->get());


        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            $allow_query = Amount_down::where([['amount_down_active', 1]])->get();
            return response()->json($allow_query);
        } else {
            if ($host == $allow_host) {
                $allow_query = Amount_down::where([['amount_down_active', 1]])->get();
                return response()->json($allow_query);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }
}
