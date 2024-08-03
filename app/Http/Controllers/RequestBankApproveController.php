<?php

namespace App\Http\Controllers;

use App\Models\RequestBankApprove;
use App\Models\RequestLog;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RequestBankApproveController extends Controller
{
    protected $pathCurrent = 'request_bankApprove/';

    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestBankApprove::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'pedding')
            ->count();

        $approve = RequestBankApprove::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'approve')
            ->count();
        $cancle = RequestBankApprove::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'cancle')
            ->count();
        $all = RequestBankApprove::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

        $output = RequestBankApprove::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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
        $credentials['po'] = 'no_img.png';
        $created = RequestBankApprove::create($credentials);

        $RequestBankApprove = RequestBankApprove::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestBankApprove->id_card = $filename_id_card;
        }

        $file_po = $request->file('po');
        if ($file_po) {
            $filename_po = $created->id . '_po.' . $file_po->getClientOriginalExtension();
            $saveImagePath_po = $this->path . $this->pathCurrent;
            $file_po->move($saveImagePath_po, $filename_po);
            $RequestBankApprove->po = $filename_po;
        }

        $RequestBankApprove->save();

        if ($RequestBankApprove['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestBankApprove['id_card'];
        } else {
            $created['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestBankApprove['po'] != 'no_img.png') {
            $created['po'] = '/' . $this->path . $this->pathCurrent . $RequestBankApprove['po'];
        } else {
            $created['po'] = '/' . $this->path . 'no_img.png';
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
        $log['car_no'] = $created['car_no'];
        $log['type'] = 'แบงค์อนุมัติ';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }

    public function show($id)
    {
        $requestBankApprove = RequestBankApprove::find($id);
        if ($requestBankApprove['id_card'] != 'no_img.png') {
            $requestBankApprove['id_card'] = '/' . $this->path . $this->pathCurrent . $requestBankApprove['id_card'];
        } else {
            $requestBankApprove['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestBankApprove['po'] != 'no_img.png') {
            $requestBankApprove['po'] = '/' . $this->path . $this->pathCurrent . $requestBankApprove['po'];
        } else {
            $requestBankApprove['po'] = '/' . $this->path . 'no_img.png';
        }

        return response()->json($requestBankApprove);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestBankApprove  $requestBankApprove
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $requestBankApprove = RequestBankApprove::find($id);
        if ($requestBankApprove['request_status'] == 'pedding') {
            $requestBankApprove['request_status'] = 'approve';
        } else if ($requestBankApprove['request_status'] == 'approve') {
            $requestBankApprove['request_status'] = 'pedding';
        } else if ($requestBankApprove['request_status'] == 'cancle') {
            $requestBankApprove['request_status'] = 'pedding';
        }
        $result = $requestBankApprove->save();

        $this->updateLog('แบงค์อนุมัติ', $requestBankApprove->id, $requestBankApprove->request_status);

        return response()->json($result);
    }

    public function cancle($id)
    {
        $request = RequestBankApprove::find($id);
        $request->request_status = 'cancle';
        $request->save();

        $this->updateLog('แบงค์อนุมัติ', $request->id, $request->request_status);


        return response()->json('cancle');
    }

    public function destroy($id)
    {
        $requestBankApprove = RequestBankApprove::find($id);
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestBankApprove->id_card);

        $this->deleteLog('แบงค์อนุมัติ', $requestBankApprove->id);

        return response()->json($requestBankApprove->delete());
    }
}
