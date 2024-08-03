<?php

namespace App\Http\Controllers;

use App\Models\Amount_down;
use App\Models\Amount_slacken;
use App\Models\Appointment;
use App\Models\Appointment_bank;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Car;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\File_car;
use App\Models\Image_car;
use App\Models\Income;
use App\Models\Middle_price;
use App\Models\Middle_price_detail;
use App\Models\Outlay_cost;
use App\Models\Partner_car;
use App\Models\Province;
use App\Models\Receiving_money;
use App\Models\User;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class CarController extends Controller
{

    protected $pathCurrent = 'cars/';


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = Car::select(

            'fuel_type',
            'car_mark_year',
            'car_mileage_balance',
            'car_no_engine',
            'car_no_body',
            'id',
            'car_no',
            'car_types_id',
            'car_models_id',
            'car_serie_id',
            'car_serie_sub_id',
            'partner_car_id',
            'province_id',
            'branch_province_id',
            'color_id',
            'fuel_id',
            'province_id_current',
            'user_id',
            'branch_id',

            'car_engine_amount',
            'car_mileage',
            'img_id_first',
            'booking_status',
            'car_gear',
            'car_year',
            'car_regis',
            'amount_price',
            'amount_down',
            'car_price_vat',
            'net_price',
            'car_fix',
            'car_active',
            'car_stock',
            'car_active',
            'car_date_buy',
            'car_regis_current',
            'car_status',
        )
            ->with([
                'car_types',
                'car_models',
                'car_series',
                'car_serie_sub',
                'branch',
                'color'
            ])->where([['car_stock', 1]])->orderBy('car_no', 'desc')->get();

        // $img = Image_car::where('car_id', $output->id)->first();
        $map = $output->map(function ($items) {
            if ($items['img_id_first'] == 0) {
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent  . 'default/car_default.png';
            } else {
                $Image_car =  Image_car::find($items['img_id_first']);
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent . $items['id'] . '/' . $Image_car->image_name;
            }


            if ($items['province_id'] != null) {
                $items['provinces'] = Province::select('name_th')
                    ->find($items['province_id']);
            }
            if ($items['province_id_current'] != null) {
                $items['province_new'] = Province::select('name_th')
                    ->find($items['province_id_current']);
            }

            return $items;
        });
        return response()->json($map);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // table car
        $credentials = $request->except(['id', 'imageCars', 'folder']);

        if (!empty($credentials['car_no'])) {
            $check_car_no = Car::where([['car_no', $credentials['car_no']]])->first();
            if (!empty($check_car_no)) {
                return response()->json('car_no');
            }
        }

        $check_car_regis = Car::where([['car_regis', $credentials['car_regis'],  ['province_id', $credentials['province_id']]]])->first();
        if (!empty($check_car_regis)) {
            return response()->json('car_regis');
        }

        if (empty($check_car_regis) && empty($check_car_no)) {

            $partner_cars = DB::table('partner_cars')->where('id', $request->partner_car_id)->first();

            if (empty($credentials['car_no'])) {
                $prefix = DB::table('prefix')->where('id', 1)->first();
                if ($prefix->year != date("y")) {
                    $prefix->year = date("y");
                    $prefix->car_no = -1;
                }

                if ($prefix->month != date("m")) {
                    $prefix->month = date("m");
                    $prefix->car_no = -1;
                }

                $car_no_int = (int)$prefix->car_no + 1;

                if ($partner_cars->partner_car_type == 1) {
                    $car_no = $prefix->year . $prefix->month . str_pad($car_no_int, 3, '0', STR_PAD_LEFT) . 'B';
                } else {
                    $car_no = $prefix->year . $prefix->month . str_pad($car_no_int, 3, '0', STR_PAD_LEFT);
                }


                $credentials['car_no'] = $car_no;

                DB::table('prefix')
                    ->where('id', 1)
                    ->update(['year' => $prefix->year, 'month' => $prefix->month, 'car_no' => str_pad($car_no_int, 3, '0', STR_PAD_LEFT)]);
            }

            if (count($request->imageCars) != 0) {
                $credentials['img_id_first'] = (int)$request->imageCars[0];
            }

            if ($credentials['car_year'] != 9999) {
                $Middle_price = Middle_price::where([
                    ['car_serie_id', $credentials['car_serie_id']],
                    ['car_serie_sub_id', $credentials['car_serie_sub_id']],
                    ['car_gear', $credentials['car_gear']],
                    ['year', $credentials['car_year']]
                ])->first();
                if (!empty($Middle_price)) {
                    if (!empty($Middle_price_detail)) {
                        $Middle_price_detail =  Middle_price_detail::where([
                            ['bank_id', $Middle_price->selected],
                            ['middle_price_id', $Middle_price->id],
                        ])->first();
                        $credentials['amount_price'] = $Middle_price_detail->amount_price;
                        $credentials['amount_price_vat'] = 1.07 * (float)$Middle_price_detail->amount_price;
                        $credentials['car_price_vat'] = (float)$Middle_price_detail->amount_price + (((float) $credentials['amount_down'] + 20000));
                        $credentials['car_price_plus'] = (float)$Middle_price_detail->middle_plus;
                        $credentials['car_price_multiply'] = (float)$Middle_price_detail->middle_multiply;
                    }
                }
            }

            if (empty($credentials['net_price']) || $credentials['net_price'] == 0) {
                $credentials['net_price'] = $credentials['VatSumOverCos'];
            }

            $carCarate = Car::create($credentials);

            if ($carCarate->net_price != null || $carCarate->net_price != 0) {
                $check_Outlay_cost = Outlay_cost::where([['car_id', $carCarate->id], ['detail', 'ค่าตัวรถ']])->first();
                if (empty($check_Outlay_cost)) {
                    $Outlay_cost =   Outlay_cost::create([
                        'date' => $carCarate->car_date_buy,
                        'no' => '',
                        'detail' => 'ค่าตัวรถ',
                        'type' => 1,
                        'type_bill' => 1,
                        'money' => $carCarate->VatSumOverCos,
                        'car_id' => $carCarate->id,
                        'user_id' => $request->user()->id,
                        'status_check' => 1,
                        'active' => 1,
                        'branch_id' =>  $carCarate->branch_id,
                    ]);
                }
            }



            if ($request->folder != null) {
                if (File::exists($this->path . $this->pathCurrent  . $request->folder)) {
                    File::move($this->path . $this->pathCurrent  . $request->folder, $this->path . $this->pathCurrent . $carCarate->id);

                    foreach ($request->imageCars as $key => $imageCar) {
                        if (isset($request->imageCars[$key]['id'])) {
                            $updateImage_car = Image_car::find($imageCar['id']);
                            if ($imageCar['id'] == $request->imageCars[0]) {
                                $updateImage_car->img_first = 1;
                            }
                            $updateImage_car->car_id = $carCarate->id;
                            $updateImage_car->save();
                        } else {
                            $updateImage_car = Image_car::find($imageCar);
                            if ($imageCar == $request->imageCars[0]) {
                                $updateImage_car->img_first = 1;
                            }
                            $updateImage_car->car_id = $carCarate->id;
                            $updateImage_car->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Car  $Car
     * @return \Illuminate\Http\Response
     */
    public function show(Car $Car)
    {
        // $Car->imageCars = Image_car::where('car_id', $Car->id)->orderBy('img_first', 'DESC')->get();

        // $car_file = File_car::with('user')->where('car_id', $Car->id)->get();
        // $map = $car_file->map(function ($items) {
        //     $items['file_name'] = ''  . $items['car_id'] . '/' . $items['file_name'];
        //     return $items;
        // });

        // $Car->file_cars = $map;

        // return response()->json($Car);
    }
    public function showCar(Request $request)
    {
        $Car = Car::find($request->id);
        // if ($request->user_group_permission == '-1' || $request->user_group_permission == '8') {
        // } else {
        //     $Car->car_buy_vat = 0;
        //     $Car->net_price = 0;
        //     $Car->car_buy = 0;
        //     $Car->amount_price = 0;
        //     $Car->amount_price_vat = 0;
        //     $Car->car_price_plus = 0;
        //     $Car->VatSumOverCos = 0;
        //     $Car->car_price_multiply = 0;
        // }

        $Car->imageCars = Image_car::where('car_id', $Car->id)->orderBy('img_first', 'DESC')->get();

        $car_file = File_car::with('user')->where('car_id', $Car->id)->get();
        $map = $car_file->map(function ($items) {
            $items['file_name'] = ''  . $items['car_id'] . '/' . $items['file_name'];
            return $items;
        });

        $Car->file_cars = $map;

        return response()->json($Car);


        // return response()->json($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Car  $Car
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Car $Car)
    {
        // if ($request['car_buy_vat'] != 0) {
        // } else {
        //     $request['car_buy_vat'] = $Car->car_buy_vat;
        // }

        // if ($request['net_price'] != 0) {
        // } else {
        //     $request['net_price'] = $Car->net_price;
        // }

        // if ($request['car_buy'] != 0) {
        // } else {
        //     $request['car_buy'] = $Car->car_buy;
        // }

        // if ($request['VatSumOverCos'] != 0) {
        // } else {
        //     $request['VatSumOverCos'] = $Car->VatSumOverCos;
        // }
        // if ($request['amount_price'] != 0) {
        // } else {
        //     $request['amount_price'] = $Car->amount_price;
        // }
        // if ($request['amount_price_vat'] != 0) {
        // } else {
        //     $request['amount_price_vat'] = $Car->amount_price_vat;
        // }


        // if ($request['car_price_plus'] != 0) {
        // } else {
        //     $request['car_price_plus'] = $Car->car_price_plus;
        // }
        // if ($request['car_price_multiply'] != 0) {
        // } else {
        //     $request['car_price_multiply'] = $Car->car_price_multiply;
        // }
        // if ($request['income'] != 0) {
        // } else {
        //     $request['income'] = $Car->income;
        // }


        if ($request['car_year'] != 9999) {
            $Middle_price = Middle_price::where([
                ['car_serie_id', $request['car_serie_id']],
                ['car_serie_sub_id', $request['car_serie_sub_id']],
                ['car_gear', $request['car_gear']],
                ['year', $request['car_year']]
            ])->first();
            if (!empty($Middle_price)) {
                $Middle_price_detail =  Middle_price_detail::where([
                    ['bank_id', $Middle_price->selected],
                    ['middle_price_id', $Middle_price->id],
                ])->first();
                if (!empty($Middle_price_detail)) {
                    $request['amount_price'] = $Middle_price_detail->amount_price;
                    $request['amount_price_vat'] = (7 / 100) * (float)$Middle_price_detail->amount_price;
                    $request['car_price_vat'] = (float)$Middle_price_detail->amount_price + (((float) $request['amount_down'] + 20000));
                    $request['car_price_plus'] = (float)$Middle_price_detail->middle_plus;
                    $request['car_price_multiply'] = (float)$Middle_price_detail->middle_multiply;
                }
            }
        }

        // if (empty($request['net_price']) || $request['net_price'] == 0) {
        //     $request['net_price'] = $request['VatSumOverCos'];
        // }


        $check_Outlay_cost = Outlay_cost::where([['car_id', $request['id']], ['detail', 'ค่าตัวรถ']])->first();
        if (empty($check_Outlay_cost)) {
            $Outlay_cost =   Outlay_cost::create([
                'date' => $request['car_date_buy'],
                'no' => '',
                'detail' => 'ค่าตัวรถ',
                'type' => 1,
                'type_bill' => 1,
                'money' => $request['VatSumOverCos'],
                'car_id' => $request['id'],
                'user_id' => $request->user()->id,
                'status_check' => 1,
                'active' => 1,
                'branch_id' =>  $request['branch_id'],
            ]);
            $request['net_price'] = (float)$request['net_price'] + (float)$request['VatSumOverCos'];
        } else {

            if ($check_Outlay_cost->money != $request['VatSumOverCos']) {
                $check_Outlay_cost->status_check =  1;
                $check_Outlay_cost->active =  1;
                $check_Outlay_cost->user_id =  $request->user()->id;
                $check_Outlay_cost->money = $request['VatSumOverCos'];
                $check_Outlay_cost->save();
            }
        }

        $update_net_price = Outlay_cost::where([['car_id', $request['id']], ['active', 1], ['status_check', 1]])->sum('money');
        // if ($update_net_price != $request['net_price']) {
        $request['net_price']  = $update_net_price;
        // }

        $update_expenses = Outlay_cost::where([['car_id', $request['id']], ['detail', 'ค่าตัวรถ'], ['active', 1], ['status_check', 1]])->sum('money');
        // if ($update_expenses != $request['expenses']) {
        $request['expenses']  = (float)$update_net_price - (float)$update_expenses;
        // }

        $income = Income::where([['car_id', $request['id']], ['active', 1], ['status_check', 1]])->sum('money');
        // if ($income != $request['income']) {
        $request['income']  = $income;
        // }
        // $request['user_id'] = $request->user()->id;
        $Car->update($request->except(['updated_at',  'imageCars', 'folder', 'img_id_first', 'file_cars']));
        // // table transiton car



        if ($request->folder != null) {
            if (File::exists($this->path . $this->pathCurrent  . $request->folder)) {
                File::move($this->path . $this->pathCurrent  . $request->folder, $this->path . $this->pathCurrent . $Car->id);

                foreach ($request->imageCars as $key => $imageCar) {
                    if (isset($request->imageCars[$key]['id'])) {
                        $updateImage_car = Image_car::find($imageCar['id']);
                        if ($imageCar['id'] == $request->imageCars[0]) {
                            $updateImage_car->img_first = 1;
                        }
                        $updateImage_car->car_id = $Car->id;
                        $updateImage_car->save();
                    } else {
                        $updateImage_car = Image_car::find($imageCar);
                        if ($imageCar == $request->imageCars[0]) {
                            $updateImage_car->img_first = 1;
                        }
                        $updateImage_car->car_id = $Car->id;
                        $updateImage_car->save();
                    }
                }
            }
        }
        return response()->json($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Car  $Car
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car $Car)
    {

        $Car->car_status = 0;
        $Car->save();
        // Car::where('id', $Car->id)->update(array(
        //     'car_status' => 0,
        // ));
    }

    public function reRollCar($idCar)
    {
        $Car = Car::find($idCar);
        $Car->car_status = 1;
        $Car->save();
        // Car::where('id', $Car->id)->update(array(
        //     'car_status' => 0,
        // ));
    }



    public function SelectAllCars()
    {
        return response()->json(Car::all());
    }

    public function SelectCarNo()
    {
        return response()->json(Car::select('car_no')->get());
    }

    public function SelectOnCar()
    {
        return response()->json(Car::where([['car_stock', '<', 3], ['car_status', 1]])->get());
        // return response()->json(Car::all());

    }


    public function SelectOnCarAll()
    {
        // return response()->json(Car::where([['car_stock', '<', 3], ['car_status', 1]])->get());
        return response()->json(Car::all());
    }

    public function SelectDetailCar($id)
    {
        $query = DB::table('cars')
            ->join('car_types', 'cars.car_types_id', '=', 'car_types.id')
            ->join('car_models', 'cars.car_models_id', '=', 'car_models.id')
            ->join('car_series', 'cars.car_serie_id', '=', 'car_series.id')
            ->join('car_serie_subs', 'cars.car_serie_sub_id', '=', 'car_serie_subs.id')
            ->where('cars.id', $id)
            ->first();

        return response()->json($query);
    }

    // public function StockOnCar_new(Request $request)
    // {

    //     $branch_id = $request->input('branch_id');
    //     $car_stock = $request->input('car_stock');
    //     $car_status = (int)$request->input('car_status');
    //     $car_types_id = $request->input('car_types_id');
    //     $serch_text = $request->input('serch_text');

    //     $map = DB::table('cars')
    //         ->leftJoin('car_types', 'cars.car_types_id', '=', 'car_types.id')
    //         ->leftJoin('car_models', 'cars.car_models_id', '=', 'car_models.id')
    //         ->leftJoin('car_series', 'cars.car_serie_id', '=', 'car_series.id')
    //         ->leftJoin('car_serie_subs', 'cars.car_serie_sub_id', '=', 'car_serie_subs.id')
    //         ->leftJoin('branches', 'cars.branch_id', '=', 'branches.id')
    //         ->leftJoin('colors', 'cars.color_id', '=', 'colors.id')
    //         ->leftJoin('contracts', 'cars.id', '=', 'contracts.car_id')
    //         ->leftJoin('provinces', 'cars.province_id', '=', 'provinces.id')
    //         ->leftJoin('provinces as province_current', 'cars.province_id_current', '=', 'province_current.id')
    //         ->select('cars.*', 'car_types.car_type_name', 'car_types.car_type_name', 'car_models.car_model_name', 'car_series.car_series_name', 'car_serie_subs.car_serie_sub_name', 'branches.branch_name', 'colors.color_name', 'contracts.contract_date', 'provinces.name_th as province_name_th', 'province_current.name_th as province_current_name_th')


    //         ->when($branch_id, function ($query) use ($branch_id) {
    //             return $query->where('branch_id', $branch_id);
    //         })

    //         ->when($car_types_id, function ($query) use ($car_types_id) {
    //             return $query->where('car_types_id', $car_types_id);
    //         })

    //         ->when($car_stock, function ($query) use ($car_stock) {
    //             if ($car_stock == 4) {
    //                 return $query->whereBetween('car_stock', [1, 2]);
    //             } else {
    //                 return $query->where('car_stock', $car_stock);
    //             }
    //         })
    //         ->when($serch_text, function ($query) use ($serch_text) {
    //             $query->where('car_no', 'LIKE', $serch_text . '%');
    //             $query->orWhere('car_regis_current', 'LIKE', '%' . $serch_text . '%');
    //             $query->orWhere('car_regis', 'LIKE', '%' . $serch_text . '%');
    //         })
    //         ->where([['car_status', $car_status]])
    //         ->orderBy('car_no', 'desc')
    //         ->get();
    //     return response()->json($map);
    // }

    public function StockOnCar(Request $request)
    {
        $user_group_permission = $request->input('user_group_permission');
        $car_price_vat_start = $request->input('car_price_vat_start');
        $car_price_vat_end = $request->input('car_price_vat_end');
        $amount_price_start = $request->input('amount_price_start');
        $amount_price_end = $request->input('amount_price_end');
        $branch_id = $request->input('branch_id');
        $car_stock = $request->input('car_stock');
        $car_status = (int)$request->input('car_status');
        $car_types_id = $request->input('car_types_id');
        $serch_text = $request->input('serch_text');

        $query = Car::select(
            'car_no_engine',
            'car_no_body',
            'id',
            'car_no',
            'car_types_id',
            'car_models_id',
            'car_serie_id',
            'car_serie_sub_id',
            'province_id',
            'color_id',
            'province_id_current',
            'user_id',
            'branch_id',
            'img_id_first',
            'booking_status',
            'car_gear',
            'car_year',
            'car_regis',
            'amount_price',
            'amount_down',
            'car_price_vat',
            'car_price_multiply',
            'net_price',
            'car_fix',
            'car_active',
            'car_stock',
            'car_active',
            'car_date_buy',
            'car_regis_current',
            'car_status',
        )
            ->with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'branch', 'color', 'contract', 'province', 'province_current', 'working', 'img_first'])
            ->with(['workings' => function ($query) {
                $query->select(['id', 'car_id', 'work_status']);
            }])
            ->when($car_price_vat_start, function ($query) use ($car_price_vat_start) {
                return $query->where('car_price_vat', '>=', $car_price_vat_start);
            })
            ->when($car_price_vat_end, function ($query) use ($car_price_vat_end) {
                return $query->where('car_price_vat', '<=', $car_price_vat_end);
            })
            ->when($amount_price_start, function ($query) use ($amount_price_start) {
                return $query->where('amount_price', '>=', $amount_price_start);
            })
            ->when($amount_price_end, function ($query) use ($amount_price_end) {
                return $query->where('amount_price', '<=', $amount_price_end);
            })
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->when($car_types_id, function ($query) use ($car_types_id) {
                return $query->where('car_types_id', $car_types_id);
            })
            ->when($car_stock, function ($query) use ($car_stock) {
                if ($car_stock == 4) {
                    return $query->whereBetween('car_stock', [1, 2]);
                } else {
                    return $query->where('car_stock', $car_stock);
                }
            })
            ->when($serch_text, function ($query) use ($serch_text) {
                $query->where('car_no', 'LIKE', $serch_text . '%');
                $query->orWhere('car_regis_current', 'LIKE', '%' . $serch_text . '%');
                $query->orWhere('car_regis', 'LIKE', '%' . $serch_text . '%');
            })
            ->where([['car_status', $car_status]])
            ->orderBy('car_no', 'desc')
            ->get();
        $map = $query->map(function ($items) {
            if ($items['img_id_first'] == 0) {
                $items['img_id_first'] = '' . '/' . $this->path  . $this->pathCurrent . 'default/car_default.png';
            } else {
                if ($items['img_first']) {
                    $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent  . $items['id'] . '/' . $items['img_first']['image_name'];
                } else {
                    $items['img_id_first'] = '' . '/' . $this->path  . $this->pathCurrent . 'default/car_default.png';
                }
            }


            if ($items['amount_price'] > 0) {
                $items['diff_price'] = (int)(($items['amount_price'] - $items['net_price']) / $items['amount_price'] * 100);
            } else {
                $items['diff_price'] = 0;
            }



            $book = 0;
            foreach ($items->workings as $key => $value) {
                if ($value->work_status > 1) {
                    $book = $book + 1;
                }
            }
            $items['count_booking'] = $book;
            return $items;
        });
        return response()->json($map);
    }

    public function uploadImgCars(Request $request)
    {
        if ($request->folder == null) {
            $folderName = time();
            if (File::makeDirectory($this->path . $this->pathCurrent . $folderName, 0775)) {
                $carCarate = Image_car::create();
                $file = $request->file;

                $filename = $carCarate->id  . '.png';
                $saveImagePath = $this->path . $this->pathCurrent . $folderName;
                $resize = Image::make($file)->resize(800, 600);
                //$resize->save();
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $Image_car = Image_car::find($carCarate->id);
                $Image_car->image_name = $filename;
                $Image_car->save();

                return response()->json([
                    'folderName' => $folderName,
                    'imageID' => $carCarate->id,
                ]);
            } else {
                return response()->json(['folderName' => false]);
            }
        } else {
            if (!File::exists($this->path . $this->pathCurrent . $request->folder)) {
                if (File::makeDirectory($this->path . $this->pathCurrent . $request->folder, 0775)) {
                    $carCarate = Image_car::create();
                    $file = $request->file;

                    $filename = $carCarate->id  . '.png';
                    $saveImagePath = $this->path . $this->pathCurrent . $request->folder;
                    $resize = Image::make($file)->resize(800, 600);

                    //$resize->save();
                    $resize->save($this->temp . $filename, 100);
                    $file->move($saveImagePath, $filename);
                    File::delete($this->temp . $filename);
                    $Image_car = Image_car::find($carCarate->id);
                    $Image_car->car_id = $request->folder;
                    $Image_car->image_name = $filename;
                    $Image_car->save();

                    return response()->json([
                        'folderName' => $request->folder,
                        'imageID' => $carCarate->id,
                    ]);
                }
            } else {
                $carCarate = Image_car::create();
                $file = $request->file;

                $filename = $carCarate->id  . '.png';
                $saveImagePath = $this->path . $this->pathCurrent . $request->folder;
                $resize = Image::make($file)->resize(800, 600);
                //$resize->save();
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $Image_car = Image_car::find($carCarate->id);
                $Image_car->car_id = $request->folder;
                $Image_car->image_name = $filename;
                $Image_car->save();

                return response()->json([
                    'folderName' => $request->folder,
                    'imageID' => $carCarate->id,
                ]);
            }
        }
    }

    public function change_fist_img(Request $request)
    {
        $updateCar = Car::find($request->car_id);

        $before_firsts = Image_car::where([['car_id', $request->car_id]])->get();
        if (count($before_firsts) != 0) {
            foreach ($before_firsts as $key => $before_first) {
                $updateBefore_first = Image_car::find($before_first->id);
                $updateBefore_first->img_first = 0;
                $updateBefore_first->save();
            }
        }
        $after_first = Image_car::find($request->image_id);
        $after_first->img_first = 1;
        $after_first->save();

        $updateCar->img_id_first = $request->image_id;
        $updateCar->save();

        $imageCars = Image_car::where('car_id', $request->car_id)->orderBy('img_first', 'DESC')->get();
        return response()->json($imageCars);
    }

    public function showSearchCars(Request $request)
    {
        $down = Amount_down::find($request->car_down_id);
        $slacken = Amount_slacken::find($request->car_slacken_id);

        $branch_province_id = $request->input('province_id');
        $branch_id = $request->input('branch_id');
        $car_type_id = $request->input('car_type_id');
        $car_model_id = $request->input('car_model_id');
        $car_series_id = $request->input('car_series_id');
        $car_serie_sub_id = $request->input('car_serie_sub_id');

        $car_down_id = $request->input('car_down_id');
        $car_slacken_id = $request->input('car_slacken_id');
        $fuel_type = $request->input('fuel_type');


        // if ($request->branch_id == 0) {
        // } else if ($request->car_type_id == 0) {
        // } else if ($request->car_model_id == 0) {
        // } else if ($request->car_series_id == 0) {
        // } else if ($request->car_down_id == 0) {
        // } else if ($request->car_slacken_id == 0) {
        // }
        $output =
            Car::select(
                'car_mark_year',
                'car_mileage_balance',
                'car_no_engine',
                'car_no_body',
                'id',
                'car_no',
                'car_types_id',
                'car_models_id',
                'car_serie_id',
                'car_serie_sub_id',
                'partner_car_id',
                'province_id',
                'branch_province_id',
                'color_id',
                'fuel_id',
                'province_id_current',
                'user_id',
                'branch_id',
                'car_engine_amount',
                'car_mileage',
                'img_id_first',
                'booking_status',
                'car_gear',
                'car_year',
                'car_regis',
                'amount_price',
                'amount_down',
                'car_price_vat',
                'net_price',
                'car_fix',
                'car_active',
                'car_stock',
                'car_active',
                'car_date_buy',
                'car_regis_current',
                'car_status',
            )
            ->with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'branch'])
            ->where([
                // ['car_types_id', $request->car_type_id],
                // ['car_models_id', $request->car_model_id],
                // ['car_serie_id', $request->car_series_id],
                // ['branch_id', $request->branch_id],
                // ['amount_down', '>=', $down->amount_down_start],
                // ['amount_down', '<=', $down->amount_down_end],
                // ['amount_slacken', '>=', $slacken->amount_slacken_start],
                // ['amount_slacken', '<=', $slacken->amount_slacken_end],
                ['car_stock', '<', 3],
                ['car_active', 1]
            ])
            ->when($down, function ($query) use ($down) {
                $down = Amount_down::find($down->id);
                return $query->where([['amount_down', '>=', $down->amount_down_start], ['amount_down', '<=', $down->amount_down_end]]);
            })
            ->when($slacken, function ($query) use ($slacken) {
                $slacken = Amount_slacken::find($slacken->id);
                return $query->where([['amount_slacken', '>=', $slacken->amount_slacken_start], ['amount_slacken', '<=', $slacken->amount_slacken_end]]);
            })


            ->when($branch_province_id, function ($query) use ($branch_province_id) {
                return $query->where('branch_province_id', $branch_province_id);
            })

            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })

            ->when($car_type_id, function ($query) use ($car_type_id) {
                return $query->where('car_types_id', $car_type_id);
            })
            ->when($car_model_id, function ($query) use ($car_model_id) {
                return $query->where('car_models_id', $car_model_id);
            })
            ->when($car_series_id, function ($query) use ($car_series_id) {
                return $query->where('car_serie_id', $car_series_id);
            })
            ->when($car_serie_sub_id, function ($query) use ($car_serie_sub_id) {
                return $query->where('car_serie_sub_id', $car_serie_sub_id);
            })

            ->when($fuel_type, function ($query) use ($fuel_type) {
                return $query->where('fuel_type', $fuel_type);
            })

            ->inRandomOrder()
            ->orderBy('created_at', 'desc')
            ->get();


        $map = $output->map(function ($items) {
            unset($items['car_buy_vat']);
            unset($items['VatSumOverCos']);
            unset($items['car_price_bank']);
            unset($items['branch_id']);
            unset($items['car_price_plus']);
            unset($items['amount_price']);
            unset($items['amount_price_vat']);
            unset($items['net_price']);
            unset($items['car_buy']);
            unset($items['income']);

            unset($items['branch_province_id']);
            unset($items['car_active']);
            unset($items['car_booking']);
            unset($items['car_buy_from']);
            unset($items['car_date_book']);
            unset($items['car_date_buy']);
            unset($items['car_fix']);
            unset($items['car_mark_year']);
            unset($items['car_mileage_balance']);
            unset($items['car_models_id']);
            unset($items['car_price_bank']);
            unset($items['car_price_etc']);
            unset($items['car_regis_current']);
            unset($items['car_sale_date']);
            unset($items['car_serie_sub_id']);
            unset($items['car_status']);
            unset($items['car_stock']);
            unset($items['car_types_id']);
            unset($items['cd']);
            unset($items['color_id']);
            unset($items['comment']);
            unset($items['created_at']);
            unset($items['danger_text']);
            unset($items['danger_type']);
            unset($items['expenses']);
            unset($items['fuel_id']);
            unset($items['obligation']);
            unset($items['owner_name']);
            unset($items['owner_no']);
            unset($items['partner_car_id']);
            unset($items['province_id']);
            unset($items['province_id_current']);
            unset($items['tex_date']);
            unset($items['updated_at']);
            unset($items['user_id']);
            unset($items['water_text']);
            unset($items['water_type']);



            // $items['car_price_vat'] = 0;
            // $items['amount_price'] = 0;


            if ($items['img_id_first'] == 0) {
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent  . 'default/car_default.png';
            } else {
                $Image_car =  Image_car::find($items['img_id_first']);
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent . $items['id'] . '/' . $Image_car->image_name;
            }
            return $items;
        });

        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            return response()->json($map);
        } else {
            if ($host == $allow_host) {
                return response()->json($map);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }

    public function showImageCar($car_id)
    {
        $imageCars = Image_car::where('car_id', $car_id)->orderBy('img_first', 'DESC')->get();
        $imageCars->map(function ($image) {
            $image['src'] = env("APP_URL") . '/' . $this->path . $this->pathCurrent  . $image['car_id'] . '/' . $image->image_name;
        });
        return response()->json($imageCars);
    }

    public function deleteImageCar(Request $request)
    {
        $delImgPath = $this->path . $this->pathCurrent  . $request->car_id . '/' . $request->image_id . '.png';
        File::delete($delImgPath);
        $Car = Car::find($request->car_id);
        $Car->img_id_first = 0;
        $Car->save();
        Image_car::find($request->image_id)->delete();
        if ($request->method != 'Add') {
            $imageCars = Image_car::where('car_id', $request->car_id)->orderBy('img_first', 'DESC')->get();
            return response()->json($imageCars);
        }
    }

    public function deleteAllImgCar(Request $request)
    {
        // $delImgPath = $this->path . $this->pathCurrent  . $request->car_id;
        // File::deleteDirectory($delImgPath);
        $Car = Car::find($request->car_id);
        $Car->img_id_first = 0;
        $Car->save();
        $Image_car = Image_car::where('car_id', $request->car_id)->get();
        for ($i = 0; $i < count($Image_car); $i++) {
            $deleteImg = Image_car::find($Image_car[$i]->id)->delete();
            $delImgPath = $this->path . $this->pathCurrent  . $request->car_id . '/' . $Image_car[$i]->id . '.png';
            File::delete($delImgPath);
        }
    }

    public function deleteFolder(Request $request)
    {
        if (!is_null($request->folder)) {
            $delImgPath = $this->path . $this->pathCurrent  . $request->folder;
            File::deleteDirectory($delImgPath);
            Image_car::where('car_id', $request->folder)->delete();
        }

        // return response()->json($request);
    }


    public function showLastCars()
    {
        $output =
            Car::select(
                'car_mark_year',
                'car_mileage_balance',
                'car_no_engine',
                'car_no_body',
                'id',
                'car_no',
                'car_types_id',
                'car_models_id',
                'car_serie_id',
                'car_serie_sub_id',
                'partner_car_id',
                'province_id',
                'branch_province_id',
                'color_id',
                'fuel_id',
                'province_id_current',
                'user_id',
                'branch_id',
                'car_engine_amount',
                'car_mileage',
                'img_id_first',
                'booking_status',
                'car_gear',
                'car_year',
                'car_regis',
                'amount_price',
                'amount_down',
                'car_price_vat',
                'net_price',
                'car_fix',
                'car_active',
                'car_stock',
                'car_active',
                'car_date_buy',
                'car_regis_current',
                'car_status',
            )
            ->with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'branch'])
            ->where([['car_active', 1], ['car_stock', '<', 3]])->orderBy('created_at', 'desc')
            ->inRandomOrder()
            ->limit(20)
            ->get();

        $map = $output->map(function ($items) {

            unset($items['car_buy_vat']);
            unset($items['VatSumOverCos']);
            unset($items['car_price_bank']);
            unset($items['branch_id']);
            unset($items['car_price_plus']);
            unset($items['amount_price']);
            unset($items['amount_price_vat']);
            unset($items['net_price']);
            unset($items['car_buy']);
            unset($items['income']);

            unset($items['branch_province_id']);
            unset($items['car_active']);
            unset($items['car_booking']);
            unset($items['car_buy_from']);
            unset($items['car_date_book']);
            unset($items['car_date_buy']);
            unset($items['car_fix']);
            unset($items['car_mark_year']);
            unset($items['car_mileage_balance']);
            unset($items['car_models_id']);
            unset($items['car_price_bank']);
            unset($items['car_price_etc']);
            unset($items['car_regis_current']);
            unset($items['car_sale_date']);
            unset($items['car_serie_sub_id']);
            unset($items['car_status']);
            unset($items['car_stock']);
            unset($items['car_types_id']);
            unset($items['cd']);
            unset($items['color_id']);
            unset($items['comment']);
            unset($items['created_at']);
            unset($items['danger_text']);
            unset($items['danger_type']);
            unset($items['expenses']);
            unset($items['fuel_id']);
            unset($items['obligation']);
            unset($items['owner_name']);
            unset($items['owner_no']);
            unset($items['partner_car_id']);
            unset($items['province_id']);
            unset($items['province_id_current']);
            unset($items['tex_date']);
            unset($items['updated_at']);
            unset($items['user_id']);
            unset($items['water_text']);
            unset($items['water_type']);



            // $items['car_price_vat'] = 0;
            // $items['amount_price'] = 0;


            if ($items['img_id_first'] == 0) {
                $items['img_id_first'] = '' . '/' . $this->path  . $this->pathCurrent . 'default/car_default.png';
            } else {
                $Image_car =  Image_car::find($items['img_id_first']);
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent  . $items['id'] . '/' . $Image_car->image_name;
            }
            return $items;
        });
        return response()->json($map);
    }

    public function showAllCars()
    {
        $output =
            Car::select(
                'car_mark_year',
                'car_mileage_balance',
                'car_no_engine',
                'car_no_body',
                'id',
                'car_no',
                'car_types_id',
                'car_models_id',
                'car_serie_id',
                'car_serie_sub_id',
                'partner_car_id',
                'province_id',
                'branch_province_id',
                'color_id',
                'fuel_id',
                'province_id_current',
                'user_id',
                'branch_id',
                'car_engine_amount',
                'car_mileage',
                'img_id_first',
                'booking_status',
                'car_gear',
                'car_year',
                'car_regis',
                'amount_price',
                'amount_down',
                'car_price_vat',
                'net_price',
                'car_fix',
                'car_active',
                'car_stock',
                'car_active',
                'car_date_buy',
                'car_regis_current',
                'car_status',
            )
            ->with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'branch'])
            ->where([['car_active', 1], ['car_stock', '<', 3]])->orderBy('created_at', 'desc')
            ->inRandomOrder()
            ->limit(100)
            ->get();

        $map = $output->map(function ($items) {

            unset($items['car_buy_vat']);
            unset($items['VatSumOverCos']);
            unset($items['car_price_bank']);
            unset($items['branch_id']);
            unset($items['car_price_plus']);
            unset($items['amount_price']);
            unset($items['amount_price_vat']);
            unset($items['net_price']);
            unset($items['car_buy']);
            unset($items['income']);

            unset($items['branch_province_id']);
            unset($items['car_active']);
            unset($items['car_booking']);
            unset($items['car_buy_from']);
            unset($items['car_date_book']);
            unset($items['car_date_buy']);
            unset($items['car_fix']);
            unset($items['car_mark_year']);
            unset($items['car_mileage_balance']);
            unset($items['car_models_id']);
            unset($items['car_price_bank']);
            unset($items['car_price_etc']);
            unset($items['car_regis_current']);
            unset($items['car_sale_date']);
            unset($items['car_serie_sub_id']);
            unset($items['car_status']);
            unset($items['car_stock']);
            unset($items['car_types_id']);
            unset($items['cd']);
            unset($items['color_id']);
            unset($items['comment']);
            unset($items['created_at']);
            unset($items['danger_text']);
            unset($items['danger_type']);
            unset($items['expenses']);
            unset($items['fuel_id']);
            unset($items['obligation']);
            unset($items['owner_name']);
            unset($items['owner_no']);
            unset($items['partner_car_id']);
            unset($items['province_id']);
            unset($items['province_id_current']);
            unset($items['tex_date']);
            unset($items['updated_at']);
            unset($items['user_id']);
            unset($items['water_text']);
            unset($items['water_type']);

            // $items['car_price_vat'] = 0;
            // $items['amount_price'] = 0;


            if ($items['img_id_first'] == 0) {
                $items['img_id_first'] = '' . '/' . $this->path  . $this->pathCurrent . 'default/car_default.png';
            } else {
                $Image_car =  Image_car::find($items['img_id_first']);
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent  . $items['id'] . '/' . $Image_car->image_name;
            }
            return $items;
        });

        return response()->json($map);
    }

    public function showDownCars()
    {

        $output =
            Car::select(
                'car_mark_year',
                'car_mileage_balance',
                'car_no_engine',
                'car_no_body',
                'id',
                'car_no',
                'car_types_id',
                'car_models_id',
                'car_serie_id',
                'car_serie_sub_id',
                'partner_car_id',
                'province_id',
                'branch_province_id',
                'color_id',
                'fuel_id',
                'province_id_current',
                'user_id',
                'branch_id',
                'car_engine_amount',
                'car_mileage',
                'img_id_first',
                'booking_status',
                'car_gear',
                'car_year',
                'car_regis',
                'amount_price',
                'amount_down',
                'car_price_vat',
                'net_price',
                'car_fix',
                'car_active',
                'car_stock',
                'car_active',
                'car_date_buy',
                'car_regis_current',
                'car_status',
            )
            ->with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'branch'])
            ->where([['car_active', 1], ['car_stock', '<', 3], ['amount_down', '<=', 5000]])->orderBy('created_at', 'desc')
            ->inRandomOrder()
            ->limit(100)
            ->get();

        $map = $output->map(function ($items) {

            unset($items['car_buy_vat']);
            unset($items['VatSumOverCos']);
            unset($items['car_price_bank']);
            unset($items['branch_id']);
            unset($items['car_price_plus']);
            unset($items['amount_price']);
            unset($items['amount_price_vat']);
            unset($items['net_price']);
            unset($items['car_buy']);
            unset($items['income']);

            unset($items['branch_province_id']);
            unset($items['car_active']);
            unset($items['car_booking']);
            unset($items['car_buy_from']);
            unset($items['car_date_book']);
            unset($items['car_date_buy']);
            unset($items['car_fix']);
            unset($items['car_mark_year']);
            unset($items['car_mileage_balance']);
            unset($items['car_models_id']);
            unset($items['car_price_bank']);
            unset($items['car_price_etc']);
            unset($items['car_regis_current']);
            unset($items['car_sale_date']);
            unset($items['car_serie_sub_id']);
            unset($items['car_status']);
            unset($items['car_stock']);
            unset($items['car_types_id']);
            unset($items['cd']);
            unset($items['color_id']);
            unset($items['comment']);
            unset($items['created_at']);
            unset($items['danger_text']);
            unset($items['danger_type']);
            unset($items['expenses']);
            unset($items['fuel_id']);
            unset($items['obligation']);
            unset($items['owner_name']);
            unset($items['owner_no']);
            unset($items['partner_car_id']);
            unset($items['province_id']);
            unset($items['province_id_current']);
            unset($items['tex_date']);
            unset($items['updated_at']);
            unset($items['user_id']);
            unset($items['water_text']);
            unset($items['water_type']);


            // $items['car_price_vat'] = 0;
            // $items['amount_price'] = 0;


            if ($items['img_id_first'] == 0) {
                $items['img_id_first'] = '' . '/' . $this->path  . $this->pathCurrent . 'default/car_default.png';
            } else {
                $Image_car =  Image_car::find($items['img_id_first']);
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent  . $items['id'] . '/' . $Image_car->image_name;
            }
            return $items;
        });
        // return response()->json($map);

        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            return response()->json($map);
        } else {
            if ($host == $allow_host) {
                return response()->json($map);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }


    public function showDownslackens()
    {
        $output =
            Car::select(
                'car_mark_year',
                'car_mileage_balance',
                'car_no_engine',
                'car_no_body',
                'id',
                'car_no',
                'car_types_id',
                'car_models_id',
                'car_serie_id',
                'car_serie_sub_id',
                'partner_car_id',
                'province_id',
                'branch_province_id',
                'color_id',
                'fuel_id',
                'province_id_current',
                'user_id',
                'branch_id',
                'car_engine_amount',
                'car_mileage',
                'img_id_first',
                'booking_status',
                'car_gear',
                'car_year',
                'car_regis',
                'amount_price',
                'amount_down',
                'car_price_vat',
                'net_price',
                'car_fix',
                'car_active',
                'car_stock',
                'car_active',
                'car_date_buy',
                'car_regis_current',
                'car_status',
            )
            ->with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'branch'])
            ->where([['car_stock', '<', 3], ['car_active', 1], ['amount_slacken', '<=', 10000]])->orderBy('created_at', 'desc')
            ->inRandomOrder()
            ->limit(100)
            ->get();

        $map = $output->map(function ($items) {

            unset($items['car_buy_vat']);
            unset($items['VatSumOverCos']);
            unset($items['car_price_bank']);
            unset($items['branch_id']);
            unset($items['car_price_plus']);
            unset($items['amount_price']);
            unset($items['amount_price_vat']);
            unset($items['net_price']);
            unset($items['car_buy']);
            unset($items['income']);

            unset($items['branch_province_id']);
            unset($items['car_active']);
            unset($items['car_booking']);
            unset($items['car_buy_from']);
            unset($items['car_date_book']);
            unset($items['car_date_buy']);
            unset($items['car_fix']);
            unset($items['car_mark_year']);
            unset($items['car_mileage_balance']);
            unset($items['car_models_id']);
            unset($items['car_price_bank']);
            unset($items['car_price_etc']);
            unset($items['car_regis_current']);
            unset($items['car_sale_date']);
            unset($items['car_serie_sub_id']);
            unset($items['car_status']);
            unset($items['car_stock']);
            unset($items['car_types_id']);
            unset($items['cd']);
            unset($items['color_id']);
            unset($items['comment']);
            unset($items['created_at']);
            unset($items['danger_text']);
            unset($items['danger_type']);
            unset($items['expenses']);
            unset($items['fuel_id']);
            unset($items['obligation']);
            unset($items['owner_name']);
            unset($items['owner_no']);
            unset($items['partner_car_id']);
            unset($items['province_id']);
            unset($items['province_id_current']);
            unset($items['tex_date']);
            unset($items['updated_at']);
            unset($items['user_id']);
            unset($items['water_text']);
            unset($items['water_type']);

            // $items['car_price_vat'] = 0;
            // $items['amount_price'] = 0;


            if ($items['img_id_first'] == 0) {
                $items['img_id_first'] = '' . '/' . $this->path  . $this->pathCurrent . 'default/car_default.png';
            } else {
                $Image_car =  Image_car::find($items['img_id_first']);
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent  . $items['id'] . '/' . $Image_car->image_name;
            }
            return $items;
        });
        // return response()->json($map);

        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            return response()->json($map);
        } else {
            if ($host == $allow_host) {
                return response()->json($map);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }
    public function infoCar($infoCar)
    {
        $car = Car::select(

            'fuel_type',
            'car_mark_year',
            'car_mileage_balance',
            'car_no_engine',
            'car_no_body',
            'id',
            'car_no',
            'car_types_id',
            'car_models_id',
            'car_serie_id',
            'car_serie_sub_id',
            'partner_car_id',
            'province_id',
            'branch_province_id',
            'color_id',
            'fuel_id',
            'province_id_current',
            'user_id',
            'branch_id',
            'car_mileage',
            'car_engine_amount',
            'img_id_first',
            'booking_status',
            'car_gear',
            'car_year',
            'car_regis',
            'amount_price',
            'amount_down',
            'car_price_vat',
            'net_price',
            'car_fix',
            'car_active',
            'car_stock',
            'car_active',
            'car_date_buy',
            'car_regis_current',
            'car_status',
        )
            ->with(['car_models', 'car_series', 'car_serie_sub', 'branch', 'fuels', 'car_images'])->where('id', $infoCar)->first();
        unset($car['car_buy_vat']);
        unset($car['VatSumOverCos']);
        unset($car['car_price_bank']);
        unset($car['branch_id']);
        unset($car['car_price_plus']);
        unset($car['amount_price']);
        unset($car['amount_price_vat']);
        unset($car['net_price']);
        unset($car['car_buy']);
        unset($car['income']);

        unset($car['branch_province_id']);
        unset($car['car_active']);
        unset($car['car_booking']);
        unset($car['car_buy_from']);
        unset($car['car_date_book']);
        unset($car['car_date_buy']);
        unset($car['car_fix']);
        unset($car['car_mark_year']);
        unset($car['car_mileage_balance']);
        unset($car['car_models_id']);
        unset($car['car_price_bank']);
        unset($car['car_price_etc']);
        unset($car['car_regis_current']);
        unset($car['car_sale_date']);
        unset($car['car_serie_sub_id']);
        unset($car['car_status']);
        unset($car['car_stock']);
        unset($car['car_types_id']);
        unset($car['cd']);
        unset($car['color_id']);
        unset($car['comment']);
        unset($car['created_at']);
        unset($car['danger_text']);
        unset($car['danger_type']);
        unset($car['expenses']);
        unset($car['fuel_id']);
        unset($car['obligation']);
        unset($car['owner_name']);
        unset($car['owner_no']);
        unset($car['partner_car_id']);
        unset($car['province_id']);
        unset($car['province_id_current']);
        unset($car['tex_date']);
        unset($car['updated_at']);
        unset($car['user_id']);
        unset($car['water_text']);
        unset($car['water_type']);


        // unset($car['car_price_vat']);
        // unset($car['amount_price']);

        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            return response()->json($car);
        } else {
            if ($host == $allow_host) {
                return response()->json($car);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }


    public function showForYouCars($car_serie_id)
    {
        $output =
            Car::select(
                'car_mark_year',
                'car_mileage_balance',
                'car_no_engine',
                'car_no_body',
                'id',
                'car_no',
                'car_types_id',
                'car_models_id',
                'car_serie_id',
                'car_serie_sub_id',
                'partner_car_id',
                'province_id',
                'branch_province_id',
                'color_id',
                'fuel_id',
                'province_id_current',
                'user_id',
                'branch_id',
                'car_engine_amount',
                'car_mileage',
                'img_id_first',
                'booking_status',
                'car_gear',
                'car_year',
                'car_regis',
                'amount_price',
                'amount_down',
                'car_price_vat',
                'net_price',
                'car_fix',
                'car_active',
                'car_stock',
                'car_active',
                'car_date_buy',
                'car_regis_current',
                'car_status',
            )
            ->with(['car_models', 'car_series', 'car_serie_sub', 'branch'])
            ->where([['car_serie_id', $car_serie_id], ['car_stock', '<', 3]])->orderBy('created_at', 'desc')
            ->inRandomOrder()
            ->limit(2)
            ->get();

        $map = $output->map(function ($items) {

            // $items['car_buy_vat'] = 0;
            // $items['net_price'] = 0;
            // $items['car_buy'] = 0;
            // $items['VatSumOverCos'] = 0;
            // $items['income'] = 0;
            // $items['car_price_plus'] = 0;
            // $items['car_price_multiply'] = 0;
            // $items['amount_price'] = 0;
            // $items['amount_price_vat'] = 0;



            unset($items['car_buy_vat']);
            unset($items['VatSumOverCos']);
            unset($items['car_price_bank']);
            unset($items['branch_id']);
            unset($items['car_price_plus']);
            unset($items['amount_price']);
            unset($items['amount_price_vat']);
            unset($items['net_price']);
            unset($items['car_buy']);
            unset($items['income']);
            unset($items['branch_province_id']);
            unset($items['car_active']);
            unset($items['car_booking']);
            unset($items['car_buy_from']);
            unset($items['car_date_book']);
            unset($items['car_date_buy']);
            unset($items['car_fix']);
            unset($items['car_mark_year']);
            unset($items['car_mileage_balance']);
            unset($items['car_models_id']);
            unset($items['car_price_bank']);
            unset($items['car_price_etc']);
            unset($items['car_regis_current']);
            unset($items['car_sale_date']);
            unset($items['car_serie_sub_id']);
            unset($items['car_status']);
            unset($items['car_stock']);
            unset($items['car_types_id']);
            unset($items['cd']);
            unset($items['color_id']);
            unset($items['comment']);
            unset($items['created_at']);
            unset($items['danger_text']);
            unset($items['danger_type']);
            unset($items['expenses']);
            unset($items['fuel_id']);
            unset($items['obligation']);
            unset($items['owner_name']);
            unset($items['owner_no']);
            unset($items['partner_car_id']);
            unset($items['province_id']);
            unset($items['province_id_current']);
            unset($items['tex_date']);
            unset($items['updated_at']);
            unset($items['user_id']);
            unset($items['water_text']);
            unset($items['water_type']);




            // $items['car_price_vat'] = 0;
            // $items['amount_price'] = 0;


            if ($items['img_id_first'] == 0) {
                $items['img_id_first'] = '' . '/' . $this->path  . $this->pathCurrent . 'default/car_default.png';
            } else {
                $Image_car =  Image_car::find($items['img_id_first']);
                $items['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent  . $items['id'] . '/' . $Image_car->image_name;
            }
            return $items;
        });
        // return response()->json($map);

        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            return response()->json($map);
        } else {
            if ($host == $allow_host) {
                return response()->json($map);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }
    public function getAllinfo($car_id, $user_group_permission)
    {
        $queryCar = [];
        $partner_car = [];
        $receiving_moneys = [];
        $user = [];
        $branch = [];
        $customer = [];
        $working = [];

        $newLabels_car = ['รายได้', 'รายจ่าย', 'กำไร'];
        $newColors_car = ['#007BC5', '#E67B19', '#009442'];
        $newDatas_price = [0, 0, 0];


        $queryCar = Car::select(
            'fuel_type',
            'car_mark_year',
            'car_mileage_balance',
            'car_no_engine',
            'car_no_body',
            'id',
            'car_no',
            'car_types_id',
            'car_models_id',
            'car_serie_id',
            'car_serie_sub_id',
            'partner_car_id',
            'province_id',
            'branch_province_id',
            'color_id',
            'fuel_id',
            'province_id_current',
            'user_id',
            'branch_id',
            'car_engine_amount',
            'car_mileage',
            'img_id_first',
            'booking_status',
            'car_gear',
            'car_year',
            'car_regis',
            'amount_price',
            'amount_down',
            'car_price_vat',
            'net_price',
            'car_fix',
            'car_active',
            'car_stock',
            'car_active',
            'car_date_buy',
            'car_regis_current',
            'car_status',
            'car_buy_vat',
            'VatSumOverCos',
            'car_buy',
            'car_price_plus',
            'car_price_multiply',
            'amount_price',
            'income',

            'expenses',
            'amount_slacken',
            'amount_overCost',
        )
            ->with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'color', 'province', 'province_current', 'fuels', 'partner_car'])->findOrFail($car_id);

        if ($user_group_permission == '-1' || $user_group_permission == 9) {


            $partner_car = Partner_car::select('id', 'partner_car_name')->findOrFail($queryCar->partner_car_id);
            $receiving_moneys = Receiving_money::select('id', 'receiving_money_all', 'commission', 'created_at', 'working_id', 'sale_id')->where('car_id', $car_id)->latest('id')->first();;


            $user = User::select('id', 'first_name')->where('id', $queryCar->user_id)->firstOrFail();
            $branch = Branch::select('id', 'branch_name')->where('id', $queryCar->branch_id)->firstOrFail();


            $findPrice = Middle_price::select('id', 'selected', 'car_serie_id', 'car_serie_sub_id', 'year', 'car_gear', 'middle_price_active')
                ->where(
                    [
                        ['car_serie_id', $queryCar->car_serie_id],
                        ['car_serie_sub_id', $queryCar->car_serie_sub_id],
                        ['year', $queryCar->car_year],
                        ['car_gear', $queryCar->car_gear]
                    ]
                )
                ->first();
            $dataBooking = Working::select('id', 'customer_name', 'branch_id', 'customer_tel', 'created_at', 'customer_id', 'sale_id', 'hear_from_type', 'work_status', 'status_del')
                ->with(['cars', 'sale', 'branch'])
                ->where([['car_id', $car_id]])
                ->orderBy('created_at', 'DESC')
                ->get();

            $dataPreviewBanks = [];
            if ($findPrice != null) {
                $getDetail = Middle_price_detail::where([['middle_price_id', $findPrice->id]])->get();
                for ($i = 0; $i < count($getDetail); $i++) {
                    if ($getDetail[$i]['middle_price'] != 0) {

                        if ($findPrice->selected == $getDetail[$i]['bank_id']) {
                            $isSelected = 1;
                        } else {
                            $isSelected = 0;
                        }

                        array_push($dataPreviewBanks, array(
                            'isSelected' => $isSelected,
                            'bank_name' => Bank::select('bank_name', 'bank_nick_name')->find($getDetail[$i]['bank_id']),
                            'middle_price' => $getDetail[$i]['middle_price'],
                            'amount_price' => ($getDetail[$i]['middle_price'] + $getDetail[$i]['middle_price'] * $getDetail[$i]['middle_plus'] / 100) * ($getDetail[$i]['middle_multiply'] / 100),
                            'middle_plus' => $getDetail[$i]['middle_plus'],
                            'middle_multiply' => $getDetail[$i]['middle_multiply'],
                        ));
                    }
                }
            }

            // การขาย
            $contract = Contract::where('car_id', $car_id)->latest()->first();
            $saleDetail['working_id'] =  "ยังไม่มีข้อมูล";
            $saleDetail['sale_name'] =  "ยังไม่มีข้อมูล";
            $saleDetail['sale_price'] =  "ยังไม่มีข้อมูล";
            $saleDetail['customer_name'] =  "ยังไม่มีข้อมูล";
            $saleDetail['sale_booking'] =  "ยังไม่มีข้อมูล";
            $saleDetail['sale_date'] =  "ยังไม่มีข้อมูล";
            if ($contract) {
                $working = Working::select('id', 'customer_id', 'sale_id', 'bank_id', 'created_at')
                    ->with(['banks'])
                    ->find($contract->working_id);
                $customer = Customer::select('customer_name')->find($working->customer_id);
                $sale = User::select('id', 'first_name')->where('id', $working->sale_id)->first();
                $appointment_bank = Appointment_bank::select('car_price', 'car_price_persen')
                    ->where('working_id', $contract->working_id)->first();

                $saleDetail['working_id'] =  'W' . $contract->working_id;
                $saleDetail['customer_name'] =  $customer['customer_name'];
                $saleDetail['sale_name'] = $sale ? $sale['first_name'] : "ไม่พบข้อมูล";

                $saleDetail['sale_price'] = $appointment_bank ? (int)$appointment_bank['car_price'] : "ไม่พบข้อมูล";
                $saleDetail['car_price_persen'] = $appointment_bank ? $appointment_bank['car_price_persen'] : "ไม่พบข้อมูล";

                $saleDetail['sale_booking'] =  $working->created_at;
                $saleDetail['sale_date'] =  $contract->contract_date;
                $saleDetail['bank_nick_name'] = $working['banks'] ? $working['banks']['bank_nick_name'] : ' ';
            }


            $income = Income::where('car_id', $car_id)
                ->where('active', 1)
                ->sum('money');
            $queryCar->income = $income;

            // รายจ่าย
            $sumExpensesWithoutCar = Outlay_cost::where('car_id', $car_id)
                ->where('detail', '!=', 'ค่าตัวรถ')
                ->where('active', 1)
                ->sum('money');

            $sumExpenses = Outlay_cost::where('car_id', $car_id)
                ->where('active', 1)
                ->sum('money');

            $queryCar->sumExpensesWithoutCar = (float)$sumExpensesWithoutCar;
            $queryCar->sumOverAllExpenses = (float)$sumExpenses;


            // สำหรับทำกราฟ
            $newDatas_price[0] = (float)$newDatas_price[0] + (float)$queryCar->income;
            // $newDatas_price[1] = (float)$newDatas_price[1] + (float)$queryCar->net_price + (float)$queryCar->car_price_etc;
            $newDatas_price[1] = $queryCar->sumOverAllExpenses;
            $newDatas_price[2] = (float)$newDatas_price[0] - (float)$newDatas_price[1];



            // รูป
            if ($queryCar->img_id_first == 0) {
                $queryCar->img_id_first = '' . '/' . $this->path . $this->pathCurrent . 'default/car_default.png';
            } else {
                $Image_car = Image_car::find($queryCar->img_id_first);
                $queryCar->img_id_first = '' . '/' . $this->path . $this->pathCurrent . $queryCar->id . '/' . $Image_car->image_name;
            }
            return response()->json([
                'car_id' => $car_id,
                'queryCar' => $queryCar,
                'partner_car' => $partner_car,
                'receiving_moneys' => $receiving_moneys,
                'customer' => $customer,
                'working' => $working,
                'user' => $user,
                'saleDetail' => $saleDetail,
                'branch' => $branch,
                'dataBooking' => $dataBooking,
                'newLabels_car' => $newLabels_car,
                'newColors_car' => $newColors_car,
                'newDatas_price' => $newDatas_price,
                'dataPreviewBanks' => $dataPreviewBanks,
                // 'Image_car' => $Image_car,
                // 'repair_price' => $repair_price
            ]);
        } else {
            $partner_car = Partner_car::select('id', 'partner_car_name')->findOrFail($queryCar->partner_car_id);
            $branch = Branch::select('id', 'branch_name')->where('id', $queryCar->branch_id)->firstOrFail();
            $findPrice = Middle_price::select('id', 'car_serie_id', 'car_serie_sub_id', 'year', 'car_gear', 'middle_price_active')
                ->where(
                    [
                        ['car_serie_id', $queryCar->car_serie_id],
                        ['car_serie_sub_id', $queryCar->car_serie_sub_id],
                        ['year', $queryCar->car_year],
                        ['car_gear', $queryCar->car_gear],
                        ['middle_price_active', 1]
                    ]
                )
                ->first();
            $dataBooking = Working::select('id', 'customer_name', 'branch_id', 'customer_tel', 'created_at', 'customer_id', 'sale_id', 'hear_from_type', 'work_status', 'status_del')
                ->with(['cars', 'sale', 'branch'])->where(
                    [
                        ['car_id', $car_id],
                    ]
                )->orderBy('created_at', 'DESC')->get();
            $dataPreviewBanks = [];

            if (!empty($findPrice)) {
                $getDetail = Middle_price_detail::where([['middle_price_id', $findPrice->id]])->get();

                for ($i = 0; $i < count($getDetail); $i++) {

                    if ($getDetail[$i]['middle_price'] != 0) {
                        array_push($dataPreviewBanks, array(
                            'bank_name' => Bank::select('bank_name')->find($getDetail[$i]['bank_id']),
                            // 'amount_price' => (float)$getDetail[$i]['amount_price'] + (((float)$queryCar->amount_down + 20000)),
                            'amount_price' => ($getDetail[$i]['middle_price'] + $getDetail[$i]['middle_price'] * $getDetail[$i]['middle_plus'] / 100) * 0.95 + (((float)$queryCar->amount_down + 20000)),
                            'middle_price' => $getDetail[$i]['middle_price'],
                            'middle_plus' => $getDetail[$i]['middle_plus'],
                            'middle_multiply' => $getDetail[$i]['middle_multiply'],
                        ));
                    }
                }
            }


            unset($queryCar['car_buy_vat']);
            unset($queryCar['VatSumOverCos']);
            unset($queryCar['net_price']);
            unset($queryCar['car_buy']);
            unset($queryCar['car_price_plus']);
            unset($queryCar['car_price_multiply']);
            unset($queryCar['amount_price']);
            unset($queryCar['income']);
            unset($queryCar['amount_slacken']);
            unset($queryCar['amount_overCost']);
            unset($queryCar['expenses']);

            // รูป
            if ($queryCar->img_id_first == 0) {
                $queryCar->img_id_first = '' . '/' . $this->path . $this->pathCurrent . 'default/car_default.png';
            } else {
                $Image_car = Image_car::find($queryCar->img_id_first);
                $queryCar->img_id_first = '' . '/' . $this->path . $this->pathCurrent . $queryCar->id . '/' . $Image_car->image_name;
            }

            return response()->json([
                'queryCar' => $queryCar,
                'partner_car' => $partner_car,
                'dataBooking' => $dataBooking,
                'branch' => $branch,
                'dataPreviewBanks' => $dataPreviewBanks,
            ]);
        }
    }
    public function update_amountPrice(Request  $request)
    {

        set_time_limit(0);
        $get_car = Car::all();
        for ($i = 0; $i < count($get_car); $i++) {
            if ($get_car[$i]->VatSumOverCos == 0) {
                $get_car[$i]->VatSumOverCos =  (float)$get_car[$i]->car_buy + (float)$get_car[$i]->amount_overCost;

                $check_Outlay_cost = Outlay_cost::where([['car_id', $get_car[$i]->id], ['detail', 'ค่าตัวรถ']])->first();
                if (empty($check_Outlay_cost)) {
                    $Outlay_cost =   Outlay_cost::create([
                        'date' => $get_car[$i]->car_date_buy,
                        'no' => '',
                        'detail' => 'ค่าตัวรถ',
                        'type' => 1,
                        'type_bill' => 1,
                        'money' =>  $get_car[$i]->VatSumOverCos,
                        'car_id' =>  $get_car[$i]->id,
                        'user_id' =>   $request->user()->id,
                        'status_check' => 1,
                        'active' => 1,
                        'branch_id' =>   $get_car[$i]->branch_id,
                    ]);
                } else {
                    if ($check_Outlay_cost->money != $get_car[$i]->VatSumOverCos) {
                        $check_Outlay_cost->status_check = 1;
                        $check_Outlay_cost->active = 1;
                        $check_Outlay_cost->money = $get_car[$i]->VatSumOverCos;
                        $check_Outlay_cost->save();
                    }
                }
            } else {
                $check_Outlay_cost = Outlay_cost::where([['car_id', $get_car[$i]->id], ['detail', 'ค่าตัวรถ']])->first();
                if (empty($check_Outlay_cost)) {
                    $Outlay_cost =   Outlay_cost::create([
                        'date' => $get_car[$i]->car_date_buy,
                        'no' => '',
                        'detail' => 'ค่าตัวรถ',
                        'type' => 1,
                        'type_bill' => 1,
                        'money' =>  $get_car[$i]->VatSumOverCos,
                        'car_id' =>  $get_car[$i]->id,
                        'user_id' =>  $get_car[$i]->user_id,
                        'status_check' => 1,
                        'active' => 1,
                        'branch_id' =>   $get_car[$i]->branch_id,
                    ]);
                } else {
                    if ($check_Outlay_cost->money != $get_car[$i]->VatSumOverCos) {
                        $check_Outlay_cost->status_check = 1;
                        $check_Outlay_cost->active = 1;
                        $check_Outlay_cost->money = $get_car[$i]->VatSumOverCos;
                        $check_Outlay_cost->save();
                    }
                }
            }


            $working =  Working::where([['car_id', $get_car[$i]->id], ['status_del', 1]])->first();
            if (!empty($working)) {
                $appointment_bank = Appointment_bank::where([['working_id', $working->id]])->first();
                if (!empty($appointment_bank)) {
                    $check_Income = Income::where([['working_id', $working->id], ['car_id', $working->car_id], ['detail', 'ค่าตัวรถ']])->first();
                    if (empty($check_Income)) {

                        $Income =   Income::create([
                            'working_id' => $working->id,
                            'date' => $appointment_bank->appointment_bank_date,
                            'no' => '',
                            'shop' => $working->customer_name,
                            'detail' => 'ค่าตัวรถ',
                            'type' => 1,
                            'type_bill' => 1,
                            'money' => $appointment_bank->car_price,
                            'car_id' => $working->car_id,
                            'user_id' => $working->sale_id,
                            'status_check' => 1,
                            'active' => 1,
                            'branch_id' =>  $working->branch_id,
                        ]);
                    } else {
                        // $check_Income->money = $request->amount_slacken;
                        // $check_Income->save();
                        Income::find($check_Income->id)->update(
                            [
                                'money' => $appointment_bank->car_price
                            ]
                        );
                    }
                }



                $income = Income::where([['working_id', $working->id]])->update([
                    'shop' => $working->customer_name,
                ]);


                $contract = Contract::where([['working_id', $working->id]])->first();
                if (!empty($contract)) {
                    $get_car[$i]->car_sale_date = $contract->contract_date;
                    Booking::where('working_id', $working->id)->update([
                        'customer_car_date_release' => $contract->contract_date
                    ]);
                }

                $appointment_bank = Appointment_bank::where([['working_id', $working->id]])->first();
                if (!empty($appointment_bank)) {
                    Booking::where([['working_id', $working->id]])->update([
                        'price_middle' => $appointment_bank->car_price
                    ]);
                }
            }

            $net_price = Outlay_cost::where([['car_id', $get_car[$i]->id], ['active', 1], ['status_check', 1]])->sum('money');
            $expenses = Outlay_cost::where([['car_id', $get_car[$i]->id], ['detail', 'ค่าตัวรถ'], ['active', 1], ['status_check', 1]])->sum('money');
            $income = Income::where([['car_id', $get_car[$i]->id], ['active', 1], ['status_check', 1]])->sum('money');

            $get_car[$i]->expenses  = (float)$net_price - $expenses;
            $get_car[$i]->net_price  = $net_price;
            $get_car[$i]->income  = $income;

            $get_car[$i]->save();
        }
    }
}
