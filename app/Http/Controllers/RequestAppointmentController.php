<?php

namespace App\Http\Controllers;

use App\Models\RequestAppointment;
use App\Models\RequestLog;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RequestAppointmentController extends Controller
{
    protected $pathCurrent = 'request_appointment/';

    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestAppointment::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'pedding')
            ->count();

        $approve = RequestAppointment::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'approve')
            ->count();
        $cancle = RequestAppointment::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'cancle')
            ->count();
        $all = RequestAppointment::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

        $output = RequestAppointment::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $credentials = (array) json_decode($data['formData']);

        $credentials['id_card'] = 'no_img.png';
        $created = RequestAppointment::create($credentials);

        $RequestAppointment = RequestAppointment::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestAppointment->id_card = $filename_id_card;
        }

        $RequestAppointment->save();

        if ($RequestAppointment['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestAppointment['id_card'];
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
        $log['type'] = 'นัดทำสัญญา';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestAppointment  $requestAppointment
     * @return \Illuminate\Http\Response
     */
    public function show(RequestAppointment $requestAppointment)
    {
        if ($requestAppointment['id_card'] != 'no_img.png') {
            $requestAppointment['id_card'] = '/' . $this->path . $this->pathCurrent . $requestAppointment['id_card'];
        } else {
            $requestAppointment['id_card'] = '/' . $this->path . 'no_img.png';
        }

        return response()->json($requestAppointment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestAppointment  $requestAppointment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestAppointment $requestAppointment)
    {
        $credentials = $request->all();
        if ($requestAppointment['request_status'] == 'pedding') {
            $requestAppointment['request_status'] = 'approve';
        } else if ($requestAppointment['request_status'] == 'approve') {
            $requestAppointment['request_status'] = 'pedding';
        } else if ($requestAppointment['request_status'] == 'cancle') {
            $requestAppointment['request_status'] = 'pedding';
        }
        $requestAppointment->save();

        $this->updateLog('นัดทำสัญญา', $requestAppointment->id, $requestAppointment->request_status);

        return response()->json($credentials);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RequestAppointment  $requestAppointment
     * @return \Illuminate\Http\Response
     */
    public function cancle($id)
    {

        $requestAppointment = RequestAppointment::find($id);
        $requestAppointment->request_status = 'cancle';
        $requestAppointment->save();

        $this->updateLog('นัดทำสัญญา', $requestAppointment->id, $requestAppointment->request_status);
    }

    public function destroy(RequestAppointment $requestAppointment)
    {
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestAppointment->id_card);

        $this->deleteLog('นัดทำสัญญา', $requestAppointment->id);

        return response()->json($requestAppointment->delete());
    }
}
