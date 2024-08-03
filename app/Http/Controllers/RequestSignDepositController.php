<?php

namespace App\Http\Controllers;

use App\Models\RequestLog;
use App\Models\RequestSignDeposit;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RequestSignDepositController extends Controller
{
    protected $pathCurrent = 'request_signDeposit/';

    public function index()
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
        $data = $request->all();
        $credentials = (array) json_decode($data['formData']);

        $credentials['id_card'] = 'no_img.png';
        $created = RequestSignDeposit::create($credentials);

        $RequestSignDeposit = RequestSignDeposit::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestSignDeposit->id_card = $filename_id_card;
        }

        $RequestSignDeposit->save();

        if ($RequestSignDeposit['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestSignDeposit['id_card'];
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
        $log['type'] = 'ฝากเซนต์';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestSignDeposit  $requestSignDeposit
     * @return \Illuminate\Http\Response
     */
    public function show(RequestSignDeposit $requestSignDeposit)
    {
        if ($requestSignDeposit['id_card'] != 'no_img.png') {
            $requestSignDeposit['id_card'] = '/' . $this->path . $this->pathCurrent . $requestSignDeposit['id_card'];
        } else {
            $requestSignDeposit['id_card'] = '/' . $this->path . 'no_img.png';
        }

        return response()->json($requestSignDeposit);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestSignDeposit  $requestSignDeposit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestSignDeposit $requestSignDeposit)
    {
        $credentials = $request->all();
        if ($requestSignDeposit['request_status'] == 'pedding') {
            $requestSignDeposit['request_status'] = 'approve';
        } else if ($requestSignDeposit['request_status'] == 'approve') {
            $requestSignDeposit['request_status'] = 'pedding';
        } else if ($requestSignDeposit['request_status'] == 'cancle') {
            $requestSignDeposit['request_status'] = 'pedding';
        }
        $requestSignDeposit->save();

        $this->updateLog('ฝากเซนต์', $requestSignDeposit->id, $requestSignDeposit->request_status);

        return response()->json($credentials);
    }
    public function cancle($id)
    {

        $requestAppointment = RequestSignDeposit::find($id);
        $requestAppointment->request_status = 'cancle';
        $requestAppointment->save();

        $this->updateLog('ฝากเซนต์', $requestAppointment->id, $requestAppointment->request_status);
    }

    public function destroy(RequestSignDeposit $requestSignDeposit)
    {
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestSignDeposit->id_card);

        $this->deleteLog('ฝากเซนต์', $requestSignDeposit->id);

        return response()->json($requestSignDeposit->delete());
    }
}
