<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Appointment_bank;
use App\Models\Bank;
use App\Models\Bank_branch;
use App\Models\Booking;
use App\Models\Branch_team;
use App\Models\Car;
use App\Models\Income;
use App\Models\Outlay_cost;
use App\Models\RequestBankApprove;
use App\Models\RequestLog;
use App\Models\RequestSign;
use App\Models\User;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentBankController extends Controller
{
    protected $pathCurrent = 'request_sign/';
    protected $pathCurrentBankApprove = 'request_bankApprove/';

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
        $dataInput = $requestFormData->all();
        $request =  json_decode($dataInput['formData']);
        (isset($request->working_id)) ? $credentials['working_id'] = $request->working_id : '';
        (isset($request->car_id)) ? $credentials['car_id'] = $request->car_id : '';
        (isset($request->credit)) ? $credentials['credit'] = $request->credit : '';
        (isset($request->car_price)) ? $credentials['car_price'] = $request->car_price : '';
        (isset($request->appointment_bank_list)) ? $credentials['appointment_bank_list'] = $request->appointment_bank_list : '';
        (isset($request->appointment_bank_date)) ? $credentials['appointment_bank_date'] = $request->appointment_bank_date : '';
        (isset($request->appointment_bank_type)) ? $credentials['appointment_bank_type'] = $request->appointment_bank_type : '';
        (isset($request->appointment_money)) ? $credentials['appointment_money'] = $request->appointment_money : '';
        (isset($request->appointment_money_price)) ? $credentials['appointment_money_price'] = $request->appointment_money_price : '';
        (isset($request->appointment_commission_bank)) ? $credentials['appointment_commission_bank'] = $request->appointment_commission_bank : '';
        (isset($request->appointment_book)) ? $credentials['appointment_book'] = $request->appointment_book : '';
        (isset($request->appointment_mkt)) ? $credentials['appointment_mkt'] = $request->appointment_mkt : '';
        (isset($request->appointment_date)) ? $credentials['appointment_date'] = $request->appointment_date : '';
        (isset($request->appointment_money_date)) ? $credentials['appointment_money_date'] = $request->appointment_money_date : '';
        (isset($request->appointment_book_date)) ? $credentials['appointment_book_date'] = $request->appointment_book_date : '';
        (isset($request->appointment_mkt_date)) ? $credentials['appointment_mkt_date'] = $request->appointment_mkt_date : '';
        (isset($request->car_price_persen)) ? $credentials['car_price_persen'] = $request->car_price_persen : '';
        (isset($request->bank_id)) ? $credentials['bank_id'] = $request->bank_id : '';
        (isset($request->bank_branch_id)) ? $credentials['bank_branch_id'] = $request->bank_branch_id : '';
        (isset($request->mtk_name)) ? $credentials['mtk_name'] = $request->mtk_name : '';
        (isset($request->mtk_tel)) ? $credentials['mtk_tel'] = $request->mtk_tel : '';

        $createAppointmentBank = Appointment_bank::create($credentials);

        if ($request->work_status == 5) {
            $working = Working::find($request->working_id);
            $sale = User::find($working->sale_id);
            $Branch_team = Branch_team::find($working->branch_team_id);
            $car = Car::find($working->car_id);
            $bank = Bank::find($request->bank_id);
            $bank_branch = Bank_branch::find($request->bank_branch_id);


            $dataHistory['working_id'] = $request->working_id;
            $dataHistory['sale_name'] = $sale->first_name;
            $dataHistory['branch_name'] = $Branch_team->branch_team_name;
            $dataHistory['car_no'] = $car->car_no;
            $dataHistory['sign_date'] = $request->appointment_bank_date;
            $dataHistory['bank_name'] = $bank->bank_nick_name;
            $dataHistory['bank_branch_name'] = $bank_branch->bank_branch_name;
            $dataHistory['mtk_name'] = $request->mtk_name;
            $dataHistory['mtk_tel'] = $request->mtk_tel;
            $dataHistory['credit'] = $request->credit;
            if ($request->appointment_bank_type == 1) {
                $dataHistory['document'] = 'ครบ';
            } else {
                $dataHistory['document'] = 'ไม่ครบ';
                $dataHistory['document_list'] = $request->appointment_bank_list;
            }

            $dataHistory['note'] = "ลงจากระบบอัตโนมัติ";
            $dataHistory['request_status'] = "approve";
            $RequestHistory = RequestSign::create($dataHistory);

            $file_id_card = $requestFormData->file('id_card');
            if ($file_id_card) {
                $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
                $saveImagePath_id_card = $this->path . $this->pathCurrent;
                $file_id_card->move($saveImagePath_id_card, $filename_id_card);
                $RequestHistory->id_card = $filename_id_card;
            }

            $file_sign_sheet = $requestFormData->file('sign_sheet');
            if ($file_sign_sheet) {
                $filename_sign_sheet = $RequestHistory->id . '_sign_sheet.' . $file_sign_sheet->getClientOriginalExtension();
                $saveImagePath_sign_sheet = $this->path . $this->pathCurrent;
                $file_sign_sheet->move($saveImagePath_sign_sheet, $filename_sign_sheet);
                $RequestHistory->sign_sheet = $filename_sign_sheet;
            }

            $file_booking_sheet = $requestFormData->file('booking_sheet');
            if ($file_booking_sheet) {
                $filename_booking_sheet = $RequestHistory->id . '_booking_sheet.' . $file_booking_sheet->getClientOriginalExtension();
                $saveImagePath_booking_sheet = $this->path . $this->pathCurrent;
                $file_booking_sheet->move($saveImagePath_booking_sheet, $filename_booking_sheet);
                $RequestHistory->booking_sheet = $filename_booking_sheet;
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
            $log['type'] = 'การทำสัญญา';
            $log['note'] = "ลงจากระบบอัตโนมัติ";
            $log['request_status'] = 'pedding';
            RequestLog::create($log);
        }

        $findCar = Car::find($request->car_id);
        $findCar->car_price = $request->car_price;
        (isset($request->appointment_money_price)) ? $findCar->car_price_bank = $request->appointment_money_price : '';;
        $findCar->save();

        //อัพเดตสถานะของงาน
        $updateStatus = Working::find($request->working_id);
        $updateStatus->appointment_bank_type = $request->appointment_bank_type;
        $updateStatus->appointment_money = $request->appointment_money;
        $updateStatus->appointment_book = $request->appointment_book;
        $updateStatus->car_price = $request->car_price;
        $updateStatus->appointment_mkt = $request->appointment_mkt;
        $updateStatus->work_status = $request->work_status;
        $updateStatus->user_id = auth()->user()->id;
        $updateStatus->bank_id = $request->bank_id;
        $updateStatus->bank_branch_id = $request->bank_branch_id;
        $updateStatus->save();

        if (isset($request->appointment_money_price) && $request->appointment_money_price > 0) {
            $check_Income = Income::where([['working_id', $request->working_id], ['car_id', $updateStatus->car_id], ['detail', 'ค่าตัวรถจากธนาคาร']])->first();
            if (empty($check_Income)) {
                $Income =   Income::create([
                    'working_id' => $request->working_id,
                    'date' =>  $request->appointment_money_date,
                    'no' => $createAppointmentBank->id,
                    'shop' => 'ธนาคาร',
                    'detail' => 'ค่าตัวรถจากธนาคาร',
                    'type' => 1,
                    'type_bill' => 1,
                    'money' => $request->appointment_money_price,
                    'car_id' => $updateStatus->car_id,
                    'user_id' => auth()->user()->id,
                    'status_check' => 1,
                    'active' => 1,
                    'branch_id' =>  $updateStatus->branch_id,
                ]);
            } else {
                $check_Income->shop = 'ธนาคาร';
                $check_Income->detail = 'ค่าตัวรถจากธนาคาร';
                $check_Income->no = $createAppointmentBank->id;
                $check_Income->date = $request->appointment_money_date;
                $check_Income->money = $request->appointment_money_price;
                if ($request->appointment_money_price != $check_Income->money) {
                    $check_Income->user_id = auth()->user()->id;
                }
                $check_Income->save();
            }
        }

        if (isset($request->appointment_commission_bank) && $request->appointment_commission_bank > 0) {
            $check_Income = Income::where([['working_id', $request->working_id], ['car_id', $updateStatus->car_id], ['detail', 'ค่าคอมจากธนาคาร']])->first();
            if (empty($check_Income)) {
                $Income =   Income::create([
                    'working_id' => $request->working_id,
                    'date' =>  $request->appointment_money_date,
                    'no' => $createAppointmentBank->id,
                    'shop' => 'ธนาคาร',
                    'detail' => 'ค่าคอมจากธนาคาร',
                    'type' => 1,
                    'type_bill' => 1,
                    'money' => $request->appointment_commission_bank,
                    'car_id' => $updateStatus->car_id,
                    'user_id' => auth()->user()->id,
                    'status_check' => 1,
                    'active' => 1,
                    'branch_id' =>  $updateStatus->branch_id,
                ]);
            } else {
                $check_Income->shop = 'ธนาคาร';
                $check_Income->detail = 'ค่าคอมจากธนาคาร';
                $check_Income->no = $createAppointmentBank->id;
                $check_Income->date = $request->appointment_money_date;
                $check_Income->money = $request->appointment_commission_bank;
                if ($request->appointment_commission_bank != $check_Income->money) {
                    $check_Income->user_id = auth()->user()->id;
                }
                $check_Income->save();
            }
        }

        if (isset($request->appointment_mkt_price) && $request->appointment_mkt_price > 0) {
            $check_mkt = Outlay_cost::where([['working_id', $request->working_id], ['car_id', $updateStatus->car_id], ['detail', 'ค่าMarketing']])->first();
            if (empty($check_mkt)) {
                $appointment = Appointment::where('working_id', $updateStatus->id)->first();
                $Outlay_cost =   Outlay_cost::create([
                    'working_id' => $updateStatus->id,
                    'date' =>  $request->appointment_mkt_date,
                    'no' => '',
                    'shop' => $appointment->sale_name,
                    'file' => '',
                    'detail' => 'ค่าMarketing',
                    'type' => 1,
                    'type_bill' => 1,
                    'money' => $request->appointment_mkt_price,
                    'car_id' => $updateStatus->car_id,
                    'user_id' => auth()->user()->id,
                    'status_check' => 1,
                    'active' => 1,
                    'branch_id' =>  $updateStatus->branch_id,
                ]);
            }
        }


        $booking = Booking::where('working_id', $credentials['working_id'])->first();
        if (!empty($booking)) {
            $booking->credit_result = $credentials['credit'];
            $booking->customer_bath_date_signed = $credentials['appointment_bank_date'];
            $booking->save();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Appointment_bank  $appointment_bank
     * @return \Illuminate\Http\Response
     */
    public function show(Appointment_bank $appointment_bank)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Appointment_bank  $appointment_bank
     * @return \Illuminate\Http\Response
     */
    public function edit(Appointment_bank $appointment_bank)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment_bank  $appointment_bank
     * @return \Illuminate\Http\Response
     */
    public function update(Request $requestFormData, Appointment_bank $appointment_bank)
    {
        $dataInput = $requestFormData->all();
        $request =  json_decode($dataInput['formData']);

        // อัพเดตสถานะของงาน
        $queryWorking = Working::find($request->working_id);
        $queryWorking->appointment_bank_type = $request->appointment_bank_type;
        $queryWorking->appointment_date = $request->appointment_date;
        $queryWorking->car_price = $request->car_price;
        $queryWorking->appointment_money = $request->appointment_money;
        $queryWorking->appointment_money_date = $request->appointment_money_date;
        $queryWorking->appointment_transfer = $request->appointment_transfer;
        $queryWorking->appointment_transfer_date = $request->appointment_transfer_date;
        $queryWorking->appointment_book = $request->appointment_book;
        $queryWorking->appointment_book_date = $request->appointment_book_date;
        $queryWorking->bank_id = $request->bank_id;
        $queryWorking->bank_branch_id = $request->bank_branch_id;
        $queryWorking->user_id = auth()->user()->id;
        $queryWorking->appointment_mkt = $request->appointment_mkt;
        $queryWorking->appointment_mkt_date = $request->appointment_mkt_date;

        if ($queryWorking->work_status == 5 && $request->work_status == 7) {
            $sale = User::find($queryWorking->sale_id);
            $Branch_team = Branch_team::find($queryWorking->branch_team_id);
            $car = Car::find($queryWorking->car_id);

            $dataHistory['working_id'] = $request->working_id;
            $dataHistory['sale_name'] = $sale->first_name;
            $dataHistory['branch_name'] = $Branch_team->branch_team_name;
            $dataHistory['car_no'] = $car->car_no;

            $dataHistory['approve_date'] = $request->appointment_date;
            $dataHistory['middle_price'] = $request->middle_price;
            $dataHistory['percent'] = $request->car_price_persen;
            $dataHistory['finance_price'] = $request->finance_price;
            $dataHistory['down'] = $request->down;
            $dataHistory['car_price'] = $request->car_price;

            $dataHistory['note'] = "ลงจากระบบอัตโนมัติ";
            $dataHistory['request_status'] = "approve";
            $RequestHistory = RequestBankApprove::create($dataHistory);

            $file_id_card = $requestFormData->file('id_card');
            if ($file_id_card) {
                $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
                $saveImagePath_id_card = $this->path . $this->pathCurrentBankApprove;
                $file_id_card->move($saveImagePath_id_card, $filename_id_card);
                $RequestHistory->id_card = $filename_id_card;
            }

            $file_po = $requestFormData->file('po');
            if ($file_po) {
                $filename_po = $RequestHistory->id . '_po.' . $file_po->getClientOriginalExtension();
                $saveImagePath_po = $this->path . $this->pathCurrentBankApprove;
                $file_po->move($saveImagePath_po, $filename_po);
                $RequestHistory->po = $filename_po;
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
            $log['type'] = 'แบงค์อนุมัติ';
            $log['note'] = "ลงจากระบบอัตโนมัติ";
            $log['request_status'] = 'pedding';
            RequestLog::create($log);
        }


        if ($queryWorking->work_status == 4 && $request->work_status == 5) {
            $queryWorking->work_status = $request->work_status;
        }

        if ($queryWorking->work_status == 5 && $request->work_status == 7) {
            $queryWorking->work_status = $request->work_status;
        }

        if ($queryWorking->work_status == 8 && $request->work_status == 9) {
            $queryWorking->work_status = $request->work_status;
        }


        $queryWorking->save();

        $booking = Booking::where('working_id', $request->working_id)->first();
        if (!empty($booking)) {
            $booking->price_middle_close = $request->middle_price;
            $booking->persen_close = $request->car_price_persen;
            $booking->finance_price_close = $request->finance_price;

            $booking->price_close = $request->car_price;
            $booking->amount_slacken = $request->down;
            $booking->price_close_aggregate = $request->finance_price;

            $booking->credit_result = $request->credit;
            $booking->customer_bath_date_signed = $request->appointment_bank_date;

            $booking->save();
        }


        $appointment_bank_data_update =  (array)json_decode($dataInput['formData']);
        unset($appointment_bank_data_update['updated_at']);
        unset($appointment_bank_data_update['action']);
        unset($appointment_bank_data_update['work_status']);
        $appointment_bank->update($appointment_bank_data_update);
        // $appointment_bank->update($request->except(['updated_at', 'action', 'work_status']));

        $findCar = Car::find($request->car_id);
        $findCar->car_price = $request->car_price;
        $findCar->car_price_bank = $request->appointment_money_price;
        $findCar->save();


        $check_Income = Income::where([['working_id', $request->working_id], ['car_id', $queryWorking->car_id], ['detail', 'ค่าตัวรถจากธนาคาร']])->first();
        if (empty($check_Income)) {
            if (isset($request->appointment_money_price) && $request->appointment_money_price > 0) {
                $Income =   Income::create([
                    'working_id' => $request->working_id,
                    'date' =>   $request->appointment_money_date,
                    'no' => $appointment_bank->id,
                    'shop' => 'ธนาคาร',
                    'detail' => 'ค่าตัวรถจากธนาคาร',
                    'type' => 1,
                    'type_bill' => 1,
                    'money' => $request->appointment_money_price,
                    'car_id' => $queryWorking->car_id,
                    'user_id' => auth()->user()->id,
                    'status_check' => 1,
                    'active' => 1,
                    'branch_id' =>  $queryWorking->branch_id,
                ]);
            }
        } else {
            if (isset($request->appointment_money_price) && $request->appointment_money_price > 0) {
                $check_Income->shop = 'ธนาคาร';
                $check_Income->detail = 'ค่าตัวรถจากธนาคาร';
                $check_Income->no = $appointment_bank->id;
                $check_Income->date = $request->appointment_money_date;
                $check_Income->money = $request->appointment_money_price;
                if ($request->appointment_money_price != $check_Income->money) {
                    $check_Income->user_id = auth()->user()->id;
                }
                $check_Income->active = 1;
                $check_Income->save();
            } else {
                $check_Income->delete();
            }
        }

        $check_Income_commission_bank = Income::where([['working_id', $request->working_id], ['car_id', $queryWorking->car_id], ['detail', 'ค่าคอมจากธนาคาร']])->first();
        if (empty($check_Income_commission_bank)) {
            if (isset($request->appointment_commission_bank) && $request->appointment_commission_bank > 0) {
                $Income =   Income::create([
                    'working_id' => $request->working_id,
                    'date' =>   $request->appointment_money_date,
                    'no' => $appointment_bank->id,
                    'shop' => 'ธนาคาร',
                    'detail' => 'ค่าคอมจากธนาคาร',
                    'type' => 1,
                    'type_bill' => 1,
                    'money' => $request->appointment_commission_bank,
                    'car_id' => $queryWorking->car_id,
                    'user_id' => auth()->user()->id,
                    'status_check' => 1,
                    'active' => 1,
                    'branch_id' =>  $queryWorking->branch_id,
                ]);
            }
        } else {
            if (isset($request->appointment_commission_bank) && $request->appointment_commission_bank > 0) {
                $check_Income_commission_bank->shop = 'ธนาคาร';
                $check_Income_commission_bank->detail = 'ค่าคอมจากธนาคาร';
                $check_Income_commission_bank->no = $appointment_bank->id;
                $check_Income_commission_bank->date = $request->appointment_money_date;
                $check_Income_commission_bank->money = $request->appointment_commission_bank;
                if ($request->appointment_commission_bank != $check_Income_commission_bank->money) {
                    $check_Income->user_id = auth()->user()->id;
                }
                $check_Income_commission_bank->active = 1;
                $check_Income_commission_bank->save();
            } else {
                $check_Income_commission_bank->delete();
            }
        }


        $check_mkt = Outlay_cost::where([['working_id', $request->working_id], ['car_id', $queryWorking->car_id], ['detail', 'ค่าMarketing']])->first();
        if (empty($check_mkt)) {
            if (isset($request->appointment_mkt_price) && $request->appointment_mkt_price > 0) {
                $appointment = Appointment::where('working_id', $queryWorking->id)->first();
                if ($appointment) {
                    $mtk_name = $appointment->sale_name;
                } else {
                    $mtk_name = 'N/A';
                }
                $Outlay_cost =   Outlay_cost::create([
                    'working_id' => $queryWorking->id,
                    'date' =>  $request->appointment_mkt_date,
                    'no' => '',
                    'shop' => $mtk_name,
                    'file' => '',
                    'detail' => 'ค่าMarketing',
                    'type' => 1,
                    'type_bill' => 1,
                    'money' => $request->appointment_mkt_price,
                    'car_id' => $queryWorking->car_id,
                    'status_check' => 1,
                    'active' => 1,
                    'branch_id' =>  $queryWorking->branch_id,
                    'user_id' => auth()->user()->id,
                ]);
            }
        } else {
            if (isset($request->appointment_mkt_price) && $request->appointment_mkt_price > 0) {
                $Outlay_cost =  Outlay_cost::find($check_mkt->id);
                $Outlay_cost->date = $request->appointment_mkt_date;
                $Outlay_cost->money = $request->appointment_mkt_price;
                $Outlay_cost->save();
            } else {
                $Outlay_cost =  Outlay_cost::find($check_mkt->id);
                $Outlay_cost->date = null;
                $Outlay_cost->money = 0;
                $Outlay_cost->save();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Appointment_bank  $appointment_bank
     * @return \Illuminate\Http\Response
     */
    public function destroy(Appointment_bank $appointment_bank)
    {
        //
    }


    public function checkAppointmentBank($idWork)
    {
        $checkBooking = Appointment_bank::where('working_id', $idWork)->first();

        if (empty($checkBooking)) {
            $query = DB::table('workings')
                ->where('workings.id', $idWork)
                ->first();
            $query->car_price = null;
            $query->car_price_persen = null;
            $query->working_id = $idWork;
            $query->action = "add";
        } else {
            $query = DB::table('appointment_banks')
                ->where('working_id', $idWork)
                ->first();
            $query->action = "edit";
        }
        return response()->json($query);
    }
}
