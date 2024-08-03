<?php

namespace App\Http\Controllers;

use App\Models\Linebot;
use App\Models\User;
use App\Models\UserLine;
use Illuminate\Http\Request;


class LinebotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
        $data = file_get_contents('php://input');
        $deCode = json_decode($data, true);

        $replyToken = $deCode['events'][0]['replyToken'];
        $userId = $deCode['events'][0]['source']['userId'];
        if ($deCode['events'][0]['source']['type'] == 'group') {
            $groupId = $deCode['events'][0]['source']['groupId'];
        } else {
            $groupId = '';
        }
        $text = $deCode['events'][0]['message']['text'];

        $credentials['userId'] = $userId;
        $credentials['groupId'] = $groupId;
        $credentials['text'] = $text;
        $credentials['replyToken'] = $replyToken;
        $credentials['message'] = json_encode($deCode);

        if ($text == 'GroupID') {
            $messages = [];
            $messages['replyToken'] = $replyToken;
            $messages['messages'][0] = $this->getFormatTextMessage($groupId);

            $encodeJson = json_encode($messages);

            $LINEDatas['url'] = "https://api.line.me/v2/bot/message/reply";
            $LINEDatas['token'] = env("LINE_ACCESS_TOKEN");

            $results = $this->sentMessage($encodeJson, $LINEDatas);
            return response()->json($results);
        } else if ($text == 'บอท แจ้งงานลงระบบ' or $text == 'บอท แจ้งลงงาน' or $text == 'บอท แจ้งงาน') {
            $datas = [
                "type" => "flex",
                "altText" => "แจ้งงานลงระบบ",
                "contents" => [
                    "type" => "bubble",
                    "footer" => [
                        "type" => "box",
                        "layout" => "vertical",
                        "spacing" => "sm",
                        "contents" => [
                            // [
                            //     "type" => "button",
                            //     "style" => "primary",
                            //     "height" => "sm",
                            //     "action" => [
                            //         "type" => "uri",
                            //         "label" => "งานของฉัน",
                            //         "uri" => "https://liff.line.me/1657381597-y0ebAoMG"
                            //     ],
                            //     "color" => "#F0582A",
                            // ],
                            // [
                            //     "type" => "button",
                            //     "style" => "primary",
                            //     "height" => "sm",
                            //     "action" => [
                            //         "type" => "uri",
                            //         "label" => "แจ้งจอง",
                            //         "uri" => "https://liff.line.me/1657381597-P5naXw27"
                            //     ]
                            // ],
                            // [
                            //     "type" => "button",
                            //     "style" => "primary",
                            //     "height" => "sm",
                            //     "action" => [
                            //         "type" => "uri",
                            //         "label" => "แจ้งฝากเซนต์",
                            //         "uri" => "https://liff.line.me/1657381597-y2JZRxAN"
                            //     ]
                            // ],
                            // [
                            //     "type" => "button",
                            //     "style" => "primary",
                            //     "height" => "sm",
                            //     "action" => [
                            //         "type" => "uri",
                            //         "label" => "แจ้งนัดทำสัญญา",
                            //         "uri" => "https://liff.line.me/1657381597-nXbA63Ko"
                            //     ]
                            // ],
                            // [
                            //     "type" => "button",
                            //     "style" => "primary",
                            //     "height" => "sm",
                            //     "action" => [
                            //         "type" => "uri",
                            //         "label" => "แจ้งการทำสัญญา",
                            //         "uri" => "https://liff.line.me/1657381597-2WGVzXPY"
                            //     ]
                            // ],
                            [
                                "type" => "button",
                                "style" => "primary",
                                "height" => "sm",
                                "action" => [
                                    "type" => "uri",
                                    "label" => "แจ้งแบงค์อนุมัติ",
                                    "uri" => "https://liff.line.me/1657381597-Mb2n0J3x"
                                ]
                            ],
                            // [
                            //     "type" => "button",
                            //     "style" => "primary",
                            //     "height" => "sm",
                            //     "action" => [
                            //         "type" => "uri",
                            //         "label" => "แจ้งปล่อยรถ",
                            //         "uri" => "https://liff.line.me/1657381597-lye41MpW"
                            //     ]
                            // ],
                            // [
                            //     "type" => "button",
                            //     "style" => "secondary",
                            //     "height" => "sm",
                            //     "action" => [
                            //         "type" => "uri",
                            //         "label" => "แจ้งเปลี่ยนคันจอง",
                            //         "uri" => "https://liff.line.me/1657381597-V9ezjWZ8"
                            //     ]
                            // ],
                            // [
                            //     "type" => "button",
                            //     "style" => "secondary",
                            //     "height" => "sm",
                            //     "action" => [
                            //         "type" => "uri",
                            //         "label" => "แจ้งเปลี่ยนคนจอง",
                            //         "uri" => "https://liff.line.me/1657381597-4mPBbdwv"
                            //     ]
                            // ],
                            [
                                "type" => "button",
                                "style" => "secondary",
                                "height" => "sm",
                                "action" => [
                                    "type" => "uri",
                                    "label" => "แจ้งอัพเดทข้อมูล",
                                    "uri" => "https://liff.line.me/1657381597-POvX0Vk7"
                                ]
                            ],
                            [
                                "type" => "button",
                                "style" => "primary",
                                "height" => "sm",
                                "action" => [
                                    "type" => "uri",
                                    "label" => "แจ้งรับเงิน",
                                    "uri" => "https://liff.line.me/1657381597-k9PoxeG6"
                                ],
                                "color" => "#00BCD4",
                            ],
                            [
                                "type" => "button",
                                "style" => "primary",
                                "height" => "sm",
                                "action" => [
                                    "type" => "uri",
                                    "label" => "แจ้งเบิกเงิน",
                                    "uri" => "https://liff.line.me/1657381597-qaA3WErN"
                                ],
                                "color" => "#00BCD4",
                            ],
                            // [
                            //     "type" => "button",
                            //     "style" => "primary",
                            //     "height" => "sm",
                            //     "action" => [
                            //         "type" => "uri",
                            //         "label" => "แจ้งยกเลิกการจอง",
                            //         "uri" => "https://liff.line.me/1657381597-naP8jylD"
                            //     ],
                            //     "color" => "#FFC107",
                            // ],
                        ],
                        "flex" => 0
                    ]
                ]
            ];

            $LINEDatas['url'] = "https://api.line.me/v2/bot/message/reply";
            $LINEDatas['token'] = env("LINE_ACCESS_TOKEN");

            $messages = [];
            $messages['replyToken'] = $replyToken;
            $messages['messages'][0] = $datas;
            $encodeJson = json_encode($messages);
            $results = $this->sentMessage($encodeJson, $LINEDatas);
            return response()->json($results);
        }
    }

    public function check_register(Request $request)
    {
        $credentials = $request->all();
        $results = [];

        $userLine = UserLine::where('lineUUID', $credentials['lineUUID'])->first();
        if ($userLine) {

            $userLine->displayName = $credentials['displayName'];
            $userLine->pictureUrl = $credentials['pictureUrl'];
            $userLine->save();

            if ($userLine->user_id) {
                $user = User::with('branch.branch_team')
                    ->where('id', $userLine->user_id)
                    ->first();
                $results['isCombine'] = true;
                $results['data'] = $user;
            } else {
                $results['isCombine'] = false;
                $results['data'] = null;
            }
        } else {
            UserLine::create($credentials);
            $results['isCombine'] = false;
            $results['data'] = null;
        }

        return response()->json($results);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Linebot  $linebot
     * @return \Illuminate\Http\Response
     */
    public function show(Linebot $linebot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Linebot  $linebot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Linebot $linebot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Linebot  $linebot
     * @return \Illuminate\Http\Response
     */
    public function destroy(Linebot $linebot)
    {
        //
    }
}
