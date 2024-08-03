<?php

namespace App\Http\Controllers;

use App\Models\RequestLog;
use App\Models\RequestSign;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RequestSignController extends Controller
{
    protected $pathCurrent = 'request_sign/';

    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestSign::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'pedding')
            ->count();

        $approve = RequestSign::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'approve')
            ->count();
        $cancle = RequestSign::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'cancle')
            ->count();
        $all = RequestSign::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

        $output = RequestSign::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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
            if ($item['sign_sheet'] == 'no_img.png') {
                $item['sign_sheet'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['sign_sheet'] = '/' . $this->path . $this->pathCurrent . $item['sign_sheet'];
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
        $credentials['sign_sheet'] = 'no_img.png';
        $credentials['booking_sheet'] = 'no_img.png';
        $created = RequestSign::create($credentials);

        $RequestSign = RequestSign::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestSign->id_card = $filename_id_card;
        }

        $file_sign_sheet = $request->file('sign_sheet');
        if ($file_sign_sheet) {
            $filename_sign_sheet = $created->id . '_sign_sheet.' . $file_sign_sheet->getClientOriginalExtension();
            $saveImagePath_sign_sheet = $this->path . $this->pathCurrent;
            $file_sign_sheet->move($saveImagePath_sign_sheet, $filename_sign_sheet);
            $RequestSign->sign_sheet = $filename_sign_sheet;
        }

        $file_booking_sheet = $request->file('booking_sheet');
        if ($file_booking_sheet) {
            $filename_booking_sheet = $created->id . '_booking_sheet.' . $file_booking_sheet->getClientOriginalExtension();
            $saveImagePath_booking_sheet = $this->path . $this->pathCurrent;
            $file_booking_sheet->move($saveImagePath_booking_sheet, $filename_booking_sheet);
            $RequestSign->booking_sheet = $filename_booking_sheet;
        }

        $RequestSign->save();

        if ($RequestSign['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestSign['id_card'];
        } else {
            $created['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestSign['sign_sheet'] != 'no_img.png') {
            $created['sign_sheet'] = '/' . $this->path . $this->pathCurrent . $RequestSign['sign_sheet'];
        } else {
            $created['sign_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestSign['booking_sheet'] != 'no_img.png') {
            $created['booking_sheet'] = '/' . $this->path . $this->pathCurrent . $RequestSign['booking_sheet'];
        } else {
            $created['booking_sheet'] = '/' . $this->path . 'no_img.png';
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
        $log['type'] = 'การทำสัญญา';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestSign  $requestSign
     * @return \Illuminate\Http\Response
     */
    public function show(RequestSign $requestSign)
    {
        if ($requestSign['id_card'] != 'no_img.png') {
            $requestSign['id_card'] = '/' . $this->path . $this->pathCurrent . $requestSign['id_card'];
        } else {
            $requestSign['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestSign['sign_sheet'] != 'no_img.png') {
            $requestSign['sign_sheet'] = '/' . $this->path . $this->pathCurrent . $requestSign['sign_sheet'];
        } else {
            $requestSign['sign_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestSign['booking_sheet'] != 'no_img.png') {
            $requestSign['booking_sheet'] = '/' . $this->path . $this->pathCurrent . $requestSign['booking_sheet'];
        } else {
            $requestSign['booking_sheet'] = '/' . $this->path . 'no_img.png';
        }

        return response()->json($requestSign);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestSign  $requestSign
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestSign $requestSign)
    {
        $credentials = $request->all();
        if ($requestSign['request_status'] == 'pedding') {
            $requestSign['request_status'] = 'approve';
        } else if ($requestSign['request_status'] == 'approve') {
            $requestSign['request_status'] = 'pedding';
        } else if ($requestSign['request_status'] == 'cancle') {
            $requestSign['request_status'] = 'pedding';
        }
        $requestSign->save();

        $this->updateLog('การทำสัญญา', $requestSign->id, $requestSign->request_status);

        return response()->json($credentials);
    }

    public function cancle($id)
    {
        $request = RequestSign::find($id);
        $request->request_status = 'cancle';
        $request->save();

        $this->updateLog('การทำสัญญา', $request->id, $request->request_status);
    }

    public function destroy(RequestSign $requestSign)
    {
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestSign->id_card);
        File::delete($destinationPath . $requestSign->sign_sheet);

        $this->deleteLog('การทำสัญญา', $requestSign->id);

        return response()->json($requestSign->delete());
    }
}
