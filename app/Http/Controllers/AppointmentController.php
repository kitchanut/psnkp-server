<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bank;
use App\Models\Bank_branch;
use App\Models\Branch_team;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Middle_price;
use App\Models\Middle_price_detail;
use App\Models\RequestAppointment;
use App\Models\RequestBooking;
use App\Models\RequestLog;
use App\Models\RequestSignDeposit;
use App\Models\User;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    protected $pathCurrent = 'request_appointment/';
    protected $pathCurrentDeposit = 'request_signDeposit/';
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $requestFormData)
    {

        // $credentials = $request->only(['working_id', 'appointment_date', 'bank_id', 'bank_branch_id', 'sale_name', 'sale_tel']);
        $dataInput = $requestFormData->all();
        $request =  json_decode($dataInput['formData']);
        $credentials['working_id'] = $request->working_id;
        $credentials['appointment_type'] = $request->appointment_type;
        $credentials['appointment_date'] = $request->appointment_date;
        (isset($request->deposit_date)) ? $credentials['deposit_date'] = $request->deposit_date : '';
        (isset($request->appointment_location)) ? $credentials['appointment_location'] = $request->appointment_location : '';
        $credentials['bank_id'] = $request->bank_id;
        $credentials['bank_branch_id'] = $request->bank_branch_id;
        $credentials['sale_name'] = $request->sale_name;
        $credentials['sale_tel'] = $request->sale_tel;

        $createAppointment = Appointment::create($credentials);

        $working = Working::find($request->working_id);
        $sale = User::find($working->sale_id);
        $Branch_team = Branch_team::find($working->branch_team_id);
        $car = Car::find($working->car_id);
        $bank = Bank::find($request->bank_id);
        $bank_branch = Bank_branch::find($request->bank_branch_id);

        if ($credentials['appointment_type'] == 'นัดทำสัญญา') {
            $dataHistory['working_id'] = $request->working_id;
            $dataHistory['sale_name'] = $sale->first_name;
            $dataHistory['branch_name'] = $Branch_team->branch_team_name;
            $dataHistory['car_no'] = $car->car_no;
            $dataHistory['appointment_date'] = $request->appointment_date;
            $dataHistory['bank_name'] = $bank->bank_nick_name;
            $dataHistory['bank_branch_name'] = $bank_branch->bank_branch_name;
            $dataHistory['mtk_name'] = $request->sale_name;
            $dataHistory['mtk_tel'] = $request->sale_tel;
            $dataHistory['note'] = "ลงจากระบบอัตโนมัติ";
            $dataHistory['request_status'] = "approve";
            $RequestHistory = RequestAppointment::create($dataHistory);

            $file_id_card = $requestFormData->file('id_card');
            if ($file_id_card) {
                $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
                $saveImagePath_id_card = $this->path . $this->pathCurrent;
                $file_id_card->move($saveImagePath_id_card, $filename_id_card);
                $RequestHistory->id_card = $filename_id_card;
            }
            $RequestHistory->save();

            if (!is_null(auth()->user())) {
                $log['user_id'] = auth()->user()->id;
            }
            $log['working_id'] = $RequestHistory['working_id'];
            $log['ref_id'] = $RequestHistory['id'];
            $log['sale_name'] = $RequestHistory['sale_name'];
            $log['branch_name'] = $RequestHistory['branch_name'];
            $log['car_no'] = $RequestHistory['car_no'];
            $log['type'] = 'นัดทำสัญญา';
            $log['note'] = "ลงจากระบบอัตโนมัติ";
            $log['request_status'] = 'pedding';
            RequestLog::create($log);
        } elseif ($credentials['appointment_type'] == 'ฝากเซนต์') {
            $dataHistory['working_id'] = $request->working_id;
            $dataHistory['sale_name'] = $sale->first_name;
            $dataHistory['branch_name'] = $Branch_team->branch_team_name;
            $dataHistory['car_no'] = $car->car_no;
            $dataHistory['date'] = $request->appointment_date;
            $dataHistory['bank_name'] = $bank->bank_nick_name;
            $dataHistory['bank_branch_name'] = $bank_branch->bank_branch_name;
            $dataHistory['mtk_name'] = $request->sale_name;
            $dataHistory['mtk_tel'] = $request->sale_tel;
            $dataHistory['sign_at'] = $request->appointment_location;
            $dataHistory['note'] = "ลงจากระบบอัตโนมัติ";
            $dataHistory['request_status'] = "approve";
            $RequestHistory = RequestSignDeposit::create($dataHistory);

            $file_id_card = $requestFormData->file('id_card');
            if ($file_id_card) {
                $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
                $saveImagePath_id_card = $this->path . $this->pathCurrentDeposit;
                $file_id_card->move($saveImagePath_id_card, $filename_id_card);
                $RequestHistory->id_card = $filename_id_card;
            }
            $RequestHistory->save();

            if (!is_null(auth()->user())) {
                $log['user_id'] = auth()->user()->id;
            }
            $log['working_id'] = $RequestHistory['working_id'];
            $log['ref_id'] = $RequestHistory['id'];
            $log['sale_name'] = $RequestHistory['sale_name'];
            $log['branch_name'] = $RequestHistory['branch_name'];
            $log['car_no'] = $RequestHistory['car_no'];
            $log['type'] = 'ฝากเซนต์';
            $log['note'] = "ลงจากระบบอัตโนมัติ";
            $log['request_status'] = 'pedding';
            RequestLog::create($log);
        }


        //อัพเดตสถานะของงาน
        $updateStatus = Working::find($request->working_id);
        $updateStatus->bank_id = $request->bank_id;
        $updateStatus->user_id = $requestFormData->user()->id;

        $updateStatus->bank_branch_id = $request->bank_branch_id;
        $updateStatus->work_status = $request->work_status;
        $updateStatus->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $requestFormData, Appointment $appointment)
    {
        $dataInput = $requestFormData->all();
        $request =  json_decode($dataInput['formData']);

        $credentials['appointment_type'] = $request->appointment_type;
        $credentials['appointment_date'] = $request->appointment_date;
        (isset($request->deposit_date)) ? $credentials['deposit_date'] = $request->deposit_date : '';
        (isset($request->appointment_location)) ? $credentials['appointment_location'] = $request->appointment_location : '';
        $credentials['bank_id'] = $request->bank_id;
        $credentials['bank_branch_id'] = $request->bank_branch_id;
        $credentials['sale_name'] = $request->sale_name;
        $credentials['sale_tel'] = $request->sale_tel;
        $appointment->update($credentials);

        $working = Working::find($request->working_id);
        $sale = User::find($working->sale_id);
        $Branch_team = Branch_team::find($working->branch_team_id);
        $car = Car::find($working->car_id);
        $bank = Bank::find($request->bank_id);
        $bank_branch = Bank_branch::find($request->bank_branch_id);


        if ($credentials['appointment_type'] == 'นัดทำสัญญา') {
            $dataHistory['working_id'] = $request->working_id;
            $dataHistory['sale_name'] = $sale->first_name;
            $dataHistory['branch_name'] = $Branch_team->branch_team_name;
            $dataHistory['car_no'] = $car->car_no;
            $dataHistory['appointment_date'] = $request->appointment_date;
            $dataHistory['bank_name'] = $bank->bank_nick_name;
            $dataHistory['bank_branch_name'] = $bank_branch->bank_branch_name;
            $dataHistory['mtk_name'] = $request->sale_name;
            $dataHistory['mtk_tel'] = $request->sale_tel;
            $dataHistory['note'] = "แก้ไข (ลงจากระบบอัตโนมัติ)";
            $dataHistory['request_status'] = "approve";
            $RequestHistory = RequestAppointment::create($dataHistory);

            $file_id_card = $requestFormData->file('id_card');
            if ($file_id_card) {
                $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
                $saveImagePath_id_card = $this->path . $this->pathCurrent;
                $file_id_card->move($saveImagePath_id_card, $filename_id_card);
                $RequestHistory->id_card = $filename_id_card;
            }
            $RequestHistory->save();

            if (!is_null(auth()->user())) {
                $log['user_id'] = auth()->user()->id;
            }
            $log['working_id'] = $RequestHistory['working_id'];
            $log['ref_id'] = $RequestHistory['id'];
            $log['sale_name'] = $RequestHistory['sale_name'];
            $log['branch_name'] = $RequestHistory['branch_name'];
            $log['car_no'] = $RequestHistory['car_no'];
            $log['type'] = 'นัดทำสัญญา';
            $log['note'] = "แก้ไข (ลงจากระบบอัตโนมัติ)";
            $log['request_status'] = 'pedding';
            RequestLog::create($log);
        } elseif ($credentials['appointment_type'] == 'ฝากเซนต์') {
            $dataHistory['working_id'] = $request->working_id;
            $dataHistory['sale_name'] = $sale->first_name;
            $dataHistory['branch_name'] = $Branch_team->branch_team_name;
            $dataHistory['car_no'] = $car->car_no;
            $dataHistory['date'] = $request->appointment_date;
            $dataHistory['bank_name'] = $bank->bank_nick_name;
            $dataHistory['bank_branch_name'] = $bank_branch->bank_branch_name;
            $dataHistory['mtk_name'] = $request->sale_name;
            $dataHistory['mtk_tel'] = $request->sale_tel;
            $dataHistory['sign_at'] = $request->appointment_location;
            $dataHistory['note'] = "แก้ไข (ลงจากระบบอัตโนมัติ)";
            $dataHistory['request_status'] = "approve";
            $RequestHistory = RequestSignDeposit::create($dataHistory);

            $file_id_card = $requestFormData->file('id_card');
            if ($file_id_card) {
                $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
                $saveImagePath_id_card = $this->path . $this->pathCurrentDeposit;
                $file_id_card->move($saveImagePath_id_card, $filename_id_card);
                $RequestHistory->id_card = $filename_id_card;
            }
            $RequestHistory->save();

            if (!is_null(auth()->user())) {
                $log['user_id'] = auth()->user()->id;
            }
            $log['working_id'] = $RequestHistory['working_id'];
            $log['ref_id'] = $RequestHistory['id'];
            $log['sale_name'] = $RequestHistory['sale_name'];
            $log['branch_name'] = $RequestHistory['branch_name'];
            $log['car_no'] = $RequestHistory['car_no'];
            $log['type'] = 'ฝากเซนต์';
            $log['note'] = "แก้ไข (ลงจากระบบอัตโนมัติ)";
            $log['request_status'] = 'pedding';
            RequestLog::create($log);
        }

        //อัพเดตสถานะของงาน
        $queryWorking = Working::find($request->working_id);
        $queryWorking->bank_id = $request->bank_id;
        $queryWorking->bank_branch_id = $request->bank_branch_id;
        $queryWorking->user_id = $requestFormData->user()->id;
        $queryWorking->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Appointment $appointment)
    {
        //
    }

    public function checkAppointment($idWork)
    {
        $checkBooking = Appointment::where('working_id', $idWork)->first();
        $checkBank = [];

        if (empty($checkBooking)) {
            $query = DB::table('workings')
                ->where('workings.id', $idWork)
                ->first();

            $findCar = Car::select('car_serie_id', 'car_serie_sub_id', 'car_year', 'car_gear', 'amount_down')
                ->where('id', $query->car_id)
                ->first();

            if (!empty($findCar)) {
                $findPrice = Middle_price::where(
                    [
                        ['car_serie_id', $findCar->car_serie_id],
                        ['car_serie_sub_id', $findCar->car_serie_sub_id],
                        ['year', $findCar->car_year],
                        ['car_gear', $findCar->car_gear]
                    ]
                )
                    ->first();


                if (!empty($findPrice)) {
                    // $getDetail =  Middle_price_detail::where([['middle_price_id', $findPrice->id], ['middle_price', '>', 0]])->get();
                    $getBank =  Bank::all();
                    for ($i = 0; $i < count($getBank); $i++) {
                        $Middle_price_detail = Middle_price_detail::where([['middle_price_id', $findPrice->id], ['middle_price', '>', 0], ['bank_id', $getBank[$i]['id']]])->first();
                        if ($Middle_price_detail) {
                            if ($findPrice->selected == $getBank[$i]['id']) {
                                $isSelected = 1;
                            } else {
                                $isSelected = 0;
                            }
                            array_push($checkBank, array(
                                'isSelected' => $isSelected,
                                'bank_name' => $getBank[$i]['bank_nick_name'],
                                'bank_id' => $getBank[$i]['id'],
                                'amount_price' => ($Middle_price_detail['middle_price'] + $Middle_price_detail['middle_price'] * $Middle_price_detail['middle_plus'] / 100) * ($Middle_price_detail['middle_multiply'] / 100),
                                'middle_price' => $Middle_price_detail['middle_price'],
                                'middle_plus' => $Middle_price_detail['middle_plus'],
                                'middle_multiply' => $Middle_price_detail['middle_multiply'],
                            ));
                        }
                        // if (!isset($getDetail[$i])) {
                        // } else {
                        //     if ($findPrice->selected == $getDetail[$i]['bank_id']) {
                        //         $isSelected = 1;
                        //     } else {
                        //         $isSelected = 0;
                        //     }
                        //     array_push($checkBank, array(
                        //         'isSelected' => $isSelected,
                        //         'bank_name' => $getBank[$i]['bank_nick_name'],
                        //         'bank_id' => $getDetail[$i]['bank_id'],
                        //         'amount_price' => ($getDetail[$i]['middle_price'] + $getDetail[$i]['middle_price'] * $getDetail[$i]['middle_plus'] / 100) * 0.95 + (((float)$findCar->amount_down + 20000)),
                        //         'middle_price' => $getDetail[$i]['middle_price'],
                        //         'middle_plus' => $getDetail[$i]['middle_plus'],
                        //         'middle_multiply' => $getDetail[$i]['middle_multiply'],
                        //     ));
                        // }
                    }
                }
            }

            $query->working_id = $idWork;
            $query->action = "add";
        } else {
            $query = DB::table('appointments')
                ->where('working_id', $idWork)
                ->first();

            $findIdCar = DB::table('workings')
                ->where('workings.id', $idWork)
                ->first();


            if (!empty($findIdCar)) {
                $findCar = Car::select('car_serie_id', 'car_serie_sub_id', 'car_year', 'car_gear')
                    ->where('id', $findIdCar->car_id)
                    ->first();
                if (!empty($findCar)) {
                    $findPrice = Middle_price::where(
                        [
                            ['car_serie_id', $findCar->car_serie_id],
                            ['car_serie_sub_id', $findCar->car_serie_sub_id],
                            ['year', $findCar->car_year],
                            ['car_gear', $findCar->car_gear],
                        ]
                    )
                        ->first();
                    if (!empty($findPrice)) {
                        // $getDetail =  Middle_price_detail::where([['middle_price_id', $findPrice->id], ['middle_price', '>', 0]])->get();
                        $getBank =  Bank::all();
                        for ($i = 0; $i < count($getBank); $i++) {
                            $Middle_price_detail = Middle_price_detail::where([['middle_price_id', $findPrice->id], ['middle_price', '>', 0], ['bank_id', $getBank[$i]['id']]])->first();
                            if ($Middle_price_detail) {
                                if ($findPrice->selected ==  $getBank[$i]['id']) {
                                    $isSelected = 1;
                                } else {
                                    $isSelected = 0;
                                }
                                array_push($checkBank, array(
                                    'isSelected' => $isSelected,
                                    'bank_name' => $getBank[$i]['bank_nick_name'],
                                    'bank_id' => $getBank[$i]['id'],
                                    'amount_price' => ($Middle_price_detail['middle_price'] + $Middle_price_detail['middle_price'] * $Middle_price_detail['middle_plus'] / 100) * ($Middle_price_detail['middle_multiply'] / 100),
                                    'middle_price' => $Middle_price_detail['middle_price'],
                                    'middle_plus' => $Middle_price_detail['middle_plus'],
                                    'middle_multiply' => $Middle_price_detail['middle_multiply'],
                                ));
                            }
                            // if (!isset($getDetail[$i])) {
                            // } else {
                            //     if ($findPrice->selected == $getDetail[$i]['bank_id']) {
                            //         $isSelected = 1;
                            //     } else {
                            //         $isSelected = 0;
                            //     }
                            //     array_push($checkBank, array(
                            //         'isSelected' => $isSelected,
                            //         'bank_name' => $getBank[$i]['bank_nick_name'],
                            //         'bank_id' => $getDetail[$i]['bank_id'],
                            //         'amount_price' => ($getDetail[$i]['middle_price'] + $getDetail[$i]['middle_price'] * $getDetail[$i]['middle_plus'] / 100) * 0.95 + (((float)$findCar->amount_down + 20000)),
                            //         'middle_price' => $getDetail[$i]['middle_price'],
                            //         'middle_plus' => $getDetail[$i]['middle_plus'],
                            //         'middle_multiply' => $getDetail[$i]['middle_multiply'],
                            //     ));
                            // }
                        }
                    }
                }
            }
            $query->action = "edit";
        }
        $query->dataPreviewBanks = $checkBank;

        return response()->json($query);
    }
}
