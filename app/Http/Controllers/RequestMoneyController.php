<?php

namespace App\Http\Controllers;

use App\Models\RequestLog;
use App\Models\RequestMoney;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RequestMoneyController extends Controller
{
    protected $pathCurrent = 'request_money/';

    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestMoney::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'pedding')
            ->count();

        $approve = RequestMoney::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'approve')
            ->count();
        $cancle = RequestMoney::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'cancle')
            ->count();
        $all = RequestMoney::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

        $output = RequestMoney::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

            if ($item['slip'] == 'no_img.png') {
                $item['slip'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['slip'] = '/' . $this->path . $this->pathCurrent . $item['slip'];
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
        $credentials['slip'] = 'no_img.png';
        $created = RequestMoney::create($credentials);

        $RequestMoney = RequestMoney::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestMoney->id_card = $filename_id_card;
        }

        $file_slip = $request->file('slip');
        if ($file_slip) {
            $filename_slip = $created->id . '_slip.' . $file_slip->getClientOriginalExtension();
            $saveImagePath_slip = $this->path . $this->pathCurrent;
            $file_slip->move($saveImagePath_slip, $filename_slip);
            $RequestMoney->slip = $filename_slip;
        }

        $RequestMoney->save();

        if ($RequestMoney['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestMoney['id_card'];
        } else {
            $created['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestMoney['slip'] != 'no_img.png') {
            $created['slip'] = '/' . $this->path . $this->pathCurrent . $RequestMoney['slip'];
        } else {
            $created['slip'] = '/' . $this->path . 'no_img.png';
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
        $log['type'] = 'การรับเงิน';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestMoney  $requestMoney
     * @return \Illuminate\Http\Response
     */
    public function show(RequestMoney $requestMoney)
    {
        if ($requestMoney['id_card'] != 'no_img.png') {
            $requestMoney['id_card'] = '/' . $this->path . $this->pathCurrent . $requestMoney['id_card'];
        } else {
            $requestMoney['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestMoney['slip'] != 'no_img.png') {
            $requestMoney['slip'] = '/' . $this->path . $this->pathCurrent . $requestMoney['slip'];
        } else {
            $requestMoney['slip'] = '/' . $this->path . 'no_img.png';
        }
        return response()->json($requestMoney);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestMoney  $requestMoney
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestMoney $requestMoney)
    {
        if ($requestMoney['request_status'] == 'pedding') {
            $requestMoney['request_status'] = 'approve';
        } else if ($requestMoney['request_status'] == 'approve') {
            $requestMoney['request_status'] = 'pedding';
        } else if ($requestMoney['request_status'] == 'cancle') {
            $requestMoney['request_status'] = 'pedding';
        }

        $this->updateLog('การรับเงิน', $requestMoney->id, $requestMoney->request_status);

        return response()->json($requestMoney->save());
    }

    public function cancle($id)
    {
        $request = RequestMoney::find($id);
        $request->request_status = 'cancle';
        $request->save();

        $this->updateLog('การรับเงิน', $request->id, $request->request_status);
    }

    public function destroy(RequestMoney $requestMoney)
    {
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestMoney->id_card);
        if ($requestMoney->slip != 'no_img.png') {
            File::delete($destinationPath . $requestMoney->slip);
        }

        $this->deleteLog('การรับเงิน', $requestMoney->id);

        return response()->json($requestMoney->delete());
    }
}
