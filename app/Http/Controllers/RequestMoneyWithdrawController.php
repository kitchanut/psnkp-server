<?php

namespace App\Http\Controllers;

use App\Models\RequestLog;
use App\Models\RequestMoneyWithdraw;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestMoneyWithdrawController extends Controller
{
    protected $pathCurrent = 'request_money_withdraw/';

    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestMoneyWithdraw::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'pedding')
            ->count();

        $approve = RequestMoneyWithdraw::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'approve')
            ->count();
        $cancle = RequestMoneyWithdraw::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'cancle')
            ->count();
        $all = RequestMoneyWithdraw::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

        $output = RequestMoneyWithdraw::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

            if ($item['sale_sheet'] == 'no_img.png') {
                $item['sale_sheet'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['sale_sheet'] = '/' . $this->path . $this->pathCurrent . $item['sale_sheet'];
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
        $credentials['sale_sheet'] = 'no_img.png';
        $created = RequestMoneyWithdraw::create($credentials);

        $RequestMoneyWithdraw = RequestMoneyWithdraw::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestMoneyWithdraw->id_card = $filename_id_card;
        }

        $file_sale_sheet = $request->file('sale_sheet');
        if ($file_sale_sheet) {
            $filename_sale_sheet = $created->id . '_sale_sheet.' . $file_sale_sheet->getClientOriginalExtension();
            $saveImagePath_sale_sheet = $this->path . $this->pathCurrent;
            $file_sale_sheet->move($saveImagePath_sale_sheet, $filename_sale_sheet);
            $RequestMoneyWithdraw->sale_sheet = $filename_sale_sheet;
        }

        $RequestMoneyWithdraw->save();

        if ($RequestMoneyWithdraw['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestMoneyWithdraw['id_card'];
        } else {
            $created['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestMoneyWithdraw['sale_sheet'] != 'no_img.png') {
            $created['sale_sheet'] = '/' . $this->path . $this->pathCurrent . $RequestMoneyWithdraw['sale_sheet'];
        } else {
            $created['sale_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if (!is_null(auth()->user())) {
            $log['user_id'] = auth()->user()->id;
        }
        $log['ref_id'] = $created['id'];
        $log['lineUUID'] = $created['lineUUID'];
        $log['displayName'] = $created['displayName'];
        $log['pictureUrl'] = $created['pictureUrl'];
        $log['sale_name'] = $created['sale_name'];
        $log['branch_name'] = $created['branch_name'];
        $log['car_no'] = $created['car_no'];
        $log['type'] = 'เบิกเงิน';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestMoneyWithdraw  $requestMoneyWithdraw
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $requestMoneyWithdraw = RequestMoneyWithdraw::find($id);
        if ($requestMoneyWithdraw['id_card'] != 'no_img.png') {
            $requestMoneyWithdraw['id_card'] = '/' . $this->path . $this->pathCurrent . $requestMoneyWithdraw['id_card'];
        } else {
            $requestMoneyWithdraw['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestMoneyWithdraw['sale_sheet'] != 'no_img.png') {
            $requestMoneyWithdraw['sale_sheet'] = '/' . $this->path . $this->pathCurrent . $requestMoneyWithdraw['sale_sheet'];
        } else {
            $requestMoneyWithdraw['sale_sheet'] = '/' . $this->path . 'no_img.png';
        }
        return response()->json($requestMoneyWithdraw);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestMoneyWithdraw  $requestMoneyWithdraw
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $RequestMoneyWithdraw = RequestMoneyWithdraw::find($id);
        if ($RequestMoneyWithdraw['request_status'] == 'pedding') {
            $RequestMoneyWithdraw['request_status'] = 'approve';
        } else if ($RequestMoneyWithdraw['request_status'] == 'approve') {
            $RequestMoneyWithdraw['request_status'] = 'pedding';
        } else if ($RequestMoneyWithdraw['request_status'] == 'cancle') {
            $RequestMoneyWithdraw['request_status'] = 'pedding';
        }

        $this->updateLog('เบิกเงิน', $RequestMoneyWithdraw->id, $RequestMoneyWithdraw->request_status);

        return response()->json($RequestMoneyWithdraw->save());
    }

    public function cancle($id)
    {
        $request = RequestMoneyWithdraw::find($id);
        $request->request_status = 'cancle';
        $request->save();

        $this->updateLog('เบิกเงิน', $request->id, $request->request_status);
    }

    public function destroy($id)
    {
        $RequestMoneyWithdraw = RequestMoneyWithdraw::find($id);
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $RequestMoneyWithdraw->id_card);
        File::delete($destinationPath . $RequestMoneyWithdraw->sale_sheet);

        $this->deleteLog('เบิกเงิน', $RequestMoneyWithdraw->id);

        return response()->json($RequestMoneyWithdraw->delete());
    }
}
