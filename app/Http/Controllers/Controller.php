<?php

namespace App\Http\Controllers;

use App\Models\RequestLog;
use App\Models\UserLine;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;



class Controller extends BaseController
{
    public $path = 'assets/images/';
    public $temp = 'assets/temps/';


    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    function registerUserLine($data)
    {
        $userLine = UserLine::where('lineUUID', $data['lineUUID'])->first();
        if (!$userLine) {
            UserLine::create($data);
            return true;
        } else {
            return false;
        }
    }

    function updateLog($type, $ref_id, $status)
    {
        $RequestLog = RequestLog::where([['type', $type], ['ref_id',  $ref_id]])->first();
        if ($RequestLog) {
            $RequestLog->request_status = $status;
            $RequestLog->save();
        }
        return true;
    }

    function deleteLog($type, $ref_id)
    {
        $RequestLog = RequestLog::where([['type', $type], ['ref_id', $ref_id]])->first();
        if ($RequestLog) {
            $RequestLog->delete();
        }
        return true;
    }

    function getFormatTextMessage($text)
    {
        $datas = [];
        $datas['type'] = 'text';
        $datas['text'] = $text;
        return $datas;
    }

    public function sentMessage($encodeJson, $datas)
    {
        $datasReturn = [];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $datas['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $encodeJson,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer " . $datas['token'],
                "cache-control: no-cache",
                "content-type: application/json; charset=UTF-8",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $datasReturn['result'] = 'E';
            $datasReturn['message'] = $err;
        } else {
            if ($response == "{}") {
                $datasReturn['result'] = 'S';
                $datasReturn['message'] = 'Success';
            } else {
                $datasReturn['result'] = 'E';
                $datasReturn['message'] = $response;
            }
        }
        return $datasReturn;
    }
}
