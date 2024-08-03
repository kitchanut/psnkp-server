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
        $filters = $this->getFilters($request);
        $query = $this->buildQuery($filters);
        $data = $query->get();

        $mappedData = $this->mapWorkingData($data);

        if ($filters['user_group_permission']) {
            $badgeWorking = $this->getBadgeWorking($filters);
            // $dataAll = $this->getDataAll($filters);

            return response()->json([
                'data' => $mappedData,
                // 'dataAll' => $dataAll,
                'badgeWorking' => $badgeWorking,
            ]);
        }

        return response()->json(['data' => $mappedData]);
    }

    private function getFilters(Request $request)
    {
        return [
            'timeStart' => $request->input('timeStart'),
            'timeEnd' => $request->input('timeEnd'),
            'search' => $request->input('search'),
            'branch_team_id' => $request->input('branch_team_id'),
            'user_team_id' => $request->input('user_team_id'),
            'branch_id' => $request->input('branch_id'),
            'sale_id' => $request->input('user_id'),
            'user_group_permission' => $request->input('user_group_permission'),
            'commission_mount' => $request->input('commission_mount'),
            'work_status' => $this->getWorkStatus($request->input('work_status')),
        ];
    }

    private function getWorkStatus($status)
    {
        if ($status == 'all') return 'all';
        if ($status == 'close') return 'close';
        if ($status == 'search') return 'all';
        if ($status == 0) return -1;
        return $status;
    }

    private function buildQuery($filters)
    {
        $query = Working::with([
            'cars',
            'appointments',
            'cars.car_models',
            'cars.car_series',
            'cars.car_serie_sub',
            'cars.color',
            'cars.insurance',
            'pre_approve',
            'appointment_banks',
            'banks',
            'bank_branchs',
            'bookings',
            'contract',
            'sale',
            'branch',
            'team',
            'branch_team',
            'customer',
            'user',
            'request_update',
            'request_log'
        ])
            ->where('status_del', 1)
            ->with(['cars' => function ($query) {
                $query->select(
                    'id',
                    'car_models_id',
                    'car_serie_id',
                    'car_serie_sub_id',
                    'color_id',
                    'car_no',
                    'tex_date',
                    'car_regis',
                    'car_regis_current',
                    'car_year',
                    'amount_price',
                    'amount_down',
                    'car_price_vat'
                );
            }]);
        // ->with(['appointments' => function ($query) {
        //     $query->select('id', 'appointment_date', 'deposit_date');
        // }])
        $this->applyFilters($query, $filters);
        return $query->orderBy('created_at', 'DESC');
    }

    private function applyFilters($query, $filters)
    {
        if ($filters['timeStart']) {
            $this->applyTimeFilter($query, $filters);
        } elseif ($filters['search']) {
            $this->applySearchFilter($query, $filters['search']);
        } elseif ($filters['commission_mount']) {
            $this->applyCommissionFilter($query, $filters);
        }
        $this->applyWorkStatusFilters($query, $filters);
        $this->applyCommonFilters($query, $filters);
    }

    private function applyTimeFilter($query, $filters)
    {
        if ($filters['work_status'] == 'close') {
            $query->whereHas('contract', function ($q) use ($filters) {
                $q->whereBetween('contract_date', [$filters['timeStart'], $filters['timeEnd']]);
            });
        } else {
            $query->whereHas('bookings', function ($q) use ($filters) {
                $q->whereBetween('created_at', [$filters['timeStart'], $filters['timeEnd']]);
            });
        }
    }

    private function applySearchFilter($query, $search)
    {
        $query->where(function ($q) use ($search) {
            $q->whereHas('cars', function ($q) use ($search) {
                $q->where('car_no', 'LIKE', "%{$search}%")
                    ->orWhere('car_regis_current', 'LIKE', "%{$search}%")
                    ->orWhere('car_regis', 'LIKE', "%{$search}%");
            });
            $q->orWhere('customer_name', 'LIKE', "%{$search}%");
        });
    }

    private function applyCommissionFilter($query, $filters)
    {
        $query->whereHas('appointment_banks', function ($q) use ($filters) {
            $q->where('commission_mount', $filters['commission_mount']);
        });
    }

    private function applyWorkStatusFilters($query, $filters)
    {
        if ($filters['work_status'] == 'all') {
        } elseif ($filters['work_status'] == 'close') {
            $query->where('work_status', 11);
        } elseif ($filters['work_status'] == -1) {
            $query->whereBetween('work_status', [1, 10]);
        } else {
            $query->where('work_status', $filters['work_status']);
        }
        return $query;
    }

    private function applyCommonFilters($query, $filters)
    {
        if ($filters['branch_team_id']) {
            $query->where('branch_team_id', $filters['branch_team_id']);
        }

        if ($filters['user_team_id']) {
            $query->where('user_team_id', $filters['user_team_id']);
        }

        if ($filters['branch_id']) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if ($filters['sale_id']) {
            $query->where('sale_id', $filters['sale_id']);
        }

        return $query;
    }

    private function mapWorkingData($data)
    {
        return $data->map(function ($item) {
            $newData['id'] = $item['id'];
            $newData['pedding'] = $item['pedding'];
            $newData['car_id'] = $item['car_id'];
            $newData['user'] = $item['user'] ? $item['user']['first_name'] : 'N/A';

            $newData['updated_at'] = $item['updated_at'];
            $newData['appointment_mkt_date'] = $item['appointment_mkt_date'];
            $newData['job_fix'] = $item['job_fix'];
            $newData['pathner_job_technician'] = $item['pathner_job_technician'];

            // customer
            $newData['customer_id'] = $item['customer_id'];
            $newData['customer_name'] = $item['customer_name'];
            $newData['customer_tel'] = $item['customer_tel'];

            // created_at
            $newData['created_at'] = $item['work_status'] >= 2 ? date_format($item['created_at'], "Y-m-d") : ' ';
            $newData['interested_at'] = $item['work_status'] >= 2 ? date_format($item['created_at'], "Y-m-d") : ' ';

            // status
            $newData['work_status'] = $item['work_status'];
            $newData['work_status_name'] = $this->getWorkStatusName($item['work_status']);
            $newData['status_del'] = $item['status_del'];
            // log
            $newData['request_update'] = $item['request_update'];
            $newData['request_log'] = $item['request_log'] ? $item['request_log'] : [];

            // note
            $newData['note'] = $item['note'] ? nl2br(htmlspecialchars($item['note'])) : ' ';
            $newData['note_sale'] = $item['note_sale'] ? nl2br(htmlspecialchars($item['note_sale'])) : ' ';

            // Car
            $newData['car_no'] = $item['cars']['car_no'];
            $newData['car_tax_date'] = !empty($item['cars']['tex_date']) ? $item['cars']['tex_date'] : ' ';
            $newData['car_regis'] = $item['cars']['car_regis'];
            $newData['car_regis_current'] = $item['cars']['car_regis_current'];
            $newData['car_year'] = $item['cars']['car_year'];
            $newData['amount_price'] = $item['cars']['amount_price'];
            $newData['amount_down'] = $item['cars']['amount_down'];
            $newData['car_price_vat'] = $item['cars']['car_price_vat'];

            $newData['car_model_name'] = $item['cars']['car_models']['car_model_name'];
            $newData['car_series_name'] = $item['cars']['car_series']['car_series_name'];
            $newData['car_serie_sub_name'] = $item['cars']['car_serie_sub']['car_serie_sub_name'];
            $newData['color_name'] = $item['cars']['color']['color_name'];
            $newData['car_insurance'] = !empty($item['cars']) && !empty($item['cars']['insurance'][0]) ? $item['cars']['insurance'][0]['insurance_end'] : ' ';


            // sale
            $newData['sale'] = $item['sale'] ? $item['sale']['first_name'] : 'ไม่พบผู้ขาย';
            $newData['sale_id'] = $item['sale'] ? $item['sale']['id'] : null;

            // branch
            $newData['branch'] = $item['branch'] ? $item['branch']['branch_name'] : 'ไม่พบสาขา';
            $newData['team_name'] = $item['team'] ? $item['team']['team_name'] : ' ';
            $newData['branch_team_name'] = $item['branch_team'] ? $item['branch_team']['branch_team_name'] : 'ไม่พบทีมสาขา';


            // bookings
            $newData['booking_date'] = $item['bookings'] ? $item['bookings']['created_at'] : ' ';
            $newData['deposit'] = $item['bookings'] && !is_null($item['bookings']['customer_bath_pledge']) ? $item['bookings']['customer_bath_pledge'] : ' ';
            $newData['commission'] = $item['bookings'] && $item['bookings']['commission'] ? $item['bookings']['commission'] : ' ';
            $newData['monthly_payment'] = $item['bookings'] && $item['bookings']['monthly_payment'] ? $item['bookings']['monthly_payment'] : ' ';
            $newData['amount_slacken'] = ' ';

            // appointments
            $newData['appointment_date_before'] = $item['appointments'] ? $item['appointments']['appointment_date'] : ' ';
            $newData['deposit_date'] = $item['appointments'] ? $item['appointments']['deposit_date'] : ' ';

            // pre_approve
            $newData['pre_approve_date'] = $item['pre_approve'] ? $item['pre_approve']['pre_approve_date'] : ' ';

            // appointment_banks
            $newData['car_price_approve'] = $item['appointment_banks'] && $item['appointment_banks']['car_price'] > 0 ? $item['appointment_banks']['car_price'] : ' ';
            $newData['car_price_approve_vat'] = $item['appointment_banks'] && $item['appointment_banks']['car_price'] > 0 ? $item['appointment_banks']['car_price'] * 1.07 : ' ';
            $newData['credit'] = $item['appointment_banks'] && $item['appointment_banks']['credit'] ? $item['appointment_banks']['credit'] : ' ';
            $newData['appointment_bank_date'] = $item['appointment_banks'] && $item['appointment_banks']['appointment_bank_date'] ? date_format(date_create($item['appointment_banks']['appointment_bank_date']), "Y-m-d") : ' ';
            $newData['appointment_date'] = $item['appointment_banks'] && $item['appointment_banks']['appointment_date'] ? date_format(date_create($item['appointment_banks']['appointment_date']), "Y-m-d") : ' ';
            $newData['down'] = $item['appointment_banks'] && $item['appointment_banks']['down'] ? $item['appointment_banks']['down'] : 0;
            $newData['bank_id'] = $item['appointment_banks'] && $item['appointment_banks']['bank_id'] ? $item['appointment_banks']['bank_id'] : 0;
            $newData['car_price_persen'] = $item['appointment_banks'] && $item['work_status'] >= 7 ? $item['appointment_banks']['car_price_persen'] : ' ';
            $newData['finance_price'] = $item['appointment_banks'] && $item['appointment_banks']['finance_price'] > 0 ? $item['appointment_banks']['finance_price'] : ' ';
            $newData['finance_price_vat'] = $item['appointment_banks'] && $item['appointment_banks']['finance_price'] > 0 ? $item['appointment_banks']['finance_price'] * 1.07 : ' ';
            $newData['sub_down'] = $item['appointment_banks'] && $item['appointment_banks']['sub_down'] ? $item['appointment_banks']['sub_down'] : ' ';
            $newData['customer_payment_due'] = $item['appointment_banks'] && $item['appointment_banks']['customer_payment_due'] ? $item['appointment_banks']['customer_payment_due'] : ' ';
            $newData['customer_payment'] = $item['appointment_banks'] && $item['appointment_banks']['customer_payment'] ? $item['appointment_banks']['customer_payment'] : ' ';
            $newData['customer_grade'] = $item['appointment_banks'] && $item['appointment_banks']['customer_grade'] ? $item['appointment_banks']['customer_grade'] : ' ';
            $newData['note_page'] = $item['appointment_banks'] && $item['appointment_banks']['note_page'] ? $item['appointment_banks']['note_page'] : ' ';
            $newData['commission_mount'] = $item['appointment_banks'] && $item['appointment_banks']['commission_mount'] ? $item['appointment_banks']['commission_mount'] : ' ';
            $newData['appointment_book_date'] = $item['appointment_banks'] && $item['appointment_banks']['appointment_book_date'] ? date_format(date_create($item['appointment_banks']['appointment_book_date']), "Y-m-d") : ' ';
            $newData['appointment_transfer_date'] = $item['appointment_banks'] && $item['appointment_banks']['appointment_transfer_date'] ? date_format(date_create($item['appointment_banks']['appointment_transfer_date']), "Y-m-d") : ' ';
            $newData['appointment_sentbook_date'] = $item['appointment_banks'] && $item['appointment_banks']['appointment_sentbook_date'] ? date_format(date_create($item['appointment_banks']['appointment_sentbook_date']), "Y-m-d") : ' ';
            $newData['appointment_money_date'] = $item['appointment_banks'] && $item['appointment_banks']['appointment_money_date'] ? date_format(date_create($item['appointment_banks']['appointment_money_date']), "Y-m-d") : ' ';
            $newData['appointment_money_price'] = $item['appointment_banks'] && $item['appointment_banks']['appointment_money_price'] > 0 ? $item['appointment_banks']['appointment_money_price'] : ' ';
            $newData['sale_name'] = $item['appointment_banks'] ? $item['appointment_banks']['mtk_name'] : ' ';
            $newData['sale_tel'] = $item['appointment_banks'] ? $item['appointment_banks']['mtk_tel'] : ' ';
            $newData['appointment_bank_type'] = $this->getAppointmentBankType($item);
            $newData['appointment_bank_document_date'] = $this->getAppointmentBankDate($item);
            $newData['appointment_bank_list'] = $this->getAppointmentBankList($item);
            $newData['hear_from'] = $this->getHearFrom($item['hear_from_type']);
            $newData['customer_job'] = $this->getCustomerJob($item['customer']);

            // bank
            $newData['bank_name'] = $item['banks'] ? $item['banks']['bank_name'] : ' ';
            $newData['bank_nick_name'] = $item['banks'] ? $item['banks']['bank_nick_name'] : ' ';

            // bank_branch
            $newData['bank_branch_name'] = $item['bank_branchs'] ? $item['bank_branchs']['bank_branch_name'] : ' ';

            // contract
            $newData['contract'] = $item['contract'] ? $item['contract'] : null;
            $newData['contract_date'] = $item['contract'] ? date_format(date_create($item['contract']['contract_date']), "Y-m-d") : ' ';
            $newData['insurance'] = $item['contract'] ? $item['contract']['insurance'] + $item['contract']['insurance_other'] : ' ';



            return $newData;
        });
    }

    private function getWorkStatusName($status)
    {
        $statusNames = [
            1 => '1. รอจอง',
            2 => '2. รอมัดจำ',
            3 => '3. รอนัดทำสัญญา',
            4 => '4. รอทำสัญญา',
            5 => '5. รอแบงค์อนุมัติ',
            6 => '6. แบงค์ไม่อนุมัติ',
            7 => '7. รอปล่อยรถ',
            8 => '8. รอชุดโอน',
            9 => '9. รอตรวจสอบ',
            10 => '10. รอปิดงาน',
            11 => '11. ปิดงาน',
        ];
        return $statusNames[$status] ?? 'N/A';
    }

    private function getHearFrom($type)
    {
        $hearFromTypes = [
            1 => 'หน้าร้าน',
            2 => 'เพจบริษัท',
            3 => 'ลูกค้าเก่าแนะนำ',
            4 => 'นายหน้า',
            5 => 'ใบปลิว',
            6 => 'Marketplace/ไลน์/เพจส่วนตัว',
            7 => 'เว็บไซต์',
            8 => 'tiktok',
        ];
        return $hearFromTypes[$type] ?? 'อื่นๆ';
    }

    private function getCustomerJob($customer)
    {
        if (!$customer) return ' ';

        $jobs = [
            1 => 'ข้าราชการ',
            2 => 'พนักงานเอกชน',
            3 => 'เกษตกร',
            4 => 'ค้าขาย',
        ];

        if ($customer->customer_job == 5) {
            return 'อื่น ๆ - ' . $customer->customer_job_list;
        }

        return $jobs[$customer->customer_job] ?? 'N/A';
    }

    private function getAppointmentBankType($item)
    {
        if ($item->work_status < 5) {
            return ' ';
        }
        if ($item->appointment_bank_type == 1) {
            return  'ครบ';
        }
        if ($item->appointment_bank_type == 2) {
            return 'ไม่ครบ';
        }
        return ' ';
    }

    private function getAppointmentBankDate($item)
    {
        if ($item->work_status < 5) {
            return ' ';
        }
        if ($item->appointment_bank_type == 1) {
            return $item->appointment_banks->appointment_bank_document_date ?? ' ';
        }
        return ' ';
    }

    private function getAppointmentBankList($item)
    {
        if ($item->work_status < 5) {
            return ' ';
        }
        if ($item->appointment_bank_type == 2) {
            return nl2br(htmlspecialchars($item->appointment_banks->appointment_bank_list ?? ''));
        }
        return ' ';
    }

    private function getBadgeWorking($filters)
    {

        $query = Working::where('status_del', 1)
            ->where('work_status', '<', 11)
            ->select('work_status as status', DB::raw("count(work_status) as content"));
        $this->applyCommonFilters($query, $filters);
        $output = $query->groupBy('work_status')->get();

        // รวมสถานะงานทั้งหมด
        $sum = array_sum(array_column($output->toArray(), 'content'));
        $data = [
            'status' => 0,
            'content' => $sum
        ];
        $output->prepend($data);

        return $output;
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
