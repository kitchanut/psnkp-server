<?php

namespace App\Http\Controllers;

use App\Models\Amphure;
use App\Models\Booking;
use App\Models\Branch_team;
use App\Models\Car;
use App\Models\District;
use App\Models\Financial;
use App\Models\Income;
use App\Models\Province;
use App\Models\RequestLog;
use App\Models\RequestMoney;
use App\Models\User;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    protected $pathCurrent = 'request_money/';

    public function index()
    {
        //
    }

    public  function indexTime(Request $request)
    {

        // $branch_id = $request->input('branch_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $output = Financial::where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])->get();

        return response()->json($output);
    }

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

        $credentials['working_id'] = $request->working_id;
        $credentials['customer_id'] = $request->customer_id;
        $credentials['payment_type'] = $request->payment_type;
        if ($request->payment_type == 99) {
            $credentials['details'] = $request->details;
        }
        $credentials['bath'] = $request->bath;
        $credentials['bath_string'] = $request->bath_string;
        $credentials['customer_name'] = $request->customer_name;
        $credentials['customer_address'] = $request->customer_address;
        $credentials['customer_tel'] = $request->customer_tel;
        $credentials['car_no'] = $request->car_no;
        $credentials['car_model_name'] = $request->car_model_name;
        $credentials['car_no_engine'] = $request->car_no_engine;
        $credentials['car_series_name'] = $request->car_series_name;
        $credentials['car_no_body'] = $request->car_no_body;
        $credentials['car_year'] = $request->car_year;
        $credentials['car_regis'] = $request->car_regis;
        $credentials['car_mileage'] = $request->car_mileage;
        $credentials['obligation'] = $request->obligation;
        $credentials['owner'] = $request->owner;
        $credentials['occupy'] = $request->occupy;
        $credentials['amphure_id'] = $request->amphure_id;
        $credentials['district_id'] = $request->district_id;
        $credentials['province_id'] = $request->province_id;
        $credentials['color_name'] = $request->color_name;
        $credentials['zip_code'] = $request->zip_code;
        $credentials['note'] = $request->note;
        $createFinancial = Financial::create($credentials);

        //อัพเดตสถานะของงาน
        $working = Working::find($request->working_id);
        $working->user_id = $requestFormData->user()->id;

        if ($createFinancial->payment_type == 1 && $working->work_status == 2) {
            $working->work_status = 3;
        }
        $working->save();

        if ($createFinancial->payment_type == 1) {
            $detail = 'เงินจอง';
        } else if ($createFinancial->payment_type == 2) {
            $detail = 'เงินดาวน์';
        } else if ($createFinancial->payment_type == 3) {
            $detail = 'ซื้อเงินสด';
        } else if ($createFinancial->payment_type == 4) {
            $detail = 'ค่างวดล่วงหน้า';
        } else if ($createFinancial->payment_type == 5) {
            $detail = 'สมาร์ทชัว';
        } else if ($createFinancial->payment_type == 6) {
            $detail = 'ประกันอื่นๆ';
        } else if ($createFinancial->payment_type == 7) {
            $detail = 'คืนค่างวดบริษัท';
        } else if ($createFinancial->payment_type == 99) {
            $detail = $request->details;
        } else {
            $detail = 'ใบสำคัญรับเงิน';
        }

        if (!empty($credentials['bath']) || $credentials['bath'] != 0) {
            Income::create([
                'main_type' => 'ใบสำคัญรับเงิน',
                'working_id' => $working->id,
                'date' => $createFinancial->created_at,
                'shop' => $working->customer_name,
                'no' => $createFinancial->id,
                'detail' => $detail,
                'type' => 1,
                'type_bill' => 1,
                'money' => $credentials['bath'],
                'car_id' => $working->car_id,
                'user_id' => $working->sale_id,
                'status_check' => 1,
                'active' => 1,
                'branch_id' =>  $working->branch_id,
            ]);
        }


        $Car = Car::find($working->car_id);
        $Car->income = Income::where([['car_id', $Car->id], ['active', 1], ['status_check', 1]])->sum('money');
        $Car->save();

        $Booking = Booking::where('working_id', $request->working_id)->first();
        $Booking->customer_bath_pledge = Financial::where([['working_id', $request->working_id], ['payment_type', 1]])->sum('bath');
        $Booking->save();

        // บันทึกลงแจ้งลงงาน
        $sale = User::find($working->sale_id);
        $Branch_team = Branch_team::find($working->branch_team_id);
        $car = Car::find($working->car_id);

        $dataHistory['working_id'] = $request->working_id;
        $dataHistory['sale_name'] = $sale->first_name;
        $dataHistory['branch_name'] = $Branch_team->branch_team_name;
        $dataHistory['car_no'] = $car->car_no;
        $dataHistory['type'] = $detail;
        $dataHistory['amount'] = $credentials['bath'];
        $dataHistory['note'] = "ลงจากระบบอัตโนมัติ " . $credentials['note'];
        $dataHistory['request_status'] = "approve";
        $RequestHistory = RequestMoney::create($dataHistory);

        $file_id_card = $requestFormData->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestHistory->id_card = $filename_id_card;
        }

        $file_slip = $requestFormData->file('slip');
        if ($file_slip) {
            $filename_slip = $RequestHistory->id . '_slip.' . $file_slip->getClientOriginalExtension();
            $saveImagePath_slip = $this->path . $this->pathCurrent;
            $file_slip->move($saveImagePath_slip, $filename_slip);
            $RequestHistory->slip = $filename_slip;
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
        $log['type'] = 'การรับเงิน';
        $log['note'] = $dataHistory['note'];
        $log['request_status'] = 'pedding';
        RequestLog::create($log);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Financial  $financial
     * @return \Illuminate\Http\Response
     */
    public function show(Financial $financial)
    {
        $financial->action = "edit";
        return response()->json($financial);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Financial  $financial
     * @return \Illuminate\Http\Response
     */
    public function edit(Financial $financial)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Financial  $financial
     * @return \Illuminate\Http\Response
     */
    public function update(Request $requestFormData, Financial $financial)
    {
        $dataInput = $requestFormData->all();
        $request =  json_decode($dataInput['formData']);
        $credentials = (array) $request;
        unset($credentials['action']);
        // $credentials = $request->except(['action']);

        $financial->update($credentials);
        $working = Working::find($request->working_id);

        if ($financial->payment_type == 1) {
            $detail = 'เงินจอง';
        } else if ($financial->payment_type == 2) {
            $detail = 'เงินดาวน์';
        } else if ($financial->payment_type == 3) {
            $detail = 'ซื้อเงินสด';
        } else if ($financial->payment_type == 4) {
            $detail = 'ค่างวดล่วงหน้า';
        } else if ($financial->payment_type == 5) {
            $detail = 'สมาร์ทชัว';
        } else if ($financial->payment_type == 6) {
            $detail = 'ประกันอื่นๆ';
        } else if ($financial->payment_type == 7) {
            $detail = 'คืนค่างวดบริษัท';
        } else if ($financial->payment_type == 99) {
            $detail = $request->details;
        } else {
            $detail = 'ใบสำคัญรับเงิน';
        }

        if (!empty($financial->bath) || $financial->bath != 0) {
            $check_Income = Income::where([['working_id', $request->working_id], ['no', $financial->id]])->first();
            if (empty($check_Income)) {
                Income::create([
                    'main_type' => 'ใบสำคัญรับเงิน',
                    'working_id' => $working->id,
                    'date' => $financial->created_at,
                    'no' => $financial->id,
                    'shop' => $working->customer_name,
                    'detail' => $detail,
                    'type' => 1,
                    'type_bill' => 1,
                    'money' => $financial->bath,
                    'car_id' => $working->car_id,
                    'user_id' => $working->sale_id,
                    'status_check' => 1,
                    'active' => 1,
                    'branch_id' =>  $working->branch_id,
                ]);
            } else {
                $check_Income->main_type = 'ใบสำคัญรับเงิน';
                $check_Income->date = $financial->created_at;
                $check_Income->detail = $detail;
                $check_Income->money = $request->bath;
                $check_Income->save();
            }
        }

        $Car = Car::find($working->car_id);
        $Car->income = Income::where([['car_id', $Car->id], ['active', 1], ['status_check', 1]])->sum('money');
        $Car->save();

        $Booking = Booking::where('working_id', $request->working_id)->first();
        $Booking->customer_bath_pledge = Financial::where([['working_id', $request->working_id], ['payment_type', 1]])->sum('bath');
        $Booking->save();

        // บันทึกลงแจ้งลงงาน
        $sale = User::find($working->sale_id);
        $Branch_team = Branch_team::find($working->branch_team_id);
        $car = Car::find($working->car_id);

        $dataHistory['working_id'] = $request->working_id;
        $dataHistory['sale_name'] = $sale->first_name;
        $dataHistory['branch_name'] = $Branch_team->branch_team_name;
        $dataHistory['car_no'] = $car->car_no;
        $dataHistory['type'] = $detail;
        $dataHistory['amount'] = $credentials['bath'];
        $dataHistory['note'] = "แก้ไขการรับเงิน " . $credentials['note'];
        $dataHistory['request_status'] = "approve";
        $RequestHistory = RequestMoney::create($dataHistory);

        $file_id_card = $requestFormData->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestHistory->id_card = $filename_id_card;
        }

        $file_slip = $requestFormData->file('slip');
        if ($file_slip) {
            $filename_slip = $RequestHistory->id . '_slip.' . $file_slip->getClientOriginalExtension();
            $saveImagePath_slip = $this->path . $this->pathCurrent;
            $file_slip->move($saveImagePath_slip, $filename_slip);
            $RequestHistory->slip = $filename_slip;
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
        $log['type'] = 'การรับเงิน';
        $log['note'] = $dataHistory['note'];
        $log['request_status'] = 'pedding';
        RequestLog::create($log);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Financial  $financial
     * @return \Illuminate\Http\Response
     */
    public function destroy(Financial $financial)
    {
        //
    }
    public function printFinancial($idFinancial)
    {
        $Financial = Financial::find($idFinancial);

        $Financial->Amphure =  Amphure::find($Financial->amphure_id);
        $Financial->District =  District::find($Financial->district_id);
        $Financial->Province =  Province::find($Financial->province_id);
        return response()->json($Financial);
    }
    public function checkFinancial($idWork, $payment_type)
    {
        $checkFinancial = Financial::where([['working_id', $idWork], ['payment_type', $payment_type]])->first();

        if (empty($checkFinancial)) {
            $query = DB::table('workings')
                ->join('cars', 'workings.car_id', '=', 'cars.id')
                ->join('colors', 'cars.color_id', '=', 'colors.id')
                ->join('car_types', 'cars.car_types_id', '=', 'car_types.id')
                ->join('car_models', 'cars.car_models_id', '=', 'car_models.id')
                ->join('car_series', 'cars.car_serie_id', '=', 'car_series.id')
                ->join('car_serie_subs', 'cars.car_serie_sub_id', '=', 'car_serie_subs.id')
                ->join('branches', 'cars.branch_id', '=', 'branches.id')
                ->join('customers', 'workings.customer_id', '=', 'customers.id')
                ->join('customer_details', 'customers.id', '=', 'customer_details.customer_id')
                ->where('workings.id', $idWork)
                ->first();
            $query->working_id = $idWork;
            $query->payment_type = (string)$payment_type;
            $query->action = "add";
        } else {
            $query = DB::table('financials')
                ->where([['working_id', $idWork], ['payment_type', $payment_type]])
                ->first();
            $query->action = "edit";
        }
        return response()->json($query);
    }

    public function addFinancial($idWork)
    {
        $payment_type = 2;
        $query = DB::table('workings')
            ->join('cars', 'workings.car_id', '=', 'cars.id')
            ->join('colors', 'cars.color_id', '=', 'colors.id')
            ->join('car_types', 'cars.car_types_id', '=', 'car_types.id')
            ->join('car_models', 'cars.car_models_id', '=', 'car_models.id')
            ->join('car_series', 'cars.car_serie_id', '=', 'car_series.id')
            ->join('car_serie_subs', 'cars.car_serie_sub_id', '=', 'car_serie_subs.id')
            ->join('branches', 'cars.branch_id', '=', 'branches.id')
            ->join('customers', 'workings.customer_id', '=', 'customers.id')
            ->join('customer_details', 'customers.id', '=', 'customer_details.customer_id')
            ->where('workings.id', $idWork)
            ->first();
        $query->working_id = $idWork;
        $query->payment_type = null;
        $query->action = "add";

        return response()->json($query);
    }

    public function editFinancial($idWork, $payment_type)
    {
        $query = DB::table('financials')
            ->where([['working_id', $idWork], ['payment_type', $payment_type]])
            ->first();
        $query->action = "edit";

        return response()->json($query);
    }

    public function allFinancialonWork($idWork)
    {
        $checkFinancial = Financial::where([['working_id', $idWork]])->get();
        return response()->json($checkFinancial);
    }
}
