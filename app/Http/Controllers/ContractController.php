<?php

namespace App\Http\Controllers;

use App\Models\Amphure;
use App\Models\Booking;
use App\Models\Branch_team;
use App\Models\Car;
use App\Models\Contract;
use App\Models\District;
use App\Models\Image_car;
use App\Models\Income;
use App\Models\Province;
use App\Models\RequestLog;
use App\Models\RequestRelease;
use App\Models\User;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class ContractController extends Controller
{
    protected $pathCurrent = 'contracts/';
    protected $pathCurrent_car = 'cars/';
    protected $pathCurrent_log = 'request_release/';

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
    public function store(Request $request)
    {
        $credentials = (array) json_decode($request->formData);
        // return response()->json($credentials['working_id']);
        $crateContract = Contract::create([
            'working_id' => $credentials['working_id'],
            'car_id' => $credentials['car_id'],
            'contract_date' => isset($credentials['contract_date']) == false ? null : $credentials['contract_date'],
            'contract_at' => isset($credentials['contract_at']) == false ? null : $credentials['contract_at'],
            'car_regis' => isset($credentials['car_regis']) == false ? null : $credentials['car_regis'],
            'car_no_engine' => isset($credentials['car_no_engine']) == false ? null : $credentials['car_no_engine'],
            'car_no_body' => isset($credentials['car_no_body']) == false ? null : $credentials['car_no_body'],
            'car_year' => isset($credentials['car_year']) == false ? null : $credentials['car_year'],
            'color_name' => isset($credentials['color_name']) == false ? null : $credentials['color_name'],
            'car_model_name' => isset($credentials['car_model_name']) == false ? null : $credentials['car_model_name'],
            'customer_name' => isset($credentials['customer_name']) == false ? null : $credentials['customer_name'],
            'customer_address' => isset($credentials['customer_address']) == false ? null : $credentials['customer_address'],
            'amphure_id' => isset($credentials['amphure_id']) == false ? null : $credentials['amphure_id'],
            'district_id' => isset($credentials['district_id']) == false ? null : $credentials['district_id'],
            'province_id' => isset($credentials['province_id']) == false ? null : $credentials['province_id'],
            'zip_code' => isset($credentials['zip_code']) == false ? null : $credentials['zip_code'],
            'customer_birthday_year' => isset($credentials['customer_birthday_year']) == false ? null : $credentials['customer_birthday_year'],
            'customer_tel' => isset($credentials['customer_tel']) == false ? null : $credentials['customer_tel'],
            'credit' => isset($credentials['credit']) == false ? null : $credentials['credit'],
            'credit_no' => isset($credentials['credit_no']) == false ? null : $credentials['credit_no'],
            'credit_address' => isset($credentials['credit_address']) == false ? null : $credentials['credit_address'],
            'contract_bath' => isset($credentials['contract_bath']) == false ? null : $credentials['contract_bath'],
            'advance_payment' => $credentials['advance_payment'],
            'insurance' => $credentials['insurance'],
            'insurance_other' => $credentials['insurance_other'],
            'other_receive' => $credentials['other_receive'],
            'total_receive' => $credentials['total_receive'],
            'contract_bath_string' => isset($credentials['contract_bath_string']) == false ? null : $credentials['contract_bath_string'],
            'contract_bath_deposit' => isset($credentials['contract_bath_deposit']) == false ? null : $credentials['contract_bath_deposit'],
            'contract_bath_deposit_string' => isset($credentials['contract_bath_deposit_string']) == false ? null : $credentials['contract_bath_deposit_string'],
            'contract_bath_hold' => isset($credentials['contract_bath_hold']) == false ? null : $credentials['contract_bath_hold'],
            'contract_bath_hold_string' => isset($credentials['contract_bath_hold_string']) == false ? null : $credentials['contract_bath_hold_string'],
            'date_bath_Refund' => isset($credentials['date_bath_Refund']) == false ? null : $credentials['date_bath_Refund'],
            'contract_note' => isset($credentials['contract_note']) == false ? null : $credentials['contract_note'],
        ]);

        $working = Working::find($credentials['working_id']);
        $sale = User::find($working->sale_id);
        $Branch_team = Branch_team::find($working->branch_team_id);
        $car = Car::find($working->car_id);

        $dataHistory['working_id'] = $credentials['working_id'];
        $dataHistory['sale_name'] = $sale->first_name;
        $dataHistory['branch_name'] = $Branch_team->branch_team_name;
        $dataHistory['car_no'] = $car->car_no;

        $dataHistory['release_date'] = $credentials['contract_date'];
        $dataHistory['dow'] = $credentials['contract_bath'];
        $dataHistory['advance_payment'] = $credentials['advance_payment'];
        $dataHistory['insurance'] = $credentials['insurance'];
        $dataHistory['insurance_other'] = $credentials['insurance_other'];
        $dataHistory['other_receive'] = $credentials['other_receive'];
        $dataHistory['total_receive'] = $credentials['total_receive'];

        $credentials['id_card'] = 'no_img.png';
        $credentials['release_img'] = 'no_img.png';
        $credentials['sale_sheet'] = 'no_img.png';
        $credentials['insurance_font_sheet'] = 'no_img.png';
        $credentials['insurance_back_sheet'] = 'no_img.png';
        $credentials['receipt'] = 'no_img.png';
        $credentials['slip'] = 'no_img.png';
        $credentials['ImageCar'] = 'no_img.png';

        $dataHistory['note'] = "ลงจากระบบอัตโนมัติ";
        $dataHistory['request_status'] = "approve";
        $RequestHistory = RequestRelease::create($dataHistory);

        if (!is_null(auth()->user())) {
            $log['user_id'] = auth()->user()->id;
        }
        $log['working_id'] = $RequestHistory['working_id'];
        $log['ref_id'] = $RequestHistory['id'];
        $log['sale_name'] = $RequestHistory['sale_name'];
        $log['branch_name'] = $RequestHistory['branch_name'];
        $log['car_no'] = $RequestHistory['car_no'];
        $log['type'] = 'ปล่อยรถ';
        $log['note'] = "ลงจากระบบอัตโนมัติ";
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        $path_fileContract = null;
        if ($request->hasFile('Image')) {
            if (!File::exists($this->path . $this->pathCurrent . $crateContract->id)) {
                if (File::makeDirectory($this->path . $this->pathCurrent . $crateContract->id, 0775, true)) {
                    $file = $request->file('Image');
                    $filename = $crateContract->id . '.png';
                    $saveImagePath = $this->path . $this->pathCurrent . $crateContract->id;
                    $resize = Image::make($file)->resize(800, 600, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $resize->save($this->temp . $filename, 100);
                    $file->move($saveImagePath, $filename);
                    $path_fileContract = $saveImagePath . '/' . $filename;

                    File::delete($this->temp . $filename);
                    $update = Contract::find($crateContract->id);
                    $update->contract_image = $filename;
                    $update->save();
                }
            } else {
                $file = $request->file('Image');
                $filename = $crateContract->id . '.png';
                $saveImagePath = $this->path . $this->pathCurrent . $crateContract->id;
                $resize = Image::make($file)->resize(800, 600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                $path_fileContract = $saveImagePath . '/' . $filename;

                File::delete($this->temp . $filename);
                $update = Contract::find($crateContract->id);
                $update->contract_image = $filename;
                $update->save();
            }
        }


        // //อัพเดตสถานะของงาน
        $updateStatus = Working::find($crateContract->working_id);
        $updateStatus->user_id = $request->user()->id;
        $updateStatus->work_status = 8;
        $updateStatus->save();

        $booking = Booking::where('working_id', $crateContract->working_id)->first();

        if (!empty($booking)) {
            $booking->customer_car_date_release = $crateContract->contract_date;
            $booking->save();
        }

        $Car = Car::find($updateStatus->car_id);


        if (!is_null($path_fileContract)) {
            $carImg = Image_car::create();
            $filename = $carImg->id  . '_out.png';
            File::copy($path_fileContract, $this->path . $this->pathCurrent_car . $Car->id . '/' . $filename);
            $Image_car = Image_car::find($carImg->id);
            $Image_car->image_name = $filename;
            $Image_car->car_id = $Car->id;
            $Image_car->save();

            $before_firsts = Image_car::where([['car_id', $Car->id]])->get();
            if (count($before_firsts) != 0) {
                foreach ($before_firsts as $key => $before_first) {
                    $updateBefore_first = Image_car::find($before_first->id);
                    $updateBefore_first->img_first = 0;
                    $updateBefore_first->save();
                }
            }

            $after_first = Image_car::find($carImg->id);
            $after_first->img_first = 1;
            $after_first->save();
            $Car->img_id_first = $carImg->id;
        }

        if (!empty($Car)) {
            if ($Car->car_stock != 3) {
                $Car->car_stock = 3;
            }
        }
        $Car->save();


        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent_log;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestHistory->id_card = $filename_id_card;
        }

        $file_release_img = $request->file('Image');
        if ($file_release_img) {
            $filename_release_img = $RequestHistory->id . '_release_img.' . $file_release_img->getClientOriginalExtension();
            File::copy($path_fileContract, $this->path . $this->pathCurrent_log . $filename_release_img);
            $RequestHistory->release_img = $filename_release_img;
        }

        $file_imageCar = $request->file('ImageCar');
        if ($file_imageCar) {
            $filename_id_card = $RequestHistory->id . '_imageCar.' . $file_imageCar->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent_log;
            $file_imageCar->move($saveImagePath_id_card, $filename_id_card);
            $RequestHistory->ImageCar = $filename_id_card;
        }


        $file_sale_sheet = $request->file('sale_sheet');
        if ($file_sale_sheet) {
            $filename_sale_sheet = $RequestHistory->id . '_sale_sheet.' . $file_sale_sheet->getClientOriginalExtension();
            $saveImagePath_sale_sheet = $this->path . $this->pathCurrent_log;
            $file_sale_sheet->move($saveImagePath_sale_sheet, $filename_sale_sheet);
            $RequestHistory->sale_sheet = $filename_sale_sheet;
        }

        $file_insurance_font_sheet = $request->file('insurance_font_sheet');
        if ($file_insurance_font_sheet) {
            $filename_insurance_font_sheet = $RequestHistory->id . '_insurance_font_sheet.' . $file_insurance_font_sheet->getClientOriginalExtension();
            $saveImagePath_insurance_font_sheet = $this->path . $this->pathCurrent_log;
            $file_insurance_font_sheet->move($saveImagePath_insurance_font_sheet, $filename_insurance_font_sheet);
            $RequestHistory->insurance_font_sheet = $filename_insurance_font_sheet;
        }

        $file_insurance_back_sheet = $request->file('insurance_back_sheet');
        if ($file_insurance_back_sheet) {
            $filename_insurance_back_sheet = $RequestHistory->id . '_insurance_back_sheet.' . $file_insurance_back_sheet->getClientOriginalExtension();
            $saveImagePath_insurance_back_sheet = $this->path . $this->pathCurrent_log;
            $file_insurance_back_sheet->move($saveImagePath_insurance_back_sheet, $filename_insurance_back_sheet);
            $RequestHistory->insurance_back_sheet = $filename_insurance_back_sheet;
        }

        $file_receipt = $request->file('receipt');
        if ($file_receipt) {
            $filename_receipt = $RequestHistory->id . '_receipt.' . $file_receipt->getClientOriginalExtension();
            $saveImagePath_receipt = $this->path . $this->pathCurrent_log;
            $file_receipt->move($saveImagePath_receipt, $filename_receipt);
            $RequestHistory->receipt = $filename_receipt;
        }

        $file_slip = $request->file('slip');
        if ($file_slip) {
            $filename_slip = $RequestHistory->id . '_slip.' . $file_slip->getClientOriginalExtension();
            $saveImagePath_slip = $this->path . $this->pathCurrent_log;
            $file_slip->move($saveImagePath_slip, $filename_slip);
            $RequestHistory->slip = $filename_slip;
        }

        $RequestHistory->save();

        return response()->json("ok");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function show(Contract $contract)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function edit(Contract $contract)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contract $contract)
    {
        // $contract->update($request->except(['updated_at', 'action']));

        $credentials = (array) json_decode($request->formData);
        unset($credentials['updated_at']);
        unset($credentials['action']);
        $contract->update($credentials);

        $path_fileContract = null;
        $check = 0;
        if ($request->hasFile('Image')) {
            if (!File::exists($this->path . $this->pathCurrent . $credentials['id'])) {
                if (File::makeDirectory($this->path . $this->pathCurrent . $credentials['id'], 0775, true)) {
                    $file = $request->file('Image');

                    $filename = $credentials['id'] . '.png';
                    $saveImagePath = $this->path . $this->pathCurrent . $credentials['id'];
                    $resize = Image::make($file)->resize(800, 600, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $resize->save($this->temp . $filename, 100);
                    $file->move($saveImagePath, $filename);
                    $path_fileContract = $saveImagePath . '/' . $filename;
                    File::delete($this->temp . $filename);
                    $update = Contract::find($credentials['id']);
                    $update->contract_image = $filename;
                    $update->save();
                }
            } else {
                $file = $request->file('Image');

                $filename = $credentials['id'] . '.png';
                $saveImagePath = $this->path . $this->pathCurrent . $credentials['id'];
                $resize = Image::make($file)->resize(800, 600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                $path_fileContract = $saveImagePath . '/' . $filename;
                File::delete($this->temp . $filename);
                $update = Contract::find($credentials['id']);
                $update->contract_image = $filename;
                $update->save();
            }
        }

        $updateStatus = Working::find($credentials['working_id']);
        $updateStatus->user_id = $request->user()->id;
        if ($updateStatus->work_status < 8) {
            $updateStatus->work_status = 8;
        }
        $updateStatus->save();

        $booking = Booking::where('working_id', $contract['working_id'])->first();
        if (!empty($booking)) {
            $booking->customer_car_date_release = $credentials['contract_date'];
            $booking->save();
        }



        if (!is_null($path_fileContract)) {
            $Car = Car::find($updateStatus->car_id);
            $carImg = Image_car::create();
            $filename = $carImg->id  . '_out.png';
            File::copy($path_fileContract, $this->path . $this->pathCurrent_car . $Car->id . '/' . $filename);
            $Image_car = Image_car::find($carImg->id);
            $Image_car->car_id = $Car->id;
            $Image_car->image_name = $filename;
            $Image_car->save();
            $before_firsts = Image_car::where([['car_id', $Car->id]])->get();
            if (count($before_firsts) != 0) {
                foreach ($before_firsts as $key => $before_first) {
                    $updateBefore_first = Image_car::find($before_first->id);
                    $updateBefore_first->img_first = 0;
                    $updateBefore_first->save();
                }
            }
            $after_first = Image_car::find($carImg->id);
            $after_first->img_first = 1;
            $after_first->save();

            $Car->img_id_first = $carImg->id;
            if (!empty($Car)) {
                if ($Car->car_stock != 3) {
                    $Car->car_stock = 3;
                }
            }
            $Car->save();
        }

        $working = Working::find($credentials['working_id']);
        $sale = User::find($working->sale_id);
        $Branch_team = Branch_team::find($working->branch_team_id);
        $car = Car::find($working->car_id);

        $dataHistory['working_id'] = $credentials['working_id'];
        $dataHistory['sale_name'] = $sale->first_name;
        $dataHistory['branch_name'] = $Branch_team->branch_team_name;
        $dataHistory['car_no'] = $car->car_no;

        $dataHistory['release_date'] = $credentials['contract_date'];
        $dataHistory['dow'] = $credentials['contract_bath'];
        $dataHistory['advance_payment'] = $credentials['advance_payment'];
        $dataHistory['insurance'] = $credentials['insurance'];
        $dataHistory['insurance_other'] = $credentials['insurance_other'];
        $dataHistory['other_receive'] = $credentials['other_receive'];
        $dataHistory['total_receive'] = $credentials['total_receive'];

        $credentials['id_card'] = 'no_img.png';
        $credentials['release_img'] = 'no_img.png';
        $credentials['sale_sheet'] = 'no_img.png';
        $credentials['insurance_font_sheet'] = 'no_img.png';
        $credentials['insurance_back_sheet'] = 'no_img.png';
        $credentials['receipt'] = 'no_img.png';
        $credentials['slip'] = 'no_img.png';
        $credentials['ImageCar'] = 'no_img.png';

        $dataHistory['note'] = "แก้ไข (ลงจากระบบอัตโนมัติ)";
        $dataHistory['request_status'] = "approve";
        $RequestHistory = RequestRelease::create($dataHistory);

        if (!is_null(auth()->user())) {
            $log['user_id'] = auth()->user()->id;
        }
        $log['working_id'] = $RequestHistory['working_id'];
        $log['ref_id'] = $RequestHistory['id'];
        $log['sale_name'] = $RequestHistory['sale_name'];
        $log['branch_name'] = $RequestHistory['branch_name'];
        $log['car_no'] = $RequestHistory['car_no'];
        $log['type'] = 'ปล่อยรถ';
        $log['note'] = "แก้ไข (ลงจากระบบอัตโนมัติ)";
        $log['request_status'] = 'pedding';
        RequestLog::create($log);

        $file_id_card = $request->file('id_card');
        if ($file_id_card) {
            $filename_id_card = $RequestHistory->id . '_id_card.' . $file_id_card->getClientOriginalExtension();
            $saveImagePath_id_card = $this->path . $this->pathCurrent_log;
            $file_id_card->move($saveImagePath_id_card, $filename_id_card);
            $RequestHistory->id_card = $filename_id_card;
        }

        $file_release_img = $request->file('Image');
        if ($file_release_img) {
            $filename_release_img = $RequestHistory->id . '_release_img.' . $file_release_img->getClientOriginalExtension();
            File::copy($path_fileContract, $this->path . $this->pathCurrent_log . $filename_release_img);
            $RequestHistory->release_img = $filename_release_img;
        }


        $file_ImageCar = $request->file('ImageCar');
        if ($file_ImageCar) {
            $filename_ImageCar = $RequestHistory->id . '_ImageCar.' . $file_ImageCar->getClientOriginalExtension();
            File::copy($path_fileContract, $this->path . $this->pathCurrent_log . $filename_ImageCar);
            $RequestHistory->ImageCar = $filename_ImageCar;
        }

        $file_sale_sheet = $request->file('sale_sheet');
        if ($file_sale_sheet) {
            $filename_sale_sheet = $RequestHistory->id . '_sale_sheet.' . $file_sale_sheet->getClientOriginalExtension();
            $saveImagePath_sale_sheet = $this->path . $this->pathCurrent_log;
            $file_sale_sheet->move($saveImagePath_sale_sheet, $filename_sale_sheet);
            $RequestHistory->sale_sheet = $filename_sale_sheet;
        }

        $file_insurance_font_sheet = $request->file('insurance_font_sheet');
        if ($file_insurance_font_sheet) {
            $filename_insurance_font_sheet = $RequestHistory->id . '_insurance_font_sheet.' . $file_insurance_font_sheet->getClientOriginalExtension();
            $saveImagePath_insurance_font_sheet = $this->path . $this->pathCurrent_log;
            $file_insurance_font_sheet->move($saveImagePath_insurance_font_sheet, $filename_insurance_font_sheet);
            $RequestHistory->insurance_font_sheet = $filename_insurance_font_sheet;
        }

        $file_insurance_back_sheet = $request->file('insurance_back_sheet');
        if ($file_insurance_back_sheet) {
            $filename_insurance_back_sheet = $RequestHistory->id . '_insurance_back_sheet.' . $file_insurance_back_sheet->getClientOriginalExtension();
            $saveImagePath_insurance_back_sheet = $this->path . $this->pathCurrent_log;
            $file_insurance_back_sheet->move($saveImagePath_insurance_back_sheet, $filename_insurance_back_sheet);
            $RequestHistory->insurance_back_sheet = $filename_insurance_back_sheet;
        }

        $file_receipt = $request->file('receipt');
        if ($file_receipt) {
            $filename_receipt = $RequestHistory->id . '_receipt.' . $file_receipt->getClientOriginalExtension();
            $saveImagePath_receipt = $this->path . $this->pathCurrent_log;
            $file_receipt->move($saveImagePath_receipt, $filename_receipt);
            $RequestHistory->receipt = $filename_receipt;
        }

        $file_slip = $request->file('slip');
        if ($file_slip) {
            $filename_slip = $RequestHistory->id . '_slip.' . $file_slip->getClientOriginalExtension();
            $saveImagePath_slip = $this->path . $this->pathCurrent_log;
            $file_slip->move($saveImagePath_slip, $filename_slip);
            $RequestHistory->slip = $filename_slip;
        }

        $RequestHistory->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contract $contract)
    {
        //
    }


    public function printContract($idContract)
    {
        $Contract =  Contract::find($idContract);

        $Contract->Amphure =  Amphure::find($Contract->amphure_id);
        $Contract->District =  District::find($Contract->district_id);
        $Contract->Province =  Province::find($Contract->province_id);

        return response()->json($Contract);
    }


    public function checkContract($idWork, $idCar, $idCustomer)
    {
        $checkContract = Contract::where('working_id', $idWork)->first();

        if (empty($checkContract)) {
            $query = DB::table('workings')
                ->join('cars', 'workings.car_id', '=', 'cars.id')
                ->join('colors', 'cars.color_id', '=', 'colors.id')
                ->join('car_types', 'cars.car_types_id', '=', 'car_types.id')
                ->join('car_models', 'cars.car_models_id', '=', 'car_models.id')
                ->join('car_series', 'cars.car_serie_id', '=', 'car_series.id')
                ->join('car_serie_subs', 'cars.car_serie_sub_id', '=', 'car_serie_subs.id')
                // ->join('branches', 'cars.branch_id', '=', 'branches.id')
                ->join('customers', 'workings.customer_id', '=', 'customers.id')
                ->join('customer_details', 'customers.id', '=', 'customer_details.customer_id')
                ->where('cars.id', $idCar)
                ->where('customers.id', $idCustomer)
                ->where('workings.id', $idWork)
                ->first();
            $query->contract_bath_hold = 0;
            $query->contract_bath_deposit = 0;
            $query->contract_bath = 0;

            $query->working_id = $idWork;
            $query->action = "add";
        } else {
            $query = DB::table('contracts')
                ->where('working_id', $idWork)
                ->first();
            $query->action = "edit";
        }
        return response()->json($query);
    }
}
