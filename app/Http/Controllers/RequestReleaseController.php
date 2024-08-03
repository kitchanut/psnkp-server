<?php

namespace App\Http\Controllers;

use App\Models\RequestLog;
use App\Models\RequestRelease;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class RequestReleaseController extends Controller
{
    protected $pathCurrent = 'request_release/';

    public function index()
    {
        //
    }

    public function countData(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $pedding = RequestRelease::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'pedding')
            ->count();

        $approve = RequestRelease::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'approve')
            ->count();
        $cancle = RequestRelease::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->where('request_status', 'cancle')
            ->count();
        $all = RequestRelease::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

        $output = RequestRelease::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
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

            if ($item['release_img'] == 'no_img.png') {
                $item['release_img'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['release_img'] = '/' . $this->path . $this->pathCurrent . $item['release_img'];
            }

            if ($item['sale_sheet'] == 'no_img.png') {
                $item['sale_sheet'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['sale_sheet'] = '/' . $this->path . $this->pathCurrent . $item['sale_sheet'];
            }

            if ($item['insurance_font_sheet'] == 'no_img.png') {
                $item['insurance_font_sheet'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['insurance_font_sheet'] = '/' . $this->path . $this->pathCurrent . $item['insurance_font_sheet'];
            }

            if ($item['insurance_back_sheet'] == 'no_img.png') {
                $item['insurance_back_sheet'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['insurance_back_sheet'] = '/' . $this->path . $this->pathCurrent . $item['insurance_back_sheet'];
            }

            if ($item['receipt'] == 'no_img.png') {
                $item['receipt'] = '/' . $this->path . 'no_img.png';
            } else {
                $item['receipt'] = '/' . $this->path . $this->pathCurrent . $item['receipt'];
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
        $credentials['release_img'] = 'no_img.png';
        $credentials['sale_sheet'] = 'no_img.png';
        $credentials['insurance_font_sheet'] = 'no_img.png';
        $credentials['insurance_back_sheet'] = 'no_img.png';
        $credentials['receipt'] = 'no_img.png';
        $credentials['slip'] = 'no_img.png';
        $created = RequestRelease::create($credentials);

        $RequestRelease = RequestRelease::find($created->id);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $created->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestRelease->id_card = $filename_id_card;
        }

        $file_release_img = $request->file('release_img');
        if ($file_release_img) {
            $filename_release_img = $created->id . '_release_img.' . $file_release_img->getClientOriginalExtension();
            $saveImagePath_release_img = $this->path . $this->pathCurrent;
            $file_release_img->move($saveImagePath_release_img, $filename_release_img);
            $RequestRelease->release_img = $filename_release_img;
        }

        $file_sale_sheet = $request->file('sale_sheet');
        if ($file_sale_sheet) {
            $filename_sale_sheet = $created->id . '_sale_sheet.' . $file_sale_sheet->getClientOriginalExtension();
            $saveImagePath_sale_sheet = $this->path . $this->pathCurrent;
            $file_sale_sheet->move($saveImagePath_sale_sheet, $filename_sale_sheet);
            $RequestRelease->sale_sheet = $filename_sale_sheet;
        }

        $file_insurance_font_sheet = $request->file('insurance_font_sheet');
        if ($file_insurance_font_sheet) {
            $filename_insurance_font_sheet = $created->id . '_insurance_font_sheet.' . $file_insurance_font_sheet->getClientOriginalExtension();
            $saveImagePath_insurance_font_sheet = $this->path . $this->pathCurrent;
            $file_insurance_font_sheet->move($saveImagePath_insurance_font_sheet, $filename_insurance_font_sheet);
            $RequestRelease->insurance_font_sheet = $filename_insurance_font_sheet;
        }

        $file_insurance_back_sheet = $request->file('insurance_back_sheet');
        if ($file_insurance_back_sheet) {
            $filename_insurance_back_sheet = $created->id . '_insurance_back_sheet.' . $file_insurance_back_sheet->getClientOriginalExtension();
            $saveImagePath_insurance_back_sheet = $this->path . $this->pathCurrent;
            $file_insurance_back_sheet->move($saveImagePath_insurance_back_sheet, $filename_insurance_back_sheet);
            $RequestRelease->insurance_back_sheet = $filename_insurance_back_sheet;
        }

        $file_receipt = $request->file('receipt');
        if ($file_receipt) {
            $filename_receipt = $created->id . '_receipt.' . $file_receipt->getClientOriginalExtension();
            $saveImagePath_receipt = $this->path . $this->pathCurrent;
            $file_receipt->move($saveImagePath_receipt, $filename_receipt);
            $RequestRelease->receipt = $filename_receipt;
        }

        $file_slip = $request->file('slip');
        if ($file_slip) {
            $filename_slip = $created->id . '_slip.' . $file_slip->getClientOriginalExtension();
            $saveImagePath_slip = $this->path . $this->pathCurrent;
            $file_slip->move($saveImagePath_slip, $filename_slip);
            $RequestRelease->slip = $filename_slip;
        }

        $RequestRelease->save();

        if ($RequestRelease['id_card'] != 'no_img.png') {
            $created['id_card'] = '/' . $this->path . $this->pathCurrent . $RequestRelease['id_card'];
        } else {
            $created['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestRelease['release_img'] != 'no_img.png') {
            $created['release_img'] = '/' . $this->path . $this->pathCurrent . $RequestRelease['release_img'];
        } else {
            $created['release_img'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestRelease['sale_sheet'] != 'no_img.png') {
            $created['sale_sheet'] = '/' . $this->path . $this->pathCurrent . $RequestRelease['sale_sheet'];
        } else {
            $created['sale_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestRelease['insurance_font_sheet'] != 'no_img.png') {
            $created['insurance_font_sheet'] = '/' . $this->path . $this->pathCurrent . $RequestRelease['insurance_font_sheet'];
        } else {
            $created['insurance_font_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestRelease['insurance_back_sheet'] != 'no_img.png') {
            $created['insurance_back_sheet'] = '/' . $this->path . $this->pathCurrent . $RequestRelease['insurance_back_sheet'];
        } else {
            $created['insurance_back_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestRelease['receipt'] != 'no_img.png') {
            $created['receipt'] = '/' . $this->path . $this->pathCurrent . $RequestRelease['receipt'];
        } else {
            $created['receipt'] = '/' . $this->path . 'no_img.png';
        }

        if ($RequestRelease['slip'] != 'no_img.png') {
            $created['slip'] = '/' . $this->path . $this->pathCurrent . $RequestRelease['slip'];
        } else {
            $created['slip'] = '/' . $this->path . 'no_img.png';
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
        $log['type'] = 'ปล่อยรถ';
        $log['request_status'] = 'pedding';
        RequestLog::create($log);


        return response()->json($created);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestRelease  $requestRelease
     * @return \Illuminate\Http\Response
     */
    public function show(RequestRelease $requestRelease)
    {
        if ($requestRelease['id_card'] != 'no_img.png') {
            $requestRelease['id_card'] = '/' . $this->path . $this->pathCurrent . $requestRelease['id_card'];
        } else {
            $requestRelease['id_card'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestRelease['release_img'] != 'no_img.png') {
            $requestRelease['release_img'] = '/' . $this->path . $this->pathCurrent . $requestRelease['release_img'];
        } else {
            $requestRelease['release_img'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestRelease['ImageCar'] != 'no_img.png') {
            $requestRelease['ImageCar'] = '/' . $this->path . $this->pathCurrent . $requestRelease['ImageCar'];
        } else {
            $requestRelease['ImageCar'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestRelease['sale_sheet'] != 'no_img.png') {
            $requestRelease['sale_sheet'] = '/' . $this->path . $this->pathCurrent . $requestRelease['sale_sheet'];
        } else {
            $requestRelease['sale_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestRelease['insurance_font_sheet'] != 'no_img.png') {
            $requestRelease['insurance_font_sheet'] = '/' . $this->path . $this->pathCurrent . $requestRelease['insurance_font_sheet'];
        } else {
            $requestRelease['insurance_font_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestRelease['insurance_back_sheet'] != 'no_img.png') {
            $requestRelease['insurance_back_sheet'] = '/' . $this->path . $this->pathCurrent . $requestRelease['insurance_back_sheet'];
        } else {
            $requestRelease['insurance_back_sheet'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestRelease['receipt'] != 'no_img.png') {
            $requestRelease['receipt'] = '/' . $this->path . $this->pathCurrent . $requestRelease['receipt'];
        } else {
            $requestRelease['receipt'] = '/' . $this->path . 'no_img.png';
        }

        if ($requestRelease['slip'] != 'no_img.png') {
            $requestRelease['slip'] = '/' . $this->path . $this->pathCurrent . $requestRelease['slip'];
        } else {
            $requestRelease['slip'] = '/' . $this->path . 'no_img.png';
        }
        return response()->json($requestRelease);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestRelease  $requestRelease
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestRelease $requestRelease)
    {
        $credentials = $request->all();
        if ($requestRelease['request_status'] == 'pedding') {
            $requestRelease['request_status'] = 'approve';
        } else if ($requestRelease['request_status'] == 'approve') {
            $requestRelease['request_status'] = 'pedding';
        } else if ($requestRelease['request_status'] == 'cancle') {
            $requestRelease['request_status'] = 'pedding';
        }
        $requestRelease->save();

        $this->updateLog('ปล่อยรถ', $requestRelease->id, $requestRelease->request_status);

        return response()->json($credentials);
    }

    public function cancle($id)
    {
        $request = RequestRelease::find($id);
        $request->request_status = 'cancle';
        $request->save();

        $this->updateLog('ปล่อยรถ', $request->id, $request->request_status);
    }

    public function destroy(RequestRelease $requestRelease)
    {
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $requestRelease->id_card);
        File::delete($destinationPath . $requestRelease->release_img);
        File::delete($destinationPath . $requestRelease->insurance_font_sheet);
        File::delete($destinationPath . $requestRelease->insurance_back_sheet);
        File::delete($destinationPath . $requestRelease->receipt);
        File::delete($destinationPath . $requestRelease->sale_sheet);
        File::delete($destinationPath . $requestRelease->slip);

        $this->deleteLog('ปล่อยรถ', $requestRelease->id);

        return response()->json($requestRelease->delete());
    }
}
