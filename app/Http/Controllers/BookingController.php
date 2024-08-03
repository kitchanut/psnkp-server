<?php

namespace App\Http\Controllers;

use App\Models\Amphure;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Branch_team;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Customer_detail;
use App\Models\District;
use App\Models\Income;
use App\Models\Province;
use App\Models\RequestBooking;
use App\Models\RequestLog;
use App\Models\User;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    protected $pathCurrent = 'request_booking/';

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

        (isset($request->car_no)) ? $credentials['car_no'] = $request->car_no : '';
        (isset($request->working_id)) ? $credentials['working_id'] = $request->working_id : '';
        (isset($request->car_type_name)) ? $credentials['car_type_name'] = $request->car_type_name : '';
        (isset($request->car_model_name)) ? $credentials['car_model_name'] = $request->car_model_name : '';
        (isset($request->car_series_name)) ? $credentials['car_series_name'] = $request->car_series_name : '';
        (isset($request->car_serie_sub_name)) ? $credentials['car_serie_sub_name'] = $request->car_serie_sub_name : '';
        (isset($request->branch_name)) ? $credentials['branch_name'] = $request->branch_name : '';
        (isset($request->car_year)) ? $credentials['car_year'] = $request->car_year : '';
        (isset($request->color_name)) ? $credentials['color_name'] = $request->color_name : '';
        (isset($request->car_regis)) ? $credentials['car_regis'] = $request->car_regis : '';
        (isset($request->car_no_engine)) ? $credentials['car_no_engine'] = $request->car_no_engine : '';
        (isset($request->car_mileage)) ? $credentials['car_mileage'] = $request->car_mileage : '';
        (isset($request->car_no_body)) ? $credentials['car_no_body'] = $request->car_no_body : '';
        (isset($request->customer_bath_pledge)) ? $credentials['customer_bath_pledge'] = $request->customer_bath_pledge : '';
        (isset($request->customer_bath_date_signed)) ? $credentials['customer_bath_date_signed'] = $request->customer_bath_date_signed : '';
        (isset($request->customer_car_date_release)) ? $credentials['customer_car_date_release'] = $request->customer_car_date_release : '';

        (isset($request->price_middle)) ? $credentials['price_middle'] = $request->price_middle : '';
        (isset($request->persen)) ? $credentials['persen'] = $request->persen : '';
        (isset($request->finance_price)) ? $credentials['finance_price'] = $request->finance_price : '';
        (isset($request->price_middle_close)) ? $credentials['price_middle_close'] = $request->price_middle_close : '';
        (isset($request->persen_close)) ? $credentials['persen_close'] = $request->persen_close : '';
        (isset($request->finance_price_close)) ? $credentials['finance_price_close'] = $request->finance_price_close : '';
        (isset($request->price_original)) ? $credentials['price_original'] = $request->price_original : '';
        (isset($request->amount_slacken)) ? $credentials['amount_slacken'] = $request->amount_slacken : '';
        (isset($request->price_original_deposit)) ? $credentials['price_original_deposit'] = $request->price_original_deposit : '';
        (isset($request->price_original_aggregate)) ? $credentials['price_original_aggregate'] = $request->price_original_aggregate : '';
        (isset($request->price_close)) ? $credentials['price_close'] = $request->price_close : '';
        (isset($request->price_hole_down)) ? $credentials['price_hole_down'] = $request->price_hole_down : '';
        (isset($request->price_close_deposit)) ? $credentials['price_close_deposit'] = $request->price_close_deposit : '';
        (isset($request->price_close_aggregate)) ? $credentials['price_close_aggregate'] = $request->price_close_aggregate : '';
        (isset($request->interest)) ? $credentials['interest'] = $request->interest : '';
        (isset($request->monthly_payment)) ? $credentials['monthly_payment'] = $request->monthly_payment : '';
        (isset($request->payment_term_month)) ? $credentials['payment_term_month'] = $request->payment_term_month : '';
        (isset($request->bank)) ? $credentials['bank'] = $request->bank : '';
        (isset($request->penname)) ? $credentials['penname'] = $request->penname : '';
        (isset($request->insurance)) ? $credentials['insurance'] = $request->insurance : '';
        (isset($request->credit_result)) ? $credentials['credit_result'] = $request->credit_result : '';
        (isset($request->customer_id)) ? $credentials['customer_id'] = $request->customer_id : '';
        (isset($request->customer_name)) ? $credentials['customer_name'] = $request->customer_name : '';
        (isset($request->customer_nickname)) ? $credentials['customer_nickname'] = $request->customer_nickname : '';
        (isset($request->customer_birthday_year)) ? $credentials['customer_birthday_year'] = $request->customer_birthday_year : '';
        (isset($request->customer_job)) ? $credentials['customer_job'] = $request->customer_job : '';
        (isset($request->customer_refer_job_list)) ? $credentials['customer_refer_job_list'] = $request->customer_refer_job_list : '';
        (isset($request->customer_job_list)) ? $credentials['customer_job_list'] = $request->customer_job_list : '';
        (isset($request->customer_tel)) ? $credentials['customer_tel'] = $request->customer_tel : '';
        (isset($request->customer_facebook)) ? $credentials['customer_facebook'] = $request->customer_facebook : '';
        (isset($request->customer_line)) ? $credentials['customer_line'] = $request->customer_line : '';
        (isset($request->customer_email)) ? $credentials['customer_email'] = $request->customer_email : '';
        (isset($request->customer_address)) ? $credentials['customer_address'] = $request->customer_address : '';
        (isset($request->customer_address_current)) ? $credentials['customer_address_current'] = $request->customer_address_current : '';
        (isset($request->customer_work_age)) ? $credentials['customer_work_age'] = $request->customer_work_age : '';
        (isset($request->customer_income)) ? $credentials['customer_income'] = $request->customer_income : '';
        (isset($request->customer_receive_money_type)) ? $credentials['customer_receive_money_type'] = $request->customer_receive_money_type : '';
        (isset($request->customer_slip_money)) ? $credentials['customer_slip_money'] = $request->customer_slip_money : '';
        (isset($request->customer_receive_evidence)) ? $credentials['customer_receive_evidence'] = $request->customer_receive_evidence : '';
        (isset($request->customer_land_all)) ? $credentials['customer_land_all'] = $request->customer_land_all : '';
        (isset($request->customer_land_domin)) ? $credentials['customer_land_domin'] = $request->customer_land_domin : '';
        (isset($request->customer_land_hire)) ? $credentials['customer_land_hire'] = $request->customer_land_hire : '';
        (isset($request->customer_value_home)) ? $credentials['customer_value_home'] = $request->customer_value_home : '';
        (isset($request->customer_value_car)) ? $credentials['customer_value_car'] = $request->customer_value_car : '';
        (isset($request->customer_value_ete)) ? $credentials['customer_value_ete'] = $request->customer_value_ete : '';
        (isset($request->customer_installment_history)) ? $credentials['customer_installment_history'] = $request->customer_installment_history : '';
        (isset($request->customer_installment_slow)) ? $credentials['customer_installment_slow'] = $request->customer_installment_slow : '';
        (isset($request->customer_installment_nmt)) ? $credentials['customer_installment_nmt'] = $request->customer_installment_nmt : '';
        (isset($request->customer_installment_check)) ? $credentials['customer_installment_check'] = $request->customer_installment_check : '';
        (isset($request->customer_refer_name)) ? $credentials['customer_refer_name'] = $request->customer_refer_name : '';
        (isset($request->customer_refer_tel)) ? $credentials['customer_refer_tel'] = $request->customer_refer_tel : '';
        (isset($request->customer_refer_related)) ? $credentials['customer_refer_related'] = $request->customer_refer_related : '';
        (isset($request->customer_refer_job)) ? $credentials['customer_refer_job'] = $request->customer_refer_job : '';
        (isset($request->customer_refer_address)) ? $credentials['customer_refer_address'] = $request->customer_refer_address : '';
        (isset($request->amphure_id)) ? $credentials['amphure_id'] = $request->amphure_id : '';
        (isset($request->district_id)) ? $credentials['district_id'] = $request->district_id : '';
        (isset($request->province_id)) ? $credentials['province_id'] = $request->province_id : '';
        (isset($request->zip_code)) ? $credentials['zip_code'] = $request->zip_code : '';
        (isset($request->amphure_id_current)) ? $credentials['amphure_id_current'] = $request->amphure_id_current : '';
        (isset($request->district_id_current)) ? $credentials['district_id_current'] = $request->district_id_current : '';
        (isset($request->province_id_current)) ? $credentials['province_id_current'] = $request->province_id_current : '';
        (isset($request->zip_code_current)) ? $credentials['zip_code_current'] = $request->zip_code_current : '';
        (isset($request->amphure_id_ref)) ? $credentials['amphure_id_ref'] = $request->amphure_id_ref : '';
        (isset($request->district_id_ref)) ? $credentials['district_id_ref'] = $request->district_id_ref : '';
        (isset($request->province_id_ref)) ? $credentials['province_id_ref'] = $request->province_id_ref : '';
        (isset($request->zip_code_ref)) ? $credentials['zip_code_ref'] = $request->zip_code_ref : '';
        (isset($request->todo_list)) ? $credentials['todo_list'] = $request->todo_list : '';
        (isset($request->price_hitting_car)) ? $credentials['price_hitting_car'] = $request->price_hitting_car : '';
        (isset($request->price_hitting_car_book)) ? $credentials['price_hitting_car_book'] = $request->price_hitting_car_book : '';
        (isset($request->price_hitting_car_close)) ? $credentials['price_hitting_car_close'] = $request->price_hitting_car_close : '';
        (isset($request->payment_less)) ? $credentials['payment_less'] = $request->payment_less : '';
        (isset($request->payment_balance)) ? $credentials['payment_balance'] = $request->payment_balance : '';
        (isset($request->commission)) ? $credentials['commission'] = $request->commission : '';
        (isset($request->commission_name)) ? $credentials['commission_name'] = $request->commission_name : '';
        (isset($request->commission_tel)) ? $credentials['commission_tel'] = $request->commission_tel : '';
        (isset($request->commission_round)) ? $credentials['commission_round'] = $request->commission_round : '';
        (isset($request->commission_bookbank)) ? $credentials['commission_bookbank'] = $request->commission_bookbank : '';
        (isset($request->commission_address)) ? $credentials['commission_address'] = $request->commission_address : '';
        (isset($request->date_noti)) ? $credentials['date_noti'] = $request->date_noti : '';
        (isset($request->date_appro)) ? $credentials['date_appro'] = $request->date_appro : '';
        (isset($request->date_check_car)) ? $credentials['date_check_car'] = $request->date_check_car : '';
        (isset($request->tex_date)) ? $credentials['tex_date'] = $request->tex_date : '';
        (isset($request->insurance_type)) ? $credentials['insurance_type'] = $request->insurance_type : '';
        // (isset($request->hear_from_type)) ? $credentials['hear_from_type'] = $request->hear_from_type : '';
        (isset($request->sign_tex)) ? $credentials['sign_tex'] = $request->sign_tex : '';
        (isset($request->act)) ? $credentials['act'] = $request->act : '';
        (isset($request->manual)) ? $credentials['manual'] = $request->manual : '';
        (isset($request->number_peel)) ? $credentials['number_peel'] = $request->number_peel : '';
        (isset($request->sign_license)) ? $credentials['sign_license'] = $request->sign_license : '';
        (isset($request->payment_bookbank)) ? $credentials['payment_bookbank'] = $request->payment_bookbank : '';
        $createBooking = Booking::create($credentials);

        $working_query = Working::find($request->working_id);
        $customer_query = Customer::find($working_query->customer_id);
        $customer_job = 'N/A';
        if ($customer_query->customer_job == 1) {
            $customer_job = 'ข้าราชการ';
        } elseif ($customer_query->customer_job == 2) {
            $customer_job = 'พนักงานเอกชน';
        } elseif ($customer_query->customer_job == 3) {
            $customer_job = 'เกษตรกร';
        } elseif ($customer_query->customer_job == 4) {
            $customer_job = 'ค้าขาย';
        } elseif ($customer_query->customer_job == 5) {
            $customer_job = $customer_query->customer_job_list;
        }
        if ($customer_job == null) {
            $customer_job = 'N/A';
        }

        if ($working_query->hear_from_type == 1) {
            $hear_from_type = 'หน้าร้าน';
        } elseif ($working_query->hear_from_type == 2) {
            $hear_from_type = 'เพจบริษัท';
        } elseif ($working_query->hear_from_type == 3) {
            $hear_from_type = 'ลูกค้าเก่าแนะนำ';
        } elseif ($working_query->hear_from_type == 4) {
            $hear_from_type = 'นายหน้า';
        } elseif ($working_query->hear_from_type == 5) {
            $hear_from_type = 'ใบปลิว';
        } elseif ($working_query->hear_from_type == 6) {
            $hear_from_type = 'Marketplace/ไลน์/เพจส่วนตัว';
        } elseif ($working_query->hear_from_type == 7) {
            $hear_from_type = 'ออนไลน์';
        } elseif ($working_query->hear_from_type == 8) {
            $hear_from_type = 'tiktok';
        } else {
            $hear_from_type = 'อื่นๆ';
        }

        $dataHistory['working_id'] = $request->working_id;
        $dataHistory['sale_name'] = $request->first_name;
        $dataHistory['branch_name'] = $credentials['branch_name'];
        $dataHistory['customer_name'] = $credentials['customer_name'];
        $dataHistory['customer_tel'] = $credentials['customer_tel'];
        $dataHistory['car_no'] = $credentials['car_no'];
        $dataHistory['booking_fee'] = $credentials['customer_bath_pledge'];
        $dataHistory['customer_job'] = $customer_job;
        $dataHistory['hear_from_type'] = $hear_from_type;
        $dataHistory['note'] = "ลงจากระบบอัตโนมัติ";
        $dataHistory['id_card'] = 'no_img.png';
        $dataHistory['booking_sheet'] = 'no_img.png';
        $dataHistory['booking_slip'] = 'no_img.png';
        $dataHistory['receipt'] = 'no_img.png';
        $dataHistory['request_booking_status'] = "approve";
        $RequestBooking = RequestBooking::create($dataHistory);

        $file_id_card = $requestFormData->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $RequestBooking->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestBooking->id_card = $filename_id_card;
        }

        $file_booking_sheet = $requestFormData->file('booking_sheet');
        if ($file_booking_sheet) {
            $filename_booking_sheet = $RequestBooking->id . '_booking_sheet.' . $file_booking_sheet->getClientOriginalExtension();
            $saveImagePath_booking_sheet = $this->path . $this->pathCurrent;
            $file_booking_sheet->move($saveImagePath_booking_sheet, $filename_booking_sheet);
            $RequestBooking->booking_sheet = $filename_booking_sheet;
        }

        $file_booking_slip = $requestFormData->file('booking_slip');
        if ($file_booking_slip) {
            $filename_booking_slip = $RequestBooking->id . '_booking_slip.' . $file_booking_slip->getClientOriginalExtension();
            $saveImagePath_booking_slip = $this->path . $this->pathCurrent;
            $file_booking_slip->move($saveImagePath_booking_slip, $filename_booking_slip);
            $RequestBooking->booking_slip = $filename_booking_slip;
        }

        $file_receipt = $requestFormData->file('receipt');
        if ($file_receipt) {
            $filename_receipt = $RequestBooking->id . '_receipt.' . $file_receipt->getClientOriginalExtension();
            $saveImagePath_receipt = $this->path . $this->pathCurrent;
            $file_receipt->move($saveImagePath_receipt, $filename_receipt);
            $RequestBooking->receipt = $filename_receipt;
        }

        $RequestBooking->save();

        if (!is_null(auth()->user())) {
            $log['user_id'] = auth()->user()->id;
        }
        $log['working_id'] = $RequestBooking['working_id'];
        $log['ref_id'] = $RequestBooking['id'];
        $log['sale_name'] = $RequestBooking['sale_name'];
        $log['branch_name'] = $RequestBooking['branch_name'];
        $log['car_no'] = $RequestBooking['car_no'];
        $log['type'] = 'การจอง';
        $log['note'] = "ลงจากระบบอัตโนมัติ";
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        $updateCustomer = Customer::find($request->customer_id);
        $updateCustomer->customer_name =  $request->customer_name;
        $updateCustomer->customer_birthday_year =  $request->customer_birthday_year;
        $updateCustomer->customer_job =  $request->customer_job;
        $updateCustomer->customer_job_list =  $request->customer_job_list;
        $updateCustomer->customer_tel =  $request->customer_tel;
        $updateCustomer->save();


        $Car = Car::find($request->car_id);
        $Car->income = Income::where([['car_id', $Car->id], ['active', 1], ['status_check', 1]])->sum('money');
        $Car->car_booking = (int)$Car->car_booking + 1;
        $Car->booking_status = 0;
        $Car->save();

        Customer_detail::where('customer_id', $request->customer_id)
            ->update([
                'customer_facebook' => $request->customer_facebook,
                'customer_line' => $request->customer_line,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'customer_address_current' => $request->customer_address_current,
                'customer_work_age' => $request->customer_work_age,
                'customer_income' => $request->customer_income,
                'customer_receive_money_type' => $request->customer_receive_money_type,
                'customer_slip_money' => $request->customer_slip_money,
                'customer_receive_evidence' => $request->customer_receive_evidence,
                'customer_land_all' => $request->customer_land_all,
                'customer_land_domin' => $request->customer_land_domin,
                'customer_land_hire' => $request->customer_land_hire,
                'customer_value_home' => $request->customer_value_home,
                'customer_value_car' => $request->customer_value_car,
                'customer_value_ete' => $request->customer_value_ete,
                'customer_installment_history' => $request->customer_installment_history,
                'customer_installment_slow' => $request->customer_installment_slow,
                'customer_installment_nmt' => $request->customer_installment_nmt,
                'customer_installment_check' => $request->customer_installment_check,
                'customer_refer_job_list' => $request->customer_refer_job_list,
                'customer_refer_name' => $request->customer_refer_name,
                'customer_refer_tel' => $request->customer_refer_tel,
                'customer_refer_related' => $request->customer_refer_related,
                'customer_refer_job' => $request->customer_refer_job,
                'customer_refer_address' => $request->customer_refer_address,
                'amphure_id' => $request->amphure_id,
                'district_id' => $request->district_id,
                'province_id' => $request->province_id,
                'zip_code' => $request->zip_code,
                'amphure_id_current' => $request->amphure_id_current,
                'district_id_current' => $request->district_id_current,
                'province_id_current' => $request->province_id_current,
                'zip_code_current' => $request->zip_code_current,
                'amphure_id_ref' => $request->amphure_id_ref,
                'district_id_ref' => $request->district_id_ref,
                'province_id_ref' => $request->province_id_ref,
                'zip_code_ref' => $request->zip_code_ref,
            ]);


        //อัพเดตสถานะของงาน
        $updateStatus = Working::find($request->working_id);
        $updateStatus->customer_name =  $request->customer_name;
        $updateStatus->price_hole_down = $request->price_hole_down;
        $updateStatus->todo_list = $request->todo_list;
        // $updateStatus->hear_from_type = $request->hear_from_type;
        $updateStatus->user_id = $requestFormData->user()->id;
        $updateStatus->work_status = 2;
        $updateStatus->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        // $booking->car;
        // return response()->json($booking);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $requestFormData, Booking $booking)
    {

        $dataInput = $requestFormData->all();
        $request =  json_decode($dataInput['formData']);
        $credentials =  (array)json_decode($dataInput['formData']);
        unset($credentials['first_name']);
        unset($credentials['action']);
        unset($credentials['updated_at']);
        $booking->update($credentials);

        $Car = Car::where('car_no', $request->car_no)->first();

        // $booking->update($request->except(['first_name', 'updated_at', 'action']));

        $working_query = Working::find($request->working_id);
        $customer_query = Customer::find($working_query->customer_id);
        $customer_job = 'N/A';
        if ($customer_query->customer_job == 1) {
            $customer_job = 'ข้าราชการ';
        } elseif ($customer_query->customer_job == 2) {
            $customer_job = 'พนักงานเอกชน';
        } elseif ($customer_query->customer_job == 3) {
            $customer_job = 'เกษตรกร';
        } elseif ($customer_query->customer_job == 4) {
            $customer_job = 'ค้าขาย';
        } elseif ($customer_query->customer_job == 5) {
            $customer_job = $customer_query->customer_job_list;
        }
        if ($customer_job == null) {
            $customer_job = 'N/A';
        }

        if ($working_query->hear_from_type == 1) {
            $hear_from_type = 'หน้าร้าน';
        } elseif ($working_query->hear_from_type == 2) {
            $hear_from_type = 'เพจบริษัท';
        } elseif ($working_query->hear_from_type == 3) {
            $hear_from_type = 'ลูกค้าเก่าแนะนำ';
        } elseif ($working_query->hear_from_type == 4) {
            $hear_from_type = 'นายหน้า';
        } elseif ($working_query->hear_from_type == 5) {
            $hear_from_type = 'ใบปลิว';
        } elseif ($working_query->hear_from_type == 6) {
            $hear_from_type = 'Marketplace/ไลน์/เพจส่วนตัว';
        } elseif ($working_query->hear_from_type == 7) {
            $hear_from_type = 'ออนไลน์';
        } elseif ($working_query->hear_from_type == 8) {
            $hear_from_type = 'tiktok';
        } else {
            $hear_from_type = 'อื่นๆ';
        }

        $dataHistory['working_id'] = $request->working_id;
        $dataHistory['sale_name'] = $request->first_name;
        $dataHistory['branch_name'] = $request->branch_name;
        $dataHistory['customer_name'] = $request->customer_name;
        $dataHistory['customer_tel'] = $request->customer_tel;
        $dataHistory['car_no'] = $request->car_no;
        if (isset($request->customer_bath_pledge)) {
            $dataHistory['booking_fee'] = $request->customer_bath_pledge;
        }
        $dataHistory['customer_job'] = $customer_job;
        $dataHistory['hear_from_type'] = $hear_from_type;
        $dataHistory['id_card'] = 'no_img.png';
        $dataHistory['booking_sheet'] = 'no_img.png';
        $dataHistory['booking_slip'] = 'no_img.png';
        $dataHistory['receipt'] = 'no_img.png';
        $dataHistory['note'] = "แก้ไขการจอง (ลงจากระบบอัตโนมัติ)";
        $dataHistory['request_booking_status'] = "approve";
        $RequestBooking = RequestBooking::create($dataHistory);

        $file_id_card = $requestFormData->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $RequestBooking->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestBooking->id_card = $filename_id_card;
        }

        $file_booking_sheet = $requestFormData->file('booking_sheet');
        if ($file_booking_sheet) {
            $filename_booking_sheet = $RequestBooking->id . '_booking_sheet.' . $file_booking_sheet->getClientOriginalExtension();
            $saveImagePath_booking_sheet = $this->path . $this->pathCurrent;
            $file_booking_sheet->move($saveImagePath_booking_sheet, $filename_booking_sheet);
            $RequestBooking->booking_sheet = $filename_booking_sheet;
        }

        $file_booking_slip = $requestFormData->file('booking_slip');
        if ($file_booking_slip) {
            $filename_booking_slip = $RequestBooking->id . '_booking_slip.' . $file_booking_slip->getClientOriginalExtension();
            $saveImagePath_booking_slip = $this->path . $this->pathCurrent;
            $file_booking_slip->move($saveImagePath_booking_slip, $filename_booking_slip);
            $RequestBooking->booking_slip = $filename_booking_slip;
        }

        $file_receipt = $requestFormData->file('receipt');
        if ($file_receipt) {
            $filename_receipt = $RequestBooking->id . '_receipt.' . $file_receipt->getClientOriginalExtension();
            $saveImagePath_receipt = $this->path . $this->pathCurrent;
            $file_receipt->move($saveImagePath_receipt, $filename_receipt);
            $RequestBooking->receipt = $filename_receipt;
        }

        $RequestBooking->save();

        $log['working_id'] = $RequestBooking['working_id'];
        $log['ref_id'] = $RequestBooking['id'];
        $log['sale_name'] = $RequestBooking['sale_name'];
        $log['branch_name'] = $RequestBooking['branch_name'];
        $log['car_no'] = $RequestBooking['car_no'];
        $log['type'] = 'การจอง';
        $log['note'] = "แก้ไขการจอง (ลงจากระบบอัตโนมัติ)";
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        $updateCustomer = Customer::find($request->customer_id);
        $updateCustomer->customer_name =  $request->customer_name;
        $updateCustomer->customer_birthday_year =  $request->customer_birthday_year;
        $updateCustomer->customer_job =  $request->customer_job;
        $updateCustomer->customer_job_list =  $request->customer_job_list;
        $updateCustomer->customer_tel =  $request->customer_tel;
        $updateCustomer->save();


        Customer_detail::where('customer_id', $request->customer_id)
            ->update([
                'customer_facebook' => $request->customer_facebook,
                'customer_line' => $request->customer_line,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'customer_address_current' => $request->customer_address_current,
                'customer_work_age' => $request->customer_work_age,
                'customer_income' => $request->customer_income,
                'customer_receive_money_type' => $request->customer_receive_money_type,
                'customer_slip_money' => $request->customer_slip_money,
                'customer_receive_evidence' => $request->customer_receive_evidence,
                'customer_land_all' => $request->customer_land_all,
                'customer_land_domin' => $request->customer_land_domin,
                'customer_land_hire' => $request->customer_land_hire,
                'customer_value_home' => $request->customer_value_home,
                'customer_value_car' => $request->customer_value_car,
                'customer_value_ete' => $request->customer_value_ete,
                'customer_installment_history' => $request->customer_installment_history,
                'customer_installment_slow' => $request->customer_installment_slow,
                'customer_installment_nmt' => $request->customer_installment_nmt,
                'customer_installment_check' => $request->customer_installment_check,
                'customer_refer_name' => $request->customer_refer_name,
                'customer_refer_tel' => $request->customer_refer_tel,
                'customer_refer_related' => $request->customer_refer_related,
                'customer_refer_job_list' => $request->customer_refer_job_list,
                'customer_refer_job' => $request->customer_refer_job,
                'customer_refer_address' => $request->customer_refer_address,
                'amphure_id' => $request->amphure_id,
                'district_id' => $request->district_id,
                'province_id' => $request->province_id,
                'zip_code' => $request->zip_code,
                'amphure_id_current' => $request->amphure_id_current,
                'district_id_current' => $request->district_id_current,
                'province_id_current' => $request->province_id_current,
                'zip_code_current' => $request->zip_code_current,
                'amphure_id_ref' => $request->amphure_id_ref,
                'district_id_ref' => $request->district_id_ref,
                'province_id_ref' => $request->province_id_ref,
                'zip_code_ref' => $request->zip_code_ref,
            ]);



        //อัพเดตสถานะของงาน
        $queryWorking = Working::find($request->working_id);
        $queryWorking->customer_name =  $request->customer_name;
        $queryWorking->customer_tel =  $request->customer_tel;
        $queryWorking->price_hole_down = $request->price_hole_down;
        $queryWorking->todo_list = $request->todo_list;
        $queryWorking->user_id = $requestFormData->user()->id;
        // $queryWorking->hear_from_type = $request->hear_from_type;
        if ($queryWorking->work_status == 1) {
            $queryWorking->work_status = 2;
        }
        $queryWorking->save();

        $Car->income = Income::where([['car_id', $Car->id], ['active', 1], ['status_check', 1]])->sum('money');
        $Car->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Booking $booking)
    {
        //
    }
    public function printBooking($idBooking)
    {
        $Booking =  Booking::find($idBooking);
        $Working = Working::select('sale_id')->find($Booking->working_id);
        $Booking->sale =  User::select('first_name', 'tel', 'branch_id')->find($Working->sale_id);
        $Booking->sale_branch =  Branch::select('branch_name')->find($Booking->sale->branch_id);

        $Booking->Amphure =  Amphure::find($Booking->amphure_id);
        $Booking->District =  District::find($Booking->district_id);
        $Booking->Province =  Province::find($Booking->province_id);


        $Booking->Amphure_current =  Amphure::find($Booking->amphure_id_current);
        $Booking->District_current =  District::find($Booking->district_id_current);
        $Booking->Province_current =  Province::find($Booking->province_id_current);


        $Booking->Amphure_ref =  Amphure::find($Booking->amphure_id_ref);
        $Booking->District_ref =  District::find($Booking->district_id_ref);
        $Booking->Province_ref =  Province::find($Booking->province_id_ref);

        return response()->json($Booking);
    }

    public function checkBooking($idWork, $idCar, $idCustomer)
    {
        $checkBooking = Booking::where('working_id', $idWork)->first();

        if (empty($checkBooking)) {
            $query = DB::table('workings')
                ->join('cars', 'workings.car_id', '=', 'cars.id')
                ->join('colors', 'cars.color_id', '=', 'colors.id')
                ->join('car_types', 'cars.car_types_id', '=', 'car_types.id')
                ->join('car_models', 'cars.car_models_id', '=', 'car_models.id')
                ->join('car_series', 'cars.car_serie_id', '=', 'car_series.id')
                ->join('car_serie_subs', 'cars.car_serie_sub_id', '=', 'car_serie_subs.id')
                ->join('users', 'workings.sale_id', '=', 'users.id')
                ->join('branch_teams', 'workings.branch_team_id', '=', 'branch_teams.id')
                // ->join('branches', 'cars.branch_id', '=', 'branches.id')
                ->join('customers', 'workings.customer_id', '=', 'customers.id')
                ->join('customer_details', 'customers.id', '=', 'customer_details.customer_id')
                ->where('workings.id', $idWork)
                ->first();

            $query->hear_from_type = '1';
            $query->sign_tex = '1';
            $query->act = '1';
            $query->manual = '1';
            $query->number_peel = '1';
            $query->sign_license = '1';

            $query->branch_name = $query->branch_team_name;


            $query->working_id = $idWork;
            $query->customer_id = $idCustomer;
            $query->created_at = date('Y-m-d H:i:s');


            $query->action = "add";
        } else {
            $query = DB::table('bookings')
                ->where('working_id', $idWork)
                ->first();
            $Working = Working::find($idWork);
            $user = User::find($Working->sale_id);
            $branch_team = Branch_team::find($Working->branch_team_id);
            $query->branch_name =  $branch_team->branch_team_name;
            $query->first_name = $user->first_name;
            $query->action = "edit";
        }
        return response()->json($query);
    }
}
