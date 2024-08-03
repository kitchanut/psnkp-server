<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Bank_branch;
use App\Models\Branch;
use App\Models\Branch_team;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Customer_detail;
use App\Models\Financial;
use App\Models\RequestCancle;
use App\Models\RequestChangeCar;
use App\Models\RequestChangeCustomer;
use App\Models\RequestLog;
use App\Models\Working;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class WorkingController extends Controller
{
    protected $pathCurrent = 'request_change_customer/';
    protected $pathCurrentChangeCar = 'request_changeCar/';
    protected $pathCurrentCancle = 'request_cancle/';

    public function index()
    {
        $dataWorking = Working::with(['cars', 'sale', 'branch', 'team', 'branch_team'])
            ->where([['work_status', '<', 11], ['status_del', 1]])
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json($dataWorking);
    }

    public function working_allData()
    {
        $dataWorking = Working::with(['cars'])
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json($dataWorking);
    }

    public function activeWorkingID()
    {
        $output = Working::select('id', 'car_id', 'customer_id')
            ->with('customer')
            ->with(['cars' => function ($query) {
                $query->select('id', 'car_no');
            }])
            ->where([['work_status', '<', 11], ['status_del', 1]])
            ->orderBy('id', 'DESC')
            ->get();
        return response()->json($output);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        $customer_id = $request->only('customer_id');

        if ($customer_id['customer_id']) {

            $customer = Customer::find($customer_id['customer_id']);

            $credentials = $request->except(['id', 'sale_code']);
            $credentials['user_id'] = $request->user()->id;
            $credentials['customer_name'] = $customer->customer_name;
            $credentials['customer_tel'] = $customer->customer_tel;
        } else {
            $credentials = $request->except(['id', 'sale_code', 'customer_id']);
            $credentials['user_id'] = 0;
            $credentials['customer_name'] = $request->customer_name;
            $credentials['customer_tel'] = $request->customer_tel;
        }

        $checkUser = User::find($request->sale_id);
        if ($checkUser == null) {
            $idUser = null;
            $idTeam = null;
            $branch_id = null;
            $branch_team_id = null;
        } else {
            $idUser = $checkUser->id;
            $idTeam = $checkUser->user_team_id;
            $branch_id = $checkUser->branch_id;
            $checkBranch =  Branch::find($branch_id);
            $branch_team_id = $checkBranch->branch_team_id;
        }

        $queryCar = Car::find($request->car_id);
        $newBooking = (int)  $queryCar->car_booking + 1;
        $queryCar->car_booking = $newBooking;
        $queryCar->save();

        $workingCrate = Working::create($credentials  + ['sale_id' => $idUser] + ['user_team_id' => $idTeam] + ['branch_id' => $branch_id] + ['branch_team_id' => $branch_team_id]);
    }

    public function show(Working $working)
    {
        return response()->json($working);
    }

    public function edit(Working $working)
    {
        //
    }

    public function update(Request $request, Working $working)
    {
        $dataInput = $request->all();
        $formData =  json_decode($dataInput['formData']);
        $dataUpdate =  (array)json_decode($dataInput['formData']);
        $queryCheck = Working::find($formData->id);

        // เปลี่ยนคันจอง
        if ($queryCheck->car_id != $formData->car_id) {

            $formDataChangCar =  json_decode($dataInput['formDataChangCar']);

            $sale = User::find($formData->sale_id);
            $Branch_team = Branch_team::find($queryCheck->branch_team_id);
            $car_old = Car::find($queryCheck->car_id);
            $car_new = Car::find($formData->car_id);

            $dataHistory['working_id'] = $formData->id;
            $dataHistory['sale_name'] = $sale->first_name;
            $dataHistory['branch_name'] = $Branch_team->branch_team_name;
            $dataHistory['car_no_old'] = $car_old->car_no;
            $dataHistory['car_no_new'] = $car_new->car_no;
            $dataHistory['sign_type'] = $formDataChangCar->sign_type;

            if ($formDataChangCar->sign_type == 'เซนต์ใหม่') {
                $dataHistory['sign_date'] = $formDataChangCar->sign_date;
                $dataHistory['bank_name'] = $formDataChangCar->bank_name;
                $dataHistory['bank_branch_name'] = $formDataChangCar->bank_branch_name;
                $dataHistory['mtk_name'] = $formDataChangCar->mtk_name;
                $dataHistory['mtk_tel'] = $formDataChangCar->mtk_tel;
                $dataHistory['credit'] = $formDataChangCar->credit;
                $dataHistory['document'] = $formDataChangCar->document;
                if (isset($formDataChangCar->document_list)) {
                    $dataHistory['document_list'] = $formDataChangCar->document_list;
                }
            }

            $dataHistory['id_card'] = 'no_img.png';
            $dataHistory['booking_sheet'] = 'no_img.png';
            $dataHistory['note'] = "ลงจากระบบอัตโนมัติ";
            $dataHistory['request_status'] = "approve";
            $RequestHistory = RequestChangeCar::create($dataHistory);

            $file_id_card = $request->file('id_card');
            if ($file_id_card) {
                $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
                $saveImagePath_id_card = $this->path . $this->pathCurrentChangeCar;
                $file_id_card->move($saveImagePath_id_card, $filename_id_card);
                $RequestHistory->id_card = $filename_id_card;
            }

            $file_sale_sheet = $request->file('sale_sheet');
            if ($file_sale_sheet) {
                $filename_sale_sheet = $RequestHistory->id . '_booking_sheet.' . $file_sale_sheet->getClientOriginalExtension();
                $saveImagePath_sale_sheet = $this->path . $this->pathCurrentChangeCar;
                $file_sale_sheet->move($saveImagePath_sale_sheet, $filename_sale_sheet);
                $RequestHistory->booking_sheet = $filename_sale_sheet;
            }
            $RequestHistory->save();

            if (!is_null(auth()->user())) {
                $log['user_id'] = auth()->user()->id;
            }
            $log['working_id'] = $RequestHistory['working_id'];
            $log['ref_id'] = $RequestHistory['id'];
            $log['sale_name'] = $RequestHistory['sale_name'];
            $log['branch_name'] = $RequestHistory['branch_name'];
            $log['car_no'] = $car_new->car_no;
            $log['car_no_old'] = $car_old->car_no;
            $log['type'] = 'เปลี่ยนจอง';
            $log['note'] = "ลงจากระบบอัตโนมัติ";
            $log['request_status'] = 'pedding';
            RequestLog::create($log);

            $queryCar =  DB::table('cars')
                ->join('car_types', 'cars.car_types_id', '=', 'car_types.id')
                ->join('car_models', 'cars.car_models_id', '=', 'car_models.id')
                ->join('car_series', 'cars.car_serie_id', '=', 'car_series.id')
                ->join('colors', 'cars.color_id', '=', 'colors.id')
                ->join('car_serie_subs', 'cars.car_serie_sub_id', '=', 'car_serie_subs.id')
                ->join('branches', 'cars.branch_id', '=', 'branches.id')
                ->where('cars.id', $formData->car_id)
                ->first();

            $checkBooking =  DB::table('bookings')->where('working_id', $formData->id)->first();
            if ($checkBooking != null) {
                DB::table('bookings')
                    ->where('working_id', $formData->id)
                    ->update([
                        'car_no' => $queryCar->car_no,
                        'car_type_name' => $queryCar->car_type_name,
                        'car_model_name' => $queryCar->car_model_name,
                        'car_series_name' => $queryCar->car_series_name,
                        'car_serie_sub_name' => $queryCar->car_serie_sub_name,
                        'car_year' => $queryCar->car_year,
                        'color_name' => $queryCar->color_name,
                        'car_regis' => $queryCar->car_regis,
                        'car_no_engine' => $queryCar->car_no_engine,
                        'car_mileage' => $queryCar->car_mileage,
                        'car_no_body' => $queryCar->car_no_body,
                    ]);
            }

            DB::table('financials')
                ->where('working_id', $formData->id)
                ->update([
                    'car_no' => $queryCar->car_no,
                    'car_model_name' => $queryCar->car_model_name,
                    'car_series_name' => $queryCar->car_series_name,
                    'car_year' => $queryCar->car_year,
                    'color_name' => $queryCar->color_name,
                    'car_regis' => $queryCar->car_regis,
                    'car_no_engine' => $queryCar->car_no_engine,
                    'car_mileage' => $queryCar->car_mileage,
                    'car_no_body' => $queryCar->car_no_body,
                ]);

            DB::table('incomes')
                ->where('working_id', $formData->id)
                ->update([
                    'car_id' => $formData->car_id
                ]);


            $checkContracts =  DB::table('contracts')
                ->where('working_id', $formData->id)->first();
            if ($checkContracts != null) {
                DB::table('contracts')
                    ->where('working_id', $formData->id)
                    ->update([
                        'car_id' => $queryCar->id,
                        'car_model_name' => $queryCar->car_model_name,
                        'car_year' => $queryCar->car_year,
                        'color_name' => $queryCar->color_name,
                        'car_regis' => $queryCar->car_regis,
                        'car_no_engine' => $queryCar->car_no_engine,
                        'car_no_body' => $queryCar->car_no_body,
                    ]);
            }
        }

        // เปลี่ยนคนจอง
        if ($queryCheck->customer_id != $formData->customer_id) {

            $sale = User::find($formData->sale_id);
            $Branch_team = Branch_team::find($queryCheck->branch_team_id);
            $car = Car::find($formData->car_id);
            $customer_old = Customer::find($queryCheck->customer_id);
            $customer_new = Customer::find($formData->customer_id);
            $customer_job = 'N/A';

            // เปลี่ยนชื่อในตาราง working
            $dataUpdate['customer_name'] = $customer_new->customer_name;

            if ($customer_new->customer_job == 1) {
                $customer_job = 'ข้าราชการ';
            } elseif ($customer_new->customer_job == 2) {
                $customer_job = 'พนักงานเอกชน';
            } elseif ($customer_new->customer_job == 3) {
                $customer_job = 'เกษตรกร';
            } elseif ($customer_new->customer_job == 4) {
                $customer_job = 'ค้าขาย';
            } elseif ($customer_new->customer_job == 5) {
                $customer_job = $customer_new->customer_job_list;
            }

            if ($customer_job == null) {
                $customer_job = 'N/A';
            }

            $dataHistoryCustomer['working_id'] = $formData->id;
            $dataHistoryCustomer['sale_name'] = $sale ? $sale->first_name : 'N/A';
            $dataHistoryCustomer['branch_name'] = $Branch_team ? $Branch_team->branch_team_name : 'N/A';
            $dataHistoryCustomer['car_no'] = $car->car_no;
            $dataHistoryCustomer['customer_old'] = $customer_old ? $customer_old->customer_name : 'N/A';
            $dataHistoryCustomer['customer_new'] = $customer_new->customer_name;
            $dataHistoryCustomer['customer_job'] = $customer_job;
            $dataHistoryCustomer['id_card'] = 'no_img.png';
            $dataHistoryCustomer['sale_sheet'] = 'no_img.png';
            $dataHistoryCustomer['note'] = "ลงจากระบบอัตโนมัติ";
            $dataHistoryCustomer['request_status'] = "approve";
            $RequestHistoryCustomer = RequestChangeCustomer::create($dataHistoryCustomer);

            $file_id_card_customer = $request->file('id_card');
            if ($file_id_card_customer) {
                $filename_id_card_customer = $RequestHistoryCustomer->id . '_id_card.' . $file_id_card_customer->getClientOriginalExtension();
                $saveImagePath_id_card_customer = $this->path . $this->pathCurrent;
                if ($queryCheck->car_id == $formData->car_id) {
                    $file_id_card_customer->move($saveImagePath_id_card_customer, $filename_id_card_customer);
                } else {
                    copy($saveImagePath_id_card . $filename_id_card, $saveImagePath_id_card_customer . $filename_id_card_customer);
                }

                $RequestHistoryCustomer->id_card = $filename_id_card_customer;
            }

            $file_sale_sheet_customer = $request->file('sale_sheet');
            if ($file_sale_sheet_customer) {
                $filename_sale_sheet_customer = $RequestHistoryCustomer->id . '_sale_sheet.' . $file_sale_sheet_customer->getClientOriginalExtension();
                $saveImagePath_sale_sheet_customer = $this->path . $this->pathCurrent;

                if ($queryCheck->car_id == $formData->car_id) {
                    $file_sale_sheet_customer->move($saveImagePath_sale_sheet_customer, $filename_sale_sheet_customer);
                } else {
                    copy($saveImagePath_sale_sheet . $filename_sale_sheet, $saveImagePath_sale_sheet_customer . $filename_sale_sheet_customer);
                }
                $RequestHistoryCustomer->sale_sheet = $filename_sale_sheet_customer;
            }
            $RequestHistoryCustomer->save();

            if (!is_null(auth()->user())) {
                $log['user_id'] = auth()->user()->id;
            }
            $log['working_id'] = $RequestHistoryCustomer['working_id'];
            $log['ref_id'] = $RequestHistoryCustomer['id'];
            $log['sale_name'] = $RequestHistoryCustomer['sale_name'];
            $log['branch_name'] = $RequestHistoryCustomer['branch_name'];
            $log['car_no'] = $RequestHistoryCustomer['car_no'];
            $log['type'] = 'เปลี่ยนคนจอง';
            $log['note'] = "ลงจากระบบอัตโนมัติ";
            $log['request_status'] = 'pedding';
            RequestLog::create($log);

            $checkBooking =  DB::table('bookings')->where('working_id', $formData->id)->first();
            if ($checkBooking != null) {
                DB::table('bookings')
                    ->where('working_id', $formData->id)
                    ->update([
                        'customer_name' => $customer_new->customer_name,
                        'customer_birthday_year' => $customer_new->customer_birthday_year,
                        'customer_tel' => $customer_new->customer_tel,
                        'customer_job' => $customer_new->customer_job,
                        'customer_job_list' => $customer_new->customer_job_list,
                    ]);

                $checkCustomerDetail = Customer_detail::find($formData->customer_id);
                if ($checkCustomerDetail) {
                    DB::table('bookings')
                        ->where('working_id', $formData->id)
                        ->update([
                            'customer_facebook' => $checkCustomerDetail->customer_facebook,
                            'customer_line' => $checkCustomerDetail->customer_line,
                            'customer_email' => $checkCustomerDetail->customer_email,

                            'customer_address' => $checkCustomerDetail->customer_address,
                            'province_id' => $checkCustomerDetail->province_id,
                            'amphure_id' => $checkCustomerDetail->amphure_id,
                            'district_id' => $checkCustomerDetail->district_id,
                            'zip_code' => $checkCustomerDetail->zip_code,
                            'customer_address_current' => $checkCustomerDetail->customer_address_current,
                            'province_id_current' => $checkCustomerDetail->province_id_current,
                            'amphure_id_current' => $checkCustomerDetail->amphure_id_current,
                            'district_id_current' => $checkCustomerDetail->district_id_current,
                            'zip_code_current' => $checkCustomerDetail->zip_code_current,

                            'customer_work_age' => $checkCustomerDetail->customer_work_age,
                            'customer_income' => $checkCustomerDetail->customer_income,
                            'customer_receive_money_type' => $checkCustomerDetail->customer_receive_money_type,
                            'customer_slip_money' => $checkCustomerDetail->customer_slip_money,
                            'customer_receive_evidence' => $checkCustomerDetail->customer_receive_evidence,

                            'customer_land_all' => $checkCustomerDetail->customer_land_all,
                            'customer_land_domin' => $checkCustomerDetail->customer_land_domin,
                            'customer_land_hire' => $checkCustomerDetail->customer_land_hire,

                            'customer_value_home' => $checkCustomerDetail->customer_value_home,
                            'customer_value_car' => $checkCustomerDetail->customer_value_car,
                            'customer_value_ete' => $checkCustomerDetail->customer_value_ete,

                            'customer_installment_history' => $checkCustomerDetail->customer_installment_history,
                            'customer_installment_slow' => $checkCustomerDetail->customer_installment_slow,
                            'customer_installment_nmt' => $checkCustomerDetail->customer_installment_nmt,
                            'customer_installment_check' => $checkCustomerDetail->customer_installment_check,

                            'customer_refer_tel' => $checkCustomerDetail->customer_refer_tel,
                            'customer_refer_related' => $checkCustomerDetail->customer_refer_related,
                            'customer_refer_job' => $checkCustomerDetail->customer_refer_job,
                            'customer_refer_job_list' => $checkCustomerDetail->customer_refer_job_list,
                            'customer_refer_address' => $checkCustomerDetail->customer_refer_address,
                            'province_id_ref' => $checkCustomerDetail->province_id_ref,
                            'amphure_id_ref' => $checkCustomerDetail->amphure_id_ref,
                            'district_id_ref' => $checkCustomerDetail->district_id_ref,
                            'zip_code_ref' => $checkCustomerDetail->zip_code_ref,
                        ]);
                }
            }
        }

        $queryCheck->update($dataUpdate);
    }

    public function destroy(Working $working)
    {
        $working->status_del = 0;
        $working->save();
    }

    public function work_cancel(Request $requestFormData)
    {
        $dataInput = $requestFormData->all();
        $request =  json_decode($dataInput['formData']);

        $working = Working::find($request->id);

        // ถ้ามีการยกเลิก
        if ($working->status_del == 1) {

            $sale = User::find($working->sale_id);
            $Branch_team = Branch_team::find($working->branch_team_id);
            $car = Car::find($working->car_id);

            $dataHistory['working_id'] = $request->id;
            $dataHistory['sale_name'] = $sale ? $sale->first_name : 'N/A';
            $dataHistory['branch_name'] = $Branch_team ? $Branch_team->branch_team_name : 'N/A';
            $dataHistory['car_no'] = $car->car_no;
            $dataHistory['id_card'] = 'no_img.png';
            $dataHistory['note'] = "ลงจากระบบอัตโนมัติ - " . $request->cancel_list;
            $dataHistory['request_status'] = "approve";
            $RequestHistory = RequestCancle::create($dataHistory);

            $file_id_card = $requestFormData->file('id_card');
            if ($file_id_card) {
                $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
                $saveImagePath_id_card = $this->path . $this->pathCurrentCancle;
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
            $log['type'] = 'ยกเลิกจอง';
            $log['note'] = "แก้ไข (ลงจากระบบอัตโนมัติ)";
            $log['request_status'] = 'pedding';
            RequestLog::create($log);
        }

        $working->status_del = !$working->status_del;
        $working->user_id = $requestFormData->user()->id;
        $working->cancel_list = $request->cancel_list;
        $working->save();

        $checkOtherBooking = Working::where([['car_id', $request->car_id], ['status_del', 1]])->count();

        $queryCar = Car::find($request->car_id);
        $newBooking = (int)  $queryCar->car_booking - 1;
        $queryCar->car_booking = $newBooking;
        if ($checkOtherBooking == 0) {
            $queryCar->booking_status = 1;
        }
        $queryCar->save();
    }



    public function updateStatusWorking(Request $request, $workingID)
    {

        $updateStatusWorking = Working::find($workingID);
        $updateStatusWorking->work_status = $request->input('work_status');
        $updateStatusWorking->user_id = $request->user()->id;
        $updateStatusWorking->save();
    }

    public function updatePedding(Request $request, $workingID)
    {
        $updateStatusWorking = Working::find($workingID);
        $updateStatusWorking->pedding = $request->input('pedding');
        $updateStatusWorking->save();
    }


    public function work_where_id($id)
    {
        return response()->json(Working::where('id', 'like', $id . '%')->get());
    }

    public function workWhereClose(Request $request)
    {

        $search = $request->input('search');
        $work_status = $request->input('work_status');

        $data = Working::with([
            'cars', 'cars.car_models', 'cars.car_series', 'cars.car_serie_sub',
            'appointments', 'appointment_banks', 'banks', 'bank_branchs',
            'bookings', 'contract', 'cars.color',
            'sale', 'branch', 'team', 'branch_team', 'customer'
        ])
            ->where([['status_del', 1]])
            ->where([['work_status', $work_status]])
            ->where(function ($query) use ($search) {
                $query->where('id', substr($search, 1));
                $query->orWhere('customer_name', 'LIKE', '%' . $search . '%');
                $query->orWhereHas('cars', function ($query) use ($search) {
                    $query->where('car_no', 'LIKE', $search . '%');
                    $query->orWhere('car_regis_current', 'LIKE', '%' . $search . '%');
                    $query->orWhere('car_regis', 'LIKE', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'DESC')
            ->limit(1000)
            ->get();

        return response()->json([
            'dataWorking' => $data,
        ]);
    }

    public function selectWhereCancle(Request $request)
    {

        $search = $request->input('search');

        $data = Working::with([
            'cars', 'cars.car_models', 'cars.car_series', 'cars.car_serie_sub',
            'appointments', 'appointment_banks', 'banks', 'bank_branchs',
            'bookings', 'contract', 'cars.color',
            'sale', 'branch', 'team', 'branch_team', 'customer'
        ])
            ->where([['status_del', 0]])
            ->where(function ($query) use ($search) {
                $query->where('id', substr($search, 1));
                $query->orWhere('customer_name', 'LIKE', '%' . $search . '%');
                $query->orWhereHas('cars', function ($query) use ($search) {
                    $query->where('car_no', 'LIKE', $search . '%');
                    $query->orWhere('car_regis_current', 'LIKE', '%' . $search . '%');
                    $query->orWhere('car_regis', 'LIKE', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'DESC')
            ->limit(1000)
            ->get();

        return response()->json([
            'dataWorking' => $data,
        ]);
    }

    public function working_cancel(Request $request)
    {

        $branch_id = $request->input('branch_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $dataWorking = Working::with(['cars', 'sale', 'branch', 'team', 'branch_team'])
            ->where([['status_del', 0], ['branch_id', $branch_id], ['created_at', '>', $timeStart], ['created_at', '<', $timeEnd]])
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json($dataWorking);
    }

    public function followWork(Request $request)
    {

        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $search = $request->input('search');
        $branch_team_id = $request->input('branch_team_id');
        $user_team_id = $request->input('user_team_id');
        $branch_id = $request->input('branch_id');
        $sale_id = $request->input('user_id');
        $user_group_permission = $request->input('user_group_permission');
        $commission_mount = $request->input('commission_mount');

        if ($request->input('work_status') == 'all') {
            $work_status = 'all';
        } else if ($request->input('work_status') == 'close') {
            $work_status = 'close';
        } else if ($request->input('work_status') == 0) {
            $work_status = -1;
        } else {
            $work_status = $request->input('work_status');
        }

        if ($timeStart) {
            $data = Working::with([
                'cars', 'appointments', 'cars.car_models', 'cars.car_series', 'cars.car_serie_sub', 'cars.insurance', 'cars.color', 'pre_approve', 'appointment_banks', 'banks', 'bank_branchs',
                'bookings', 'contract',
                'sale', 'branch', 'team', 'branch_team', 'customer', 'user', 'request_update'
            ])
                ->where([['status_del', 1]])
                ->when($work_status, function ($query) use ($work_status, $timeStart, $timeEnd) {
                    if ($work_status == 'close') {
                        $query->whereHas('contract', function ($query) use ($timeStart, $timeEnd) {
                            $query->where('contract_date', '>=', $timeStart);
                            $query->where('contract_date', '<=', $timeEnd);
                        });
                    } else {
                        $query->whereHas('bookings', function ($query) use ($timeStart, $timeEnd) {
                            $query->where('created_at', '>=', $timeStart);
                            $query->where('created_at', '<=', $timeEnd);
                        });
                    }
                    if ($work_status == 'all') {
                    } else if ($work_status == 'close') {
                        $query->where('work_status', 11);
                    } else if ($work_status == -1) {
                        $query->where([['work_status', '>=', 1], ['work_status', '<', 11]]);
                    } else {
                        $query->where('work_status', $work_status);
                    }
                })
                ->when($branch_team_id, function ($query) use ($branch_team_id) {
                    return $query->where('branch_team_id', $branch_team_id);
                })
                ->when($user_team_id, function ($query) use ($user_team_id) {
                    return $query->where('user_team_id', $user_team_id);
                })
                ->when($branch_id, function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                })
                ->when($sale_id, function ($query) use ($sale_id, $user_group_permission) {
                    if ($user_group_permission == 2 || $user_group_permission == 3) {
                        return $query->where('sale_id', $sale_id);
                    }
                })
                ->orderBy('created_at', 'DESC')
                ->get();
        } elseif ($search) {
            $data = Working::with([
                'cars', 'cars.car_models', 'cars.car_series', 'cars.car_serie_sub', 'cars.insurance',
                'appointments', 'pre_approve', 'appointment_banks', 'banks', 'bank_branchs',
                'bookings', 'contract', 'cars.color',
                'sale', 'branch', 'team', 'branch_team', 'customer', 'user', 'request_update'
            ])
                ->whereHas('cars', function ($query) use ($search) {
                    $query->where('car_no', 'LIKE', '%' . $search . '%');
                    $query->orWhere('car_regis_current', 'LIKE', '%' . $search . '%');
                    $query->orWhere('car_regis', 'LIKE', '%' . $search . '%');
                })
                ->orWhere('customer_name', 'LIKE', '%' . $search . '%')
                ->get();
        } elseif ($commission_mount) {
            $data = Working::with([
                'cars', 'cars.car_models', 'cars.car_series', 'cars.car_serie_sub', 'cars.insurance',
                'appointments', 'pre_approve', 'banks', 'bank_branchs',
                'bookings', 'contract', 'cars.color',
                'sale', 'branch', 'team', 'branch_team', 'customer', 'user', 'request_update'
            ])
                ->where([['status_del', 1]])
                ->whereHas('appointment_banks', function ($query) use ($commission_mount) {
                    return $query->where('commission_mount', $commission_mount);
                })
                ->when($work_status, function ($query) use ($work_status) {
                    if ($work_status == 'all') {
                    } else if ($work_status == -1) {
                        return $query->where([['work_status', '>=', 1], ['work_status', '<', 11]]);
                    } else {
                        return $query->where('work_status', $work_status);
                    }
                })
                ->when($branch_team_id, function ($query) use ($branch_team_id) {
                    return $query->where('branch_team_id', $branch_team_id);
                })
                ->when($user_team_id, function ($query) use ($user_team_id) {
                    return $query->where('user_team_id', $user_team_id);
                })
                ->when($branch_id, function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                })
                ->when($sale_id, function ($query) use ($sale_id, $user_group_permission) {
                    if ($user_group_permission == 2 || $user_group_permission == 3) {
                        return $query->where('sale_id', $sale_id);
                    }
                })
                ->orderBy('created_at', 'DESC')
                ->get();
        } else {
            $data = Working::with([
                'cars', 'cars.car_models', 'cars.car_series', 'cars.car_serie_sub', 'cars.insurance',
                'appointments', 'pre_approve', 'banks', 'appointment_banks', 'bank_branchs',
                'bookings', 'contract', 'cars.color',
                'sale', 'branch', 'team', 'branch_team', 'customer', 'user', 'request_update', 'request_log'
            ])
                ->where([['status_del', 1]])
                ->when($work_status, function ($query) use ($work_status) {
                    if ($work_status == 'all') {
                    } else if ($work_status == -1) {
                        return $query->where([['work_status', '>=', 1], ['work_status', '<', 11]]);
                    } else {
                        return $query->where('work_status', $work_status);
                    }
                })
                ->when($branch_team_id, function ($query) use ($branch_team_id) {
                    return $query->where('branch_team_id', $branch_team_id);
                })
                ->when($branch_id, function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                })
                ->when($user_team_id, function ($query) use ($user_team_id) {
                    return $query->where('user_team_id', $user_team_id);
                })
                ->when($sale_id, function ($query) use ($sale_id, $user_group_permission) {
                    if ($user_group_permission == 2 || $user_group_permission == 3) {
                        return $query->where('sale_id', $sale_id);
                    }
                })
                ->orderBy('created_at', 'DESC')
                ->get();
        }

        $map = $data->map(function ($item) {
            $newData['id'] = $item['id'];
            $newData['pedding'] = $item['pedding'];
            $newData['car_id'] = $item['car_id'];
            $newData['car_no'] = $item['cars']['car_no'];
            $newData['car_tax_date'] = $item['cars']['tex_date'];
            $newData['customer_id'] = $item['customer_id'];
            $newData['work_status'] = $item['work_status'];
            $newData['updated_at'] = $item['updated_at'];
            $newData['appointment_mkt_date'] = $item['appointment_mkt_date'];
            $newData['job_fix'] = $item['job_fix'];
            $newData['pathner_job_technician'] = $item['pathner_job_technician'];
            $newData['status_del'] = $item['status_del'];
            $newData['request_update'] = $item['request_update'];
            if ($item['request_log']) {
                $newData['request_log'] = $item['request_log'];
            }
            if ($item['user']) {
                $newData['user'] = $item['user']['first_name'];
            } else {
                $newData['user'] = 'N/A';
            }


            if (!empty($item['cars']['tex_date'])) {
                $newData['car_tax_date'] = $item['cars']['tex_date'];
            } else {
                $newData['car_tax_date'] = ' ';
            }

            if (!empty($item['cars']) && !empty($item['cars']['insurance'][0])) {
                $newData['car_insurance'] = $item['cars']['insurance'][0]['insurance_end'];
            } else {
                $newData['car_insurance'] = ' ';
            }


            if ($item['work_status'] == 1) {
                $newData['work_status_name'] = '1. รอจอง';
            } elseif ($item['work_status'] == 2) {
                $newData['work_status_name'] = '2. รอมัดจำ';
            } elseif ($item['work_status'] == 3) {
                $newData['work_status_name'] = '3. รอนัดทำสัญญา';
            } elseif ($item['work_status'] == 4) {
                $newData['work_status_name'] = '4. รอทำสัญญา';
            } elseif ($item['work_status'] == 5) {
                $newData['work_status_name'] = '5. รอแบงค์อนุมัติ';
            } elseif ($item['work_status'] == 6) {
                $newData['work_status_name'] = '6. แบงค์ไม่อนุมัติ';
            } elseif ($item['work_status'] == 7) {
                $newData['work_status_name'] = '7. รอปล่อยรถ';
            } elseif ($item['work_status'] == 8) {
                $newData['work_status_name'] = '8. รอชุดโอน';
            } elseif ($item['work_status'] == 9) {
                $newData['work_status_name'] = '9. รอตรวจสอบ';
            } elseif ($item['work_status'] == 10) {
                $newData['work_status_name'] = '10. รอปิดงาน';
            } elseif ($item['work_status'] == 11) {
                $newData['work_status_name'] = '11. ปิดงาน';
            } else {
                $newData['work_status_name'] = 'N/A';
            }

            $newData['car_model_name'] = $item['cars']['car_models']['car_model_name'];
            $newData['car_series_name'] = $item['cars']['car_series']['car_series_name'];
            $newData['car_serie_sub_name'] = $item['cars']['car_serie_sub']['car_serie_sub_name'];
            $newData['car_regis'] = $item['cars']['car_regis'];
            $newData['car_regis_current'] = $item['cars']['car_regis_current'];
            $newData['car_year'] = $item['cars']['car_year'];
            $newData['color_name'] = $item['cars']['color']['color_name'];
            $newData['amount_price'] = $item['cars']['amount_price'];
            $newData['amount_down'] = $item['cars']['amount_down'];
            $newData['car_price_vat'] = $item['cars']['car_price_vat'];



            if (!empty($item['sale'])) {
                $newData['sale'] = $item['sale']['first_name'];
                $newData['sale_id'] = $item['sale']['id'];
            } else {
                $newData['sale'] = 'ไม่พบผู้ขาย';
                $newData['sale_id'] = null;
            }

            if (!empty($item['branch'])) {
                $newData['branch_name'] = $item['branch']['branch_name'];
            } else {
                $newData['branch_name'] = 'ไม่พบสาขา';
            }


            if (!empty($item['team'])) {
                $newData['team_name'] = $item['team']['team_name'];
            } else {
                $newData['team_name'] = ' ';
            }

            if (!empty($item['branch_team'])) {
                $newData['branch_team_name'] = $item['branch_team']['branch_team_name'];
            } else {
                $newData['branch_team_name'] = 'ไม่พบทีมสาขา';
            }

            $newData['customer_name'] = $item['customer_name'];
            $newData['customer_tel'] = $item['customer_tel'];

            if ($item['work_status'] >= 2) {
                $newData['created_at'] = date_format($item['created_at'], "Y-m-d");
            } else {
                $newData['created_at'] = ' ';
                $newData['interested_at'] = date_format($item['created_at'], "Y-m-d");
            }


            if (!empty($item['bookings'])) {

                $newData['booking_date'] = $item['bookings']['created_at'];

                if (!is_null($item['bookings']['customer_bath_pledge'])) {
                    $newData['deposit'] = $item['bookings']['customer_bath_pledge'];
                } else {
                    $newData['deposit'] = ' ';
                }

                if ($item['bookings']['commission']) {
                    $newData['commission'] = $item['bookings']['commission'];
                } else {
                    $newData['commission'] = ' ';
                }

                if ($item['bookings']['monthly_payment']) {
                    $newData['monthly_payment'] = $item['bookings']['monthly_payment'];
                } else {
                    $newData['monthly_payment'] = ' ';
                }
            } else {
                $newData['booking_date'] = ' ';
                $newData['amount_slacken'] = ' ';
                $newData['commission'] = ' ';
                $newData['deposit'] = ' ';
                $newData['monthly_payment'] = ' ';
            }

            if (!empty($item['appointments'])) {
                $newData['appointment_date_before'] = $item['appointments']['appointment_date'];
                $newData['deposit_date'] = $item['appointments']['deposit_date'];
            } else {
                $newData['appointment_date_before'] = ' ';
                $newData['deposit_date'] = ' ';
            }

            if (!empty($item['pre_approve'])) {
                $newData['pre_approve_date'] = $item['pre_approve']['pre_approve_date'];
            } else {
                $newData['pre_approve_date'] = ' ';
            }




            if (!empty($item['appointment_banks'])) {
                if ($item['appointment_banks']['car_price'] > 0) {
                    $newData['car_price_approve'] = $item['appointment_banks']['car_price'];
                    $newData['car_price_approve_vat'] = $item['appointment_banks']['car_price'] * 1.07;
                } else {
                    $newData['car_price_approve'] = '';
                    $newData['car_price_approve_vat'] = '';
                }


                if ($item['appointment_banks']['credit']) {
                    $newData['credit'] = $item['appointment_banks']['credit'];
                } else {
                    $newData['credit'] = ' ';
                }

                if ($item['appointment_banks']['appointment_bank_date']) {
                    $newData['appointment_bank_date'] = date_format(date_create($item['appointment_banks']['appointment_bank_date']), "Y-m-d");
                } else {
                    $newData['appointment_bank_date'] = ' ';
                }

                if ($item['appointment_banks']['appointment_date']) {
                    $newData['appointment_date'] = date_format(date_create($item['appointment_banks']['appointment_date']), "Y-m-d");
                } else {
                    $newData['appointment_date'] = ' ';
                }

                if ($item['appointment_banks']['down']) {
                    $newData['down'] = $item['appointment_banks']['down'];
                } else {
                    $newData['down'] = 0;
                }

                if ($item['appointment_banks']['bank_id']) {
                    $newData['bank_id'] = $item['appointment_banks']['bank_id'];
                } else {
                    $newData['bank_id'] = 0;
                }

                if ($item['work_status'] >= 7) {
                    $newData['car_price_persen'] = $item['appointment_banks']['car_price_persen'];
                } else {
                    $newData['car_price_persen'] = ' ';
                }

                if ($item['appointment_banks']['finance_price'] > 0) {
                    $newData['finance_price'] = $item['appointment_banks']['finance_price'];
                } else {
                    $newData['finance_price'] = ' ';
                }

                if ($item['appointment_banks']['finance_price'] > 0) {
                    $newData['finance_price_vat'] = $item['appointment_banks']['finance_price'] * 1.07;
                } else {
                    $newData['finance_price_vat'] = ' ';
                }

                if (isset($item['appointment_banks']['sub_down'])) {
                    $newData['sub_down'] = $item['appointment_banks']['sub_down'];
                } else {
                    $newData['sub_down'] = ' ';
                }

                if ($item['appointment_banks']['customer_payment_due']) {
                    $newData['customer_payment_due'] = $item['appointment_banks']['customer_payment_due'];
                } else {
                    $newData['customer_payment_due'] = ' ';
                }

                if ($item['appointment_banks']['customer_payment']) {
                    $newData['customer_payment'] = $item['appointment_banks']['customer_payment'];
                } else {
                    $newData['customer_payment'] = ' ';
                }

                if ($item['appointment_banks']['customer_grade']) {
                    $newData['customer_grade'] = $item['appointment_banks']['customer_grade'];
                } else {
                    $newData['customer_grade'] = ' ';
                }


                if ($item['appointment_banks']['note_page']) {
                    $newData['note_page'] = $item['appointment_banks']['note_page'];
                } else {
                    $newData['note_page'] = ' ';
                }


                if ($item['appointment_banks']['commission_mount']) {
                    $newData['commission_mount'] = $item['appointment_banks']['commission_mount'];
                } else {
                    $newData['commission_mount'] = ' ';
                }


                if ($item['appointment_banks']['appointment_book_date']) {
                    $newData['appointment_book_date'] = date_format(date_create($item['appointment_banks']['appointment_book_date']), "Y-m-d");
                } else {
                    $newData['appointment_book_date'] = ' ';
                }


                if ($item['appointment_banks']['appointment_transfer_date']) {
                    $newData['appointment_transfer_date'] = date_format(date_create($item['appointment_banks']['appointment_transfer_date']), "Y-m-d");
                } else {
                    $newData['appointment_transfer_date'] = ' ';
                }


                if ($item['appointment_banks']['appointment_sentbook_date'] != null) {
                    $newData['appointment_sentbook_date'] = date_format(date_create($item['appointment_banks']['appointment_sentbook_date']), "Y-m-d");
                } else {
                    $newData['appointment_sentbook_date'] = ' ';
                }



                if ($item['appointment_banks']['appointment_money_date']) {
                    $newData['appointment_money_date'] = date_format(date_create($item['appointment_banks']['appointment_money_date']), "Y-m-d");
                } else {
                    $newData['appointment_money_date'] = ' ';
                }

                if ($item['appointment_banks']['appointment_money_price'] > 0) {
                    $newData['appointment_money_price'] = $item['appointment_banks']['appointment_money_price'];
                } else {
                    $newData['appointment_money_price'] = ' ';
                }

                if (!empty($item['appointment_banks'])) {
                    $newData['sale_name'] = $item['appointment_banks']['mtk_name'];
                    $newData['sale_tel'] = $item['appointment_banks']['mtk_tel'];
                } else {
                    $newData['sale_name'] = ' ';
                    $newData['sale_tel'] = ' ';
                }
            } else {
                $newData['car_price_approve'] = ' ';
                $newData['car_price_approve_vat'] = ' ';
                $newData['credit'] = ' ';
                $newData['appointment_bank_date'] = ' ';
                $newData['appointment_date'] = ' ';
                $newData['car_price_persen'] = ' ';
                $newData['appointment_transfer_date'] = ' ';
                $newData['appointment_book_date'] = ' ';
                $newData['appointment_money_date'] = ' ';
                $newData['appointment_sentbook_date'] = ' ';
                $newData['appointment_money_price'] = ' ';
                $newData['customer_payment_due'] = ' ';
                $newData['customer_payment'] = ' ';
                $newData['customer_grade'] = ' ';
                $newData['note_page'] = ' ';
                $newData['commission_mount'] = ' ';
                $newData['finance_price'] = ' ';
                $newData['finance_price_vat'] = ' ';
                $newData['sub_down'] = ' ';
            }

            if ($item['work_status'] >= 5) {
                if ($item['appointment_bank_type'] == 1) {
                    $newData['appointment_bank_type'] = 'ครบ';
                    if ($item['appointment_banks'] && $item['appointment_banks']['appointment_bank_document_date']) {
                        $newData['appointment_bank_document_date'] = $item['appointment_banks']['appointment_bank_document_date'];
                    } else {
                        $newData['appointment_bank_document_date'] = ' ';
                    }
                } elseif ($item['appointment_bank_type'] == 2) {
                    $newData['appointment_bank_type'] = 'ไม่ครบ';
                    if ($item['appointment_banks']) {
                        $newData['appointment_bank_list'] =  nl2br(htmlspecialchars($item['appointment_banks']['appointment_bank_list']));
                    } else {
                        $newData['appointment_bank_list'] = 'n/a';
                    }
                } else {
                    $newData['appointment_bank_type'] = ' ';
                }
            } else {
                $newData['appointment_bank_type'] = ' ';
                $newData['appointment_bank_document_date'] = ' ';
            }

            if ($item['hear_from_type'] == 1) {
                $newData['hear_from'] = 'หน้าร้าน';
            } elseif ($item['hear_from_type'] == 2) {
                $newData['hear_from'] = 'เพจบริษัท';
            } elseif ($item['hear_from_type'] == 3) {
                $newData['hear_from'] = 'ลูกค้าเก่าแนะนำ';
            } elseif ($item['hear_from_type'] == 4) {
                $newData['hear_from'] = 'นายหน้า';
            } elseif ($item['hear_from_type'] == 5) {
                $newData['hear_from'] = 'ใบปลิว';
            } elseif ($item['hear_from_type'] == 6) {
                $newData['hear_from'] = 'Marketplace/ไลน์/เพจส่วนตัว';
            } elseif ($item['hear_from_type'] == 7) {
                $newData['hear_from'] = 'เว็บไซต์';
            } elseif ($item['hear_from_type'] == 8) {
                $newData['hear_from'] = 'tiktok';
            } else {
                $newData['hear_from'] = 'อื่นๆ';
            }

            if (!empty($item['customer'])) {
                if ($item['customer']['customer_job'] == 1) {
                    $newData['customer_job'] = 'ข้าราชการ';
                } elseif ($item['customer']['customer_job'] == 2) {
                    $newData['customer_job'] = 'พนักงานเอกชน';
                } elseif ($item['customer']['customer_job'] == 3) {
                    $newData['customer_job'] = 'เกษตกร';
                } elseif ($item['customer']['customer_job'] == 4) {
                    $newData['customer_job'] = 'ค้าขาย';
                } elseif ($item['customer']['customer_job'] == 5) {
                    $newData['customer_job'] = 'อื่น ๆ - ' . $item['customer']['customer_job_list'];
                } else {
                    $newData['customer_job'] = 'N/A';
                }
            } else {
                $newData['customer_job'] = ' ';
            }

            if (!empty($item['banks'])) {
                $newData['bank_name'] = $item['banks']['bank_name'];
                $newData['bank_nick_name'] = $item['banks']['bank_nick_name'];
            } else {
                $newData['bank_name'] = ' ';
                $newData['bank_nick_name'] = ' ';
            }

            if (!empty($item['bank_branchs'])) {
                $newData['bank_branch_name'] = $item['bank_branchs']['bank_branch_name'];
            } else {
                $newData['bank_branch_name'] = ' ';
            }



            if (!empty($item['contract'])) {
                $newData['contract'] = $item['contract'];
                $newData['contract_date'] = date_format(date_create($item['contract']['contract_date']), "Y-m-d");
                $newData['insurance'] = $item['contract']['insurance'] + $item['contract']['insurance_other'];
            } else {
                $newData['contract'] = null;
                $newData['contract_date'] = ' ';
            }

            if ($item['note']) {
                $newData['note'] = nl2br(htmlspecialchars($item['note']));
            } else {
                $newData['note'] = ' ';
            }

            if ($item['note_sale']) {
                $newData['note_sale'] = nl2br(htmlspecialchars($item['note_sale']));
            } else {
                $newData['note_sale'] = ' ';
            }

            return $newData;
        });

        if ($user_group_permission) {
            $badgeWorking = Working::where([['status_del', 1]])
                ->select('sale_id', 'work_status as status', DB::raw("count(work_status) as content"))
                ->when($user_group_permission, function ($query) use ($user_group_permission) {
                    if ($user_group_permission == 2 || $user_group_permission == 3) {
                        return $query->where('work_status', '<', 11);
                    }
                })
                ->when($branch_id, function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                })
                ->when($branch_team_id, function ($query) use ($branch_team_id) {
                    return $query->where('branch_team_id', $branch_team_id);
                })
                ->when($sale_id, function ($query) use ($sale_id, $user_group_permission) {
                    if ($user_group_permission == 2 || $user_group_permission == 3) {
                        return $query->where('sale_id', $sale_id);
                    }
                })
                ->when($user_team_id, function ($query) use ($user_team_id) {
                    return $query->where('user_team_id', $user_team_id);
                })
                ->groupBy('work_status')
                ->get();

            if ($user_group_permission == 2) {
                $dataAll = Working::select('id')
                    ->where([['work_status', '>=', 1], ['work_status', '<', 11], ['status_del', 1]])
                    ->when($user_team_id, function ($query) use ($user_team_id) {
                        return $query->where('user_team_id', $user_team_id);
                    })
                    ->when($branch_id, function ($query) use ($branch_id) {
                        return $query->where('branch_id', $branch_id);
                    })
                    ->when($branch_team_id, function ($query) use ($branch_team_id) {
                        return $query->where('branch_team_id', $branch_team_id);
                    })
                    ->count();
            } else if ($user_group_permission == 3) {
                $dataAll = Working::select('id')->where([['work_status', '>=', 1], ['work_status', '<', 11], ['sale_id', $sale_id], ['status_del', 1]])
                    ->when($user_team_id, function ($query) use ($user_team_id) {
                        return $query->where('user_team_id', $user_team_id);
                    })
                    ->when($branch_id, function ($query) use ($branch_id) {
                        return $query->where('branch_id', $branch_id);
                    })

                    ->when($branch_team_id, function ($query) use ($branch_team_id) {
                        return $query->where('branch_team_id', $branch_team_id);
                    })->count();
            } else {
                $dataAll = Working::select('id')->where([['status_del', 1], ['work_status', '>=', 1], ['work_status', '<', 11]])
                    ->when($user_team_id, function ($query) use ($user_team_id) {
                        return $query->where('user_team_id', $user_team_id);
                    })
                    ->when($branch_id, function ($query) use ($branch_id) {
                        return $query->where('branch_id', $branch_id);
                    })

                    ->when($branch_team_id, function ($query) use ($branch_team_id) {
                        return $query->where('branch_team_id', $branch_team_id);
                    })->count();
            }

            return response()->json([
                'data' => $map,
                'dataAll' => empty($dataAll) ? '0' : $dataAll,
                'badgeWorking' => $badgeWorking,
            ]);
        } else {
            return response()->json([
                'data' => $map,
            ]);
        }
    }

    public function commission_month_by_team_branch(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $arr = explode("-", $timeStart);
        $commission_mount = $arr[0] . '-' . $arr[1];

        $appointment_banks = DB::table('workings')
            ->leftJoin('appointment_banks', 'workings.id', '=', 'appointment_banks.working_id')
            ->where('appointment_banks.commission_mount', $commission_mount)
            ->where('workings.status_del', 1)
            ->select(DB::raw('branch_team_id'))
            ->get();

        return response()->json($appointment_banks);
    }

    public function followDown(Request $request)
    {
        $date = date_create("2022-08-15");
        $start_date = date_format($date, "Y-m-d H:i:s");
        $working = Working::with('appointment_banks', 'cars', 'cars.car_models', 'cars.car_series', 'cars.car_serie_sub', 'cars.color', 'sale', 'branch', 'team', 'branch_team')
            ->where('created_at', '>=', $start_date)
            ->where('work_status', '>=', 8)
            ->where('status_del', 1)
            ->get();

        $output = collect($working)->filter(function ($value, $key) {

            $value->car_model_name = $value->cars->car_models->car_model_name;
            $value->car_series_name = $value->cars->car_series->car_series_name;
            $value->car_serie_sub_name = $value->cars->car_serie_sub->car_serie_sub_name;
            $value->car_regis = $value->cars->car_regis;
            $value->car_year = $value->cars->car_year;
            $value->color_name = $value->cars->color->color_name;
            $value->sale_name = $value->sale->first_name . " " . $value->sale->last_name;

            $value->branch_team_name = $value->branch_team->branch_team_name;
            $value->branch_name = $value->branch->branch_name;
            if ($value->team) {
                $value->team_name = $value->team->team_name;
            } else {
                $value->team_name = "";
            }

            $value->sumFinancial = Financial::where('working_id', $value->id)
                ->sum('bath');
            return ($value->sumFinancial != $value->appointment_banks->down && $value->appointment_banks->bank_id != 6);
        })->values();
        return response()->json($output);
    }

    public function updateNote(Request $request, $workingID)
    {
        $working = Working::find($workingID);
        $working->note = $request->note;
        $working->note_sale = $request->note_sale;
        $working->save();

        return response()->json($working);
    }
}
