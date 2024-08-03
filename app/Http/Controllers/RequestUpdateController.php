<?php

namespace App\Http\Controllers;

use App\Models\RequestLog;
use App\Models\RequestUpdate;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RequestUpdateController extends Controller
{
    protected $pathCurrent = 'request_update/';

    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestUpdate::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'pedding')
            ->count();

        $approve = RequestUpdate::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'approve')
            ->count();
        $cancle = RequestUpdate::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'cancle')
            ->count();
        $all = RequestUpdate::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

        $output = RequestUpdate::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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
        $created = RequestUpdate::create($credentials);

        $RequestUpdate = RequestUpdate::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestUpdate->id_card = $filename_id_card;
        }

        $RequestUpdate->save();

        if ($RequestUpdate['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestUpdate['id_card'];
        } else {
            $created['id_card'] = '/' . $this->path . 'no_img.png';
        }

        // if (!$created['note']) {
        //     $created['note'] = "-";
        // }

        $log['working_id'] = $created['working_id'];
        $log['ref_id'] = $created['id'];
        $log['lineUUID'] = $created['lineUUID'];
        $log['displayName'] = $created['displayName'];
        $log['pictureUrl'] = $created['pictureUrl'];
        $log['sale_name'] = $created['sale_name'];
        $log['branch_name'] = $created['branch_name'];
        $log['car_no'] = $created['car_no'];
        $log['type'] = 'อัพเดทข้อมูล';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestUpdate  $requestUpdate
     * @return \Illuminate\Http\Response
     */
    public function show(RequestUpdate $requestUpdate)
    {
        if ($requestUpdate['id_card'] != 'no_img.png') {
            $requestUpdate['id_card'] = '/' . $this->path . $this->pathCurrent . $requestUpdate['id_card'];
        } else {
            $requestUpdate['id_card'] = '/' . $this->path . 'no_img.png';
        }
        return response()->json($requestUpdate);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestUpdate  $requestUpdate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestUpdate $requestUpdate)
    {
        if ($requestUpdate['request_status'] == 'pedding') {
            $requestUpdate['request_status'] = 'approve';
        } else if ($requestUpdate['request_status'] == 'approve') {
            $requestUpdate['request_status'] = 'pedding';
        } else if ($requestUpdate['request_status'] == 'cancle') {
            $requestUpdate['request_status'] = 'pedding';
        }

        $this->updateLog('อัพเดทข้อมูล', $requestUpdate->id, $requestUpdate->request_status);

        return response()->json($requestUpdate->save());
    }

    public function cancle($id)
    {
        $request = RequestUpdate::find($id);
        $request->request_status = 'cancle';
        $request->save();

        $this->updateLog('อัพเดทข้อมูล', $request->id, $request->request_status);
    }

    public function destroy(RequestUpdate $requestUpdate)
    {
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestUpdate->id_card);

        $this->deleteLog('อัพเดทข้อมูล', $requestUpdate->id);


        return response()->json($requestUpdate->delete());
    }
}
