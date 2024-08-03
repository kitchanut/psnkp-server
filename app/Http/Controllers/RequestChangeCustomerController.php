<?php

namespace App\Http\Controllers;

use App\Models\RequestChangeCustomer;
use App\Models\RequestLog;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RequestChangeCustomerController extends Controller
{
    protected $pathCurrent = 'request_change_customer/';

    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestChangeCustomer::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'pedding')
            ->count();

        $approve = RequestChangeCustomer::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'approve')
            ->count();
        $cancle = RequestChangeCustomer::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'cancle')
            ->count();
        $all = RequestChangeCustomer::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

        $output = RequestChangeCustomer::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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
        if ($credentials['customer_job'] == 'อื่น ๆ') {
            $credentials['customer_job'] = $credentials['customer_job_list'];
        }
        unset($credentials['customer_job_list']);
        $created = RequestChangeCustomer::create($credentials);

        $RequestChangeCustomer = RequestChangeCustomer::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestChangeCustomer->id_card = $filename_id_card;
        }

        $file_sale_sheet = $request->file('sale_sheet');
        if ($file_sale_sheet) {
            $filename_sale_sheet = $created->id . '_sale_sheet.' . $file_sale_sheet->getClientOriginalExtension();
            $saveImagePath_sale_sheet = $this->path . $this->pathCurrent;
            $file_sale_sheet->move($saveImagePath_sale_sheet, $filename_sale_sheet);
            $RequestChangeCustomer->sale_sheet = $filename_sale_sheet;
        }

        $RequestChangeCustomer->save();

        if ($RequestChangeCustomer['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestChangeCustomer['id_card'];
        } else {
            $created['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestChangeCustomer['sale_sheet'] != 'no_img.png') {
            $created['sale_sheet'] = '/' . $this->path . $this->pathCurrent . $RequestChangeCustomer['sale_sheet'];
        } else {
            $created['sale_sheet'] = '/' . $this->path . 'no_img.png';
        }

        $log['ref_id'] = $created['id'];
        $log['lineUUID'] = $created['lineUUID'];
        $log['displayName'] = $created['displayName'];
        $log['pictureUrl'] = $created['pictureUrl'];
        $log['sale_name'] = $created['sale_name'];
        $log['branch_name'] = $created['branch_name'];
        $log['car_no'] = $created['car_no'];
        $log['type'] = 'เปลี่ยนคนจอง';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestChangeCustomer  $requestChangeCustomer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $requestChangeCustomer = RequestChangeCustomer::find($id);
        if ($requestChangeCustomer['id_card'] != 'no_img.png') {
            $requestChangeCustomer['id_card'] = '/' . $this->path . $this->pathCurrent . $requestChangeCustomer['id_card'];
        } else {
            $requestChangeCustomer['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestChangeCustomer['sale_sheet'] != 'no_img.png') {
            $requestChangeCustomer['sale_sheet'] = '/' . $this->path . $this->pathCurrent . $requestChangeCustomer['sale_sheet'];
        } else {
            $requestChangeCustomer['sale_sheet'] = '/' . $this->path . 'no_img.png';
        }
        return response()->json($requestChangeCustomer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestChangeCustomer  $requestChangeCustomer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $requestChangeCustomer = RequestChangeCustomer::find($id);
        if ($requestChangeCustomer['request_status'] == 'pedding') {
            $requestChangeCustomer['request_status'] = 'approve';
        } else if ($requestChangeCustomer['request_status'] == 'approve') {
            $requestChangeCustomer['request_status'] = 'pedding';
        } else if ($requestChangeCustomer['request_status'] == 'cancle') {
            $requestChangeCustomer['request_status'] = 'pedding';
        }

        $this->updateLog('เปลี่ยนคนจอง', $requestChangeCustomer->id, $requestChangeCustomer->request_status);

        return response()->json($requestChangeCustomer->save());
    }

    public function cancle($id)
    {
        $request = RequestChangeCustomer::find($id);
        $request->request_status = 'cancle';
        $request->save();

        $this->updateLog('เปลี่ยนคนจอง', $request->id, $request->request_status);
    }

    public function destroy($id)
    {
        $requestChangeCustomer = RequestChangeCustomer::find($id);
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestChangeCustomer->id_card);
        File::delete($destinationPath . $requestChangeCustomer->sale_sheet);

        $this->deleteLog('เปลี่ยนคนจอง', $requestChangeCustomer->id);

        return response()->json($requestChangeCustomer->delete());
    }
}
