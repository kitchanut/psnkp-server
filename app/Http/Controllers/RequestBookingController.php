<?php

namespace App\Http\Controllers;

use App\Models\RequestBooking;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class RequestBookingController extends Controller
{
    protected $pathCurrent = 'request_booking/';

    public function index()
    {
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestBooking::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_booking_status', 'pedding')
            ->count();

        $approve = RequestBooking::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_booking_status', 'approve')
            ->count();
        $cancle = RequestBooking::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_booking_status', 'cancle')
            ->count();
        $all = RequestBooking::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

        $output = RequestBooking::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->when($toggle, function ($query) use ($toggle) {
                $query->where('request_booking_status', (string) $toggle);
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

            if ($item['booking_slip'] == 'no_img.png') {
                $item['booking_slip'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['booking_slip'] = '/' . $this->path . $this->pathCurrent . $item['booking_slip'];
            }

            if ($item['receipt'] == 'no_img.png') {
                $item['receipt'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['receipt'] = '/' . $this->path . $this->pathCurrent . $item['receipt'];
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
        $credentials['booking_sheet'] = 'no_img.png';
        $credentials['booking_slip'] = 'no_img.png';
        $credentials['receipt'] = 'no_img.png';
        if ($credentials['customer_job'] == 'อื่น ๆ') {
            $credentials['customer_job'] = $credentials['customer_job_list'];
        }
        unset($credentials['customer_job_list']);
        $created = RequestBooking::create($credentials);

        $requestBooking = RequestBooking::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $requestBooking->id_card = $filename_id_card;
        }


        $file_booking_sheet = $request->file('booking_sheet');
        if ($file_booking_sheet) {
            $filename_booking_sheet = $created->id . '_booking_sheet.' . $file_booking_sheet->getClientOriginalExtension();
            $saveImagePath_booking_sheet = $this->path . $this->pathCurrent;
            $file_booking_sheet->move($saveImagePath_booking_sheet, $filename_booking_sheet);
            $requestBooking->booking_sheet = $filename_booking_sheet;
        }

        $file_booking_slip = $request->file('booking_slip');
        if ($file_booking_slip) {
            $filename_booking_slip = $created->id . '_booking_slip.' . $file_booking_slip->getClientOriginalExtension();
            $saveImagePath_booking_slip = $this->path . $this->pathCurrent;
            $file_booking_slip->move($saveImagePath_booking_slip, $filename_booking_slip);
            $requestBooking->booking_slip = $filename_booking_slip;
        }

        $file_receipt = $request->file('receipt');
        if ($file_receipt) {
            $filename_receipt = $created->id . '_receipt.' . $file_receipt->getClientOriginalExtension();
            $saveImagePath_receipt = $this->path . $this->pathCurrent;
            $file_receipt->move($saveImagePath_receipt, $filename_receipt);
            $requestBooking->receipt = $filename_receipt;
        }

        $requestBooking->save();

        if ($requestBooking['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $requestBooking['id_card'];
        } else {
            $created['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestBooking['booking_sheet'] != 'no_img.png') {
            $created['booking_sheet'] = '/' . $this->path . $this->pathCurrent . $requestBooking['booking_sheet'];
        } else {
            $created['booking_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestBooking['booking_slip'] != 'no_img.png') {
            $created['booking_slip'] = '/' . $this->path . $this->pathCurrent . $requestBooking['booking_slip'];
        } else {
            $created['booking_slip'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestBooking['receipt'] != 'no_img.png') {
            $created['receipt'] = '/' . $this->path . $this->pathCurrent . $requestBooking['receipt'];
        } else {
            $created['receipt'] = '/' . $this->path . 'no_img.png';
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
        $log['type'] = 'การจอง';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        return response()->json($created);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestBooking  $requestBooking
     * @return \Illuminate\Http\Response
     */
    public function show(RequestBooking $requestBooking)
    {

        if ($requestBooking['id_card'] == 'no_img.png') {
            $requestBooking['id_card'] = '/' . $this->path . 'no_img.png';
        } else {
            $requestBooking['id_card'] = '/' . $this->path . $this->pathCurrent . $requestBooking['id_card'];
        }

        if ($requestBooking['booking_sheet'] == 'no_img.png') {
            $requestBooking['booking_sheet'] = '/' . $this->path . 'no_img.png';
        } else {
            $requestBooking['booking_sheet'] = '/' . $this->path . $this->pathCurrent . $requestBooking['booking_sheet'];
        }

        if ($requestBooking['booking_slip'] == 'no_img.png') {
            $requestBooking['booking_slip'] = '/' . $this->path . 'no_img.png';
        } else {
            $requestBooking['booking_slip'] = '/' . $this->path . $this->pathCurrent . $requestBooking['booking_slip'];
        }

        if ($requestBooking['receipt'] == 'no_img.png') {
            $requestBooking['receipt'] = '/' . $this->path . 'no_img.png';
        } else {
            $requestBooking['receipt'] = '/' . $this->path . $this->pathCurrent . $requestBooking['receipt'];
        }
        return response()->json($requestBooking);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestBooking  $requestBooking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestBooking $requestBooking)
    {
        $credentials = $request->all();
        if ($requestBooking['request_booking_status'] == 'pedding') {
            $requestBooking['request_booking_status'] = 'approve';
        } else if ($requestBooking['request_booking_status'] == 'approve') {
            $requestBooking['request_booking_status'] = 'pedding';
        } else if ($requestBooking['request_booking_status'] == 'cancle') {
            $requestBooking['request_booking_status'] = 'pedding';
        }

        $this->updateLog('การจอง', $requestBooking->id, $requestBooking->request_booking_status);


        $requestBooking->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RequestBooking  $RequestBooking
     * @return \Illuminate\Http\Response
     */
    public function cancle($id)
    {

        $requestBooking = RequestBooking::find($id);
        $requestBooking->request_booking_status = 'cancle';
        $requestBooking->save();

        $this->updateLog('การจอง', $requestBooking->id, $requestBooking->request_booking_status);
    }

    public function destroy(RequestBooking $requestBooking)
    {
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestBooking->id_card);
        File::delete($destinationPath . $requestBooking->booking_sheet);
        File::delete($destinationPath . $requestBooking->booking_slip);
        File::delete($destinationPath . $requestBooking->receipt);

        $this->deleteLog('การจอง', $requestBooking->id);


        return response()->json($requestBooking->delete());
    }
}
