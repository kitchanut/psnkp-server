<?php

namespace App\Http\Controllers;

use App\Models\RequestChangeCar;
use App\Models\RequestLog;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RequestChangeCarController extends Controller
{
    protected $pathCurrent = 'request_changeCar/';

    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestChangeCar::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'pedding')
            ->count();

        $approve = RequestChangeCar::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'approve')
            ->count();
        $cancle = RequestChangeCar::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'cancle')
            ->count();
        $all = RequestChangeCar::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

        $output = RequestChangeCar::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

            if ($item['booking_sheet'] == 'no_img.png') {
                $item['booking_sheet'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['booking_sheet'] = '/' . $this->path . $this->pathCurrent . $item['booking_sheet'];
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
        $created = RequestChangeCar::create($credentials);

        $RequestChangeCar = RequestChangeCar::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestChangeCar->id_card = $filename_id_card;
        }

        $file_booking_sheet = $request->file('booking_sheet');
        if ($file_booking_sheet) {
            $filename_booking_sheet = $created->id . '_booking_sheet.' . $file_booking_sheet->getClientOriginalExtension();
            $saveImagePath_booking_sheet = $this->path . $this->pathCurrent;
            $file_booking_sheet->move($saveImagePath_booking_sheet, $filename_booking_sheet);
            $RequestChangeCar->booking_sheet = $filename_booking_sheet;
        }

        $RequestChangeCar->save();

        if ($RequestChangeCar['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestChangeCar['id_card'];
        } else {
            $created['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestChangeCar['booking_sheet'] != 'no_img.png') {
            $created['booking_sheet'] = '/' . $this->path . $this->pathCurrent . $RequestChangeCar['booking_sheet'];
        } else {
            $created['booking_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if (!$created['note']) {
            $created['note'] = "-";
        }

        $log['ref_id'] = $created['id'];
        $log['lineUUID'] = $created['lineUUID'];
        $log['displayName'] = $created['displayName'];
        $log['pictureUrl'] = $created['pictureUrl'];
        $log['sale_name'] = $created['sale_name'];
        $log['branch_name'] = $created['branch_name'];
        $log['car_no'] = $created['car_no_new'];
        $log['car_no_old'] = $created['car_no_old'];
        $log['type'] = 'เปลี่ยนจอง';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }

    public function show($id)
    {
        $requestChangeCar = RequestChangeCar::find($id);
        if ($requestChangeCar['id_card'] != 'no_img.png') {
            $requestChangeCar['id_card'] = '/' . $this->path . $this->pathCurrent . $requestChangeCar['id_card'];
        } else {
            $requestChangeCar['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestChangeCar['booking_sheet'] != 'no_img.png') {
            $requestChangeCar['booking_sheet'] = '/' . $this->path . $this->pathCurrent . $requestChangeCar['booking_sheet'];
        } else {
            $requestChangeCar['booking_sheet'] = '/' . $this->path . 'no_img.png';
        }

        return response()->json($requestChangeCar);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestChangeCar  $requestChangeCar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $requestChangeCar = RequestChangeCar::find($id);
        if ($requestChangeCar['request_status'] == 'pedding') {
            $requestChangeCar['request_status'] = 'approve';
        } else if ($requestChangeCar['request_status'] == 'approve') {
            $requestChangeCar['request_status'] = 'pedding';
        } else if ($requestChangeCar['request_status'] == 'cancle') {
            $requestChangeCar['request_status'] = 'pedding';
        }

        $this->updateLog('เปลี่ยนจอง', $requestChangeCar->id, $requestChangeCar->request_status);

        return response()->json($requestChangeCar->save());
    }

    public function cancle($id)
    {
        $request = RequestChangeCar::find($id);
        $request->request_status = 'cancle';
        $request->save();

        $this->updateLog('เปลี่ยนจอง', $request->id, $request->request_status);
    }

    public function destroy($id)
    {
        $requestChangeCar = RequestChangeCar::find($id);
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestChangeCar->id_card);
        File::delete($destinationPath . $requestChangeCar->booking_sheet);

        $this->deleteLog('เปลี่ยนจอง', $requestChangeCar->id);

        return response()->json($requestChangeCar->delete());
    }
}
