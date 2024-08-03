<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = Installment::with('user')->get();
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
        $credentials['user_id'] = auth()->user()->id;
        Installment::create($credentials);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Installment  $installment
     * @return \Illuminate\Http\Response
     */
    public function show(Installment $installment)
    {
        return response()->json($installment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Installment  $installment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Installment $installment)
    {
        $credentials = $request->all();
        $credentials['user_id'] = auth()->user()->id;
        $installment->update($credentials);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Installment  $installment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Installment $installment)
    {
        $installment->delete();
    }

    public function getByWorkingID($working_id)
    {
        $output = Installment::where('working_id', $working_id)->first();

        // $output['installment_payments']->map(function ($item) {
        //     if ($item['installment_img'] != null) {
        //         $item['installment_img'] = '' . '/' . $this->path . 'installment/'  . $item['installment_img'];
        //     }
        //     return $item;
        // });

        return response()->json($output);
    }

    public function getByUser($user_group_permission, $user_id, $branch_id)
    {
        if ($user_group_permission == -1) {
            $output = Installment::with('working.cars', 'working.sale', 'working.branch')->get();
        } elseif ($user_group_permission == 2) {
            $output = Installment::with('working.cars', 'working.sale', 'working.branch')
                ->whereHas('working', function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                })
                ->get();
        } else {
            $output = Installment::with('working.cars', 'working.sale', 'working.branch')
                ->whereHas('working', function ($query) use ($user_id) {
                    return $query->where('sale_id', $user_id);
                })
                ->get();
        }

        return response()->json($output);
    }
}
