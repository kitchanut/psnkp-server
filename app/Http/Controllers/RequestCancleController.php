<?php

namespace App\Http\Controllers;

use App\Models\RequestCancle;
use App\Models\RequestLog;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RequestCancleController extends Controller
{
    protected $pathCurrent = 'request_cancle/';

    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestCancle::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'pedding')
            ->count();

        $approve = RequestCancle::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'approve')
            ->count();
        $cancle = RequestCancle::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'cancle')
            ->count();
        $all = RequestCancle::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->count();

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

        $output = RequestCancle::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->when($toggle, function ($query) use ($toggle) {
                $query->where('request_status', (string) $toggle);
            })
            ->orderBy('created_at', 'DESC')
            ->get();
        $map = $output->map(function ($item) {
            if ($item['id_card'] == 'no_img.png') {
                $item['id_card'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['id_card'] = '/' . $this->path . $this->pathCurrent . $item['id_card'];
            }

            return $item;
        });
        return response()->json($map);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $credentials = (array) json_decode($data['formData']);

        $credentials['id_card'] = 'no_img.png';
        $created = RequestCancle::create($credentials);

        $RequestCancle = RequestCancle::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestCancle->id_card = $filename_id_card;
        }

        $RequestCancle->save();

        if ($RequestCancle['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestCancle['id_card'];
        } else {
            $created['id_card'] = '/' . $this->path . 'no_img.png';
        }

        // if (!$created['note']) {
        //     $created['note'] = "-";
        // }

        $log['ref_id'] = $created['id'];
        $log['lineUUID'] = $created['lineUUID'];
        $log['displayName'] = $created['displayName'];
        $log['pictureUrl'] = $created['pictureUrl'];
        $log['sale_name'] = $created['sale_name'];
        $log['branch_name'] = $created['branch_name'];
        $log['car_no'] = $created['car_no'];
        $log['type'] = 'ยกเลิกจอง';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestCancle  $requestCancle
     * @return \Illuminate\Http\Response
     */
    public function show(RequestCancle $requestCancle)
    {
        if ($requestCancle['id_card'] != 'no_img.png') {
            $requestCancle['id_card'] = '/' . $this->path . $this->pathCurrent . $requestCancle['id_card'];
        } else {
            $requestCancle['id_card'] = '/' . $this->path . 'no_img.png';
        }
        return response()->json($requestCancle);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestCancle  $requestCancle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestCancle $requestCancle)
    {
        if ($requestCancle['request_status'] == 'pedding') {
            $requestCancle['request_status'] = 'approve';
        } else if ($requestCancle['request_status'] == 'approve') {
            $requestCancle['request_status'] = 'pedding';
        } else if ($requestCancle['request_status'] == 'cancle') {
            $requestCancle['request_status'] = 'pedding';
        }

        $this->updateLog('ยกเลิกจอง', $requestCancle->id, $requestCancle->request_status);

        return response()->json($requestCancle->save());
    }

    public function cancle($id)
    {
        $request = RequestCancle::find($id);
        $request->request_status = 'cancle';
        $request->save();

        $this->updateLog('ยกเลิกจอง', $request->id, $request->request_status);
    }

    public function destroy(RequestCancle $requestCancle)
    {
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestCancle->id_card);

        $this->deleteLog('ยกเลิกจอง', $requestCancle->id);

        return response()->json($requestCancle->delete());
    }
}
