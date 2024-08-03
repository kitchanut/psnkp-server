<?php

namespace App\Http\Controllers;

use App\Models\RequestLog;
use App\Models\User;
use Illuminate\Http\Request;

class RequestLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');
        $search = $request->input('search');

        if ($search) {
            $peddingObj = RequestLog::where('car_no', $search)
                ->orWhere('car_no_old', $search)
                ->get();
            $pedding = collect($peddingObj)->filter(function ($value, $key) {
                return $value['request_status'] == 'pedding';
            })->count();
            $approveObj = RequestLog::where('car_no', $search)
                ->orWhere('car_no_old', $search)
                ->get();
            $approve = collect($approveObj)->filter(function ($value, $key) {
                return $value['request_status'] == 'approve';
            })->count();

            $cancleObj = RequestLog::where('car_no', $search)
                ->orWhere('car_no_old', $search)
                ->get();
            $cancle = collect($cancleObj)->filter(function ($value, $key) {
                return $value['request_status'] == 'cancle';
            })->count();

            $all = RequestLog::where('car_no', $search)
                ->orWhere('car_no_old', $search)
                ->count();
        } else {
            $pedding = RequestLog::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
                ->where('request_status', 'pedding')
                ->count();

            $approve = RequestLog::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
                ->where('request_status', 'approve')
                ->count();
            $cancle = RequestLog::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
                ->where('request_status', 'cancle')
                ->count();
            $all = RequestLog::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
                ->count();
        }



        $output['pedding'] = $pedding;
        $output['approve'] = $approve;
        $output['cancle'] = $cancle;
        $output['all'] = $all;

        return response()->json($output);
    }

    public function indexCustom(Request $request)
    {

        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');
        $toggle = $request->input('toggle');
        if ($toggle == 'all') {
            $toggle = null;
        }
        $search = $request->input('search');

        if ($search) {
            $output = RequestLog::where('car_no', $search)
                ->orWhere('car_no_old', $search)
                ->orderBy('created_at', 'DESC')
                ->get();
        } else {
            $output = RequestLog::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
                ->when($toggle, function ($query) use ($toggle) {
                    $query->where('request_status', (string) $toggle);
                })
                ->orderBy('created_at', 'DESC')
                ->get();
        }
        $output->map(function ($item) {
            $item['user_image'] = '' . '/' . $this->path . 'users/'  . 'default/user_default.png';
            $user = User::find($item->user_id);
            if ($user) {
                if ($user['user_image'] != null) {
                    $item['user_image'] = '' . '/' . $this->path . 'users/' . $user['id'] . '/' . $user['user_image'];
                }
            } else {
                $nameArray = explode(" ", $item->sale_name);
                $userName = User::where('first_name', $nameArray[0])->first();
                if ($userName) {
                    if ($userName['user_image'] != null) {
                        $item['user_image'] = '' . '/' . $this->path . 'users/' . $userName['id'] . '/' . $userName['user_image'];
                    }
                }
            }
            return $item;
        });

        return response()->json($output);
    }

    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestLog  $requestLog
     * @return \Illuminate\Http\Response
     */
    public function show(RequestLog $requestLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestLog  $requestLog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestLog $requestLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RequestLog  $requestLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(RequestLog $requestLog)
    {
        //
    }
}
