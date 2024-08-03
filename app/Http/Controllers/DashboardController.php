<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Contract;
use App\Models\Dashboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dashboard  $dashboard
     * @return \Illuminate\Http\Response
     */
    public function show(Dashboard $dashboard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Dashboard  $dashboard
     * @return \Illuminate\Http\Response
     */
    public function edit(Dashboard $dashboard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Dashboard  $dashboard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Dashboard $dashboard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Dashboard  $dashboard
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dashboard $dashboard)
    {
        //
    }
    public function dashboard_manager_bar_car(Request $request)
    {
        $branch_id = $request->input('branch_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $car_buy = DB::table('cars')
            ->where('car_status', 1)
            ->whereBetween('car_date_buy', [$timeStart, $timeEnd])
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->select(DB::raw("sum(car_buy) as data"), DB::raw('MONTH(car_date_buy) month'))
            ->groupBy(DB::raw('MONTH(car_date_buy)'))
            ->get();
        $newDataCar_buy = $this->newDataCar($car_buy);
        $sumCar_buy = array_sum($newDataCar_buy);

        $count_car_buy = DB::table('cars')
            ->where('car_status', 1)
            ->whereBetween('car_date_buy', [$timeStart, $timeEnd])
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->select(DB::raw("count(cars.id) as data"), DB::raw('MONTH(car_date_buy) month'))
            ->groupBy(DB::raw('MONTH(car_date_buy)'))
            ->get();

        $newDataCountCar_buy = $this->newDataCar($count_car_buy);
        // $countCar_buy = array_sum($newDataCountCar_buy);

        $car_sale = DB::table('cars')
            ->join('contracts', 'cars.id', '=', 'contracts.car_id')
            // ->where([['car_stock', '=', 3]])
            ->whereBetween('contracts.contract_date', [$timeStart, $timeEnd])
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->select(DB::raw("sum(car_price) as data"), DB::raw('MONTH(contracts.contract_date) month'))
            ->groupBy(DB::raw('MONTH(contracts.contract_date)'))
            ->get();

        $newDataCar_sale = $this->newDataCar($car_sale);
        $sumCar_sale = array_sum($newDataCar_sale);

        $count_car_sale = DB::table('cars')
            ->join('contracts', 'cars.id', '=', 'contracts.car_id')
            // ->where([['car_stock', '=', 3]])
            ->whereBetween('contracts.contract_date', [$timeStart, $timeEnd])
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->select(DB::raw("count(cars.id) as data"), DB::raw('MONTH(contracts.contract_date) month'))
            ->groupBy(DB::raw('MONTH(contracts.contract_date)'))
            ->get();

        $newDataCountCar_sale = $this->newDataCar($count_car_sale);
        // $countCar_sale = array_sum($newDataCountCar_sale);

        return response()->json([
            'car_buy' => $newDataCar_buy,
            'sumCar_buy' => $sumCar_buy,
            'car_sale' => $newDataCar_sale,
            'sumCar_sale' => $sumCar_sale,
            'countCar_buy' => $newDataCountCar_buy,
            'countCar_sale' => $newDataCountCar_sale
        ]);
    }
    public function dashboard_manager_doughnut_stock(Request $request)
    {
        $car_stock = $request->input('car_stock');
        $branch_id = $request->input('branch_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        if ($car_stock == 3) {
            $stock_sum_cars = Car::with('contract')
                ->where('car_status', 1)
                ->whereHas('contract', function ($q) use ($timeStart, $timeEnd) {
                    $q->where('contract_date', '>=', $timeStart);
                    $q->where('contract_date', '<=', $timeEnd);
                })
                ->when($car_stock, function ($query) use ($car_stock) {
                    return $query->where('car_stock', $car_stock);
                })
                ->when($branch_id, function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                })
                ->selectRaw('car_stock as stock, sum(car_buy) as car_buy, count(car_stock) as count')
                ->groupBy('car_stock')
                ->get();
        } else {
            $stock_sum_cars = Car::with('contract')
                ->where('car_status', 1)
                ->when($car_stock, function ($query) use ($car_stock) {
                    return $query->where('car_stock', $car_stock);
                })
                ->when($branch_id, function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                })
                ->selectRaw('car_stock as stock, sum(car_buy) as car_buy, count(car_stock) as count')
                ->groupBy('car_stock')
                ->get();
        }


        $newLabels_car = ['รอรับเข้าคลัง', 'อยู่ในคลัง', 'ขายออก',];
        $newColors_car = ['#E67B19', '#007BC5', '#009442'];
        $newDatas_car = [0, 0, 0];
        $newDatas_price = [0, 0, 0];
        foreach ($stock_sum_cars as $key => $stock_sum_car) {
            if ($stock_sum_car->stock == 1) {
                $newDatas_car[0] += $stock_sum_car->count;
                $newDatas_price[0] += $stock_sum_car->car_buy;
            } else if ($stock_sum_car->stock == 2) {
                $newDatas_car[1] += $stock_sum_car->count;
                $newDatas_price[1] += $stock_sum_car->car_buy;
            } else if ($stock_sum_car->stock == 3) {
                $newDatas_car[2] += $stock_sum_car->count;
                $newDatas_price[2] += $stock_sum_car->car_buy;
            }
        }
        return response()->json([
            'car_labels' => $newLabels_car,
            'car_backgroundColor' => $newColors_car,
            'car_data' => $newDatas_car,
            'car_price' => $newDatas_price,
            'car_sum' => array_sum($newDatas_car),
            'car_sum_price' => array_sum($newDatas_price),
        ]);
    }

    public function dashboard_manager_bar_visit(Request $request)
    {
        $customer_visits = DB::table('customer_visits')
            ->where(
                $request->branch_id == 0 ?
                    [['created_at', '>=', $request->timeStart], ['created_at', '<=', $request->timeEnd], ['del', 1]]
                    :
                    [['branch_id', '=', $request->branch_id], ['created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd], ['del', 1]]
            )
            ->select(
                DB::raw('sum(del) as data'),
                DB::raw('MONTH(customer_visits.created_at) month')
            )
            ->groupBy(('del'), DB::raw('MONTH(customer_visits.created_at)'))
            ->get();
        return response()->json($customer_visits);
    }

    public function dashboard_manager_bar_visit_car_type(Request $request)
    {
        $customer_visits_car_datas = DB::table('customer_visits')
            ->join('car_types', 'customer_visits.car_types_id', '=', 'car_types.id')
            ->where(
                $request->branch_id == 0 ?
                    [['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                    :
                    [['customer_visits.branch_id', '=', $request->branch_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
            )
            ->select('customer_visits.car_types_id as car_types_id', 'car_types.car_type_name as label', 'car_types.car_type_name_en as label2', 'car_types.car_type_code_color as backgroundColor')
            ->groupBy('customer_visits.car_types_id', 'car_types.car_type_name', 'car_types.car_type_name_en', 'car_types.car_type_code_color')
            ->get();

        $dataOutputs = [];

        foreach ($customer_visits_car_datas as $key => $customer_visits_car_data) {
            $rawData = DB::table('customer_visits')
                ->join('car_types', 'customer_visits.car_types_id', '=', 'car_types.id')
                ->where(
                    $request->branch_id == 0 ?
                        [['customer_visits.car_types_id', '=', $customer_visits_car_data->car_types_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                        :
                        [['customer_visits.car_types_id', '=', $customer_visits_car_data->car_types_id], ['customer_visits.branch_id', '=', $request->branch_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                )
                ->select(DB::raw("count(customer_visits.car_types_id) as data"), DB::raw('MONTH(customer_visits.created_at) month'))
                ->groupBy(DB::raw('MONTH(customer_visits.created_at)'))
                ->get();
            $dataOutputs[$key]['label'] = $customer_visits_car_data->label . '(' . $customer_visits_car_data->label2 . ')';
            $dataOutputs[$key]['backgroundColor'] = $customer_visits_car_data->backgroundColor;
            $dataOutputs[$key]['data'] = $this->newDataVisit($rawData);
        }
        return response()->json($dataOutputs);
    }


    public function dashboard_manager_bar_visit_car_model(Request $request)
    {
        $customer_visits_car_models = DB::table('customer_visits')
            ->join('car_models', 'customer_visits.car_models_id', '=', 'car_models.id')
            ->where(
                $request->branch_id == 0 ?
                    [['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                    :
                    [['customer_visits.branch_id', '=', $request->branch_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
            )
            ->select('customer_visits.car_models_id as car_models_id', 'car_models.car_model_name as label', 'car_models.car_model_code_color as backgroundColor')
            ->groupBy('customer_visits.car_models_id', 'car_models.car_model_name', 'car_models.car_model_code_color')
            ->get();

        $dataOutputs = [];

        foreach ($customer_visits_car_models as $key => $customer_visits_car_data) {
            $rawData = DB::table('customer_visits')
                ->join('car_models', 'customer_visits.car_models_id', '=', 'car_models.id')
                ->where(
                    $request->branch_id == 0 ?
                        [['customer_visits.car_models_id', '=', $customer_visits_car_data->car_models_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                        :
                        [['customer_visits.car_models_id', '=', $customer_visits_car_data->car_models_id], ['customer_visits.branch_id', '=', $request->branch_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                )
                ->select(DB::raw("count(customer_visits.car_models_id) as data"), DB::raw('MONTH(customer_visits.created_at) month'))
                ->groupBy(DB::raw('MONTH(customer_visits.created_at)'))
                ->get();
            $dataOutputs[$key]['label'] = $customer_visits_car_data->label;
            $dataOutputs[$key]['backgroundColor'] = $customer_visits_car_data->backgroundColor;
            $dataOutputs[$key]['data'] = $this->newDataVisit($rawData);
        }
        return response()->json($dataOutputs);
    }

    public function dashboard_manager_bar_visit_car_serie(Request $request)
    {
        $branch_id = $request->input('branch_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');


        $customer_visits_car_series = DB::table('customer_visits')
            ->join('car_series', 'customer_visits.car_serie_id', '=', 'car_series.id')
            ->whereBetween('customer_visits.created_at', [$timeStart, $timeEnd])
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('customer_visits.branch_id', $branch_id);
            })


            ->select('customer_visits.car_serie_id as car_serie_id', 'car_series.car_series_name as label', 'car_series.car_serie_code_color as backgroundColor')
            ->groupBy('customer_visits.car_serie_id', 'car_series.car_series_name', 'car_series.car_serie_code_color')
            ->get();

        $dataOutputs = [];

        foreach ($customer_visits_car_series as $key => $customer_visits_car_data) {
            $rawData = DB::table('customer_visits')
                ->join('car_series', 'customer_visits.car_serie_id', '=', 'car_series.id')
                ->whereBetween('customer_visits.created_at', [$timeStart, $timeEnd])
                ->when($branch_id, function ($query) use ($branch_id) {
                    return $query->where('customer_visits.branch_id', $branch_id);
                })
                ->where(
                    [['customer_visits.car_serie_id', '=', $customer_visits_car_data->car_serie_id]]
                )
                ->select(DB::raw("count(customer_visits.car_serie_id) as data"), DB::raw('MONTH(customer_visits.created_at) month'))
                ->groupBy(DB::raw('MONTH(customer_visits.created_at)'))
                ->get();
            $dataOutputs[$key]['label'] = $customer_visits_car_data->label;
            $dataOutputs[$key]['backgroundColor'] = $customer_visits_car_data->backgroundColor;
            $dataOutputs[$key]['data'] = $this->newDataVisit($rawData);
        }
        return response()->json($dataOutputs);
    }

    public function dashboard_manager_bar_top_car_serie(Request $request)
    {
        // $branch_id = $request->input('branch_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $output = Contract::with('cars.car_series', 'cars.car_types')
            ->with(['cars' => function ($query) {
                $query->select('id', 'car_serie_id', 'car_types_id');
            }])
            ->select('id', 'car_id')
            ->whereBetween('contract_date', [$timeStart, $timeEnd])
            ->get()
            ->groupBy('cars.car_serie_id')
            ->all();

        $newOutput = [];
        $index = 0;
        foreach ($output as $key => $value) {
            $newOutput[$index]['car_types_id'] = $value[0]->cars->car_types->id;
            $newOutput[$index]['car_serie_name'] = $value[0]->cars->car_series->car_series_name;
            $newOutput[$index]['count'] = intval(count($value));
            $index++;
        }

        // $customer_visits_car_series = DB::table('contracts')
        //     ->join('cars', 'contracts.car_id', '=', 'cars.id')
        //     ->join('car_series', 'cars.car_serie_id', '=', 'car_series.id')
        //     ->whereBetween('contracts.contract_date', [$timeStart, $timeEnd])
        //     ->when($branch_id, function ($query) use ($branch_id) {
        //         return $query->where('cars.branch_id', $branch_id);
        //     })
        //     ->select('cars.car_serie_id as car_serie_id', 'car_series.car_series_name as label', 'car_series.car_serie_code_color as backgroundColor')
        //     ->groupBy('cars.car_serie_id', 'car_series.car_series_name', 'car_series.car_serie_code_color')
        //     ->get();

        // $dataOutputs = [];

        // foreach ($customer_visits_car_series as $key => $customer_visits_car_data) {
        //     $rawData = DB::table('cars')
        //         ->join('car_series', 'cars.car_serie_id', '=', 'car_series.id')
        //         ->join('contracts', 'cars.id', '=', 'contracts.car_id')
        //         ->whereBetween('contracts.contract_date', [$timeStart, $timeEnd])
        //         ->when($branch_id, function ($query) use ($branch_id) {
        //             return $query->where('cars.branch_id', $branch_id);
        //         })
        //         ->where(
        //             [['cars.car_serie_id', '=', $customer_visits_car_data->car_serie_id]]
        //         )
        //         ->select(DB::raw("count(cars.car_serie_id) as data"), DB::raw('MONTH(contracts.contract_date) month'))
        //         ->groupBy(DB::raw('MONTH(contracts.contract_date)'))
        //         ->get();
        //     $dataOutputs[$key]['label'] = $customer_visits_car_data->label;
        //     $dataOutputs[$key]['backgroundColor'] = $customer_visits_car_data->backgroundColor;
        //     $dataOutputs[$key]['data'] = $this->newDataVisit($rawData);
        //     $dataOutputs[$key]['data_sum'] = array_sum($dataOutputs[$key]['data']);
        // }
        return response()->json($newOutput);
    }


    public function dashboard_manager_bar_visit_car_slacken(Request $request)
    {
        $customer_visits_amount_slackens = DB::table('customer_visits')
            ->join('amount_slackens', 'customer_visits.car_serie_id', '=', 'amount_slackens.id')
            ->where(
                $request->branch_id == 0 ?
                    [['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                    :
                    [['customer_visits.branch_id', '=', $request->branch_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
            )
            ->select('customer_visits.car_serie_id as car_serie_id', 'amount_slackens.amount_slacken_name as label', 'amount_slackens.amount_slacken_code_color as backgroundColor')
            ->groupBy('customer_visits.car_serie_id', 'amount_slackens.amount_slacken_name', 'amount_slackens.amount_slacken_code_color')
            ->get();

        $dataOutputs = [];

        foreach ($customer_visits_amount_slackens as $key => $customer_visits_car_data) {
            $rawData = DB::table('customer_visits')
                ->join('amount_slackens', 'customer_visits.car_serie_id', '=', 'amount_slackens.id')
                ->where(
                    $request->branch_id == 0 ?
                        [['customer_visits.car_serie_id', '=', $customer_visits_car_data->car_serie_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                        :
                        [['customer_visits.car_serie_id', '=', $customer_visits_car_data->car_serie_id], ['customer_visits.branch_id', '=', $request->branch_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                )
                ->select(DB::raw("count(customer_visits.car_serie_id) as data"), DB::raw('MONTH(customer_visits.created_at) month'))
                ->groupBy(DB::raw('MONTH(customer_visits.created_at)'))
                ->get();
            $dataOutputs[$key]['label'] = $customer_visits_car_data->label;
            $dataOutputs[$key]['backgroundColor'] = $customer_visits_car_data->backgroundColor;
            $dataOutputs[$key]['data'] = $this->newDataVisit($rawData);
        }
        return response()->json($customer_visits_amount_slackens);
    }

    public function dashboard_manager_bar_visit_car_down(Request $request)
    {
        $customer_visits_amount_downs = DB::table('customer_visits')
            ->join('amount_downs', 'customer_visits.car_serie_id', '=', 'amount_downs.id')
            ->where(
                $request->branch_id == 0 ?
                    [['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                    :
                    [['customer_visits.branch_id', '=', $request->branch_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
            )
            ->select('customer_visits.car_serie_id as car_serie_id', 'amount_downs.amount_down_name as label', 'amount_downs.amount_down_code_color as backgroundColor')
            ->groupBy('customer_visits.car_serie_id', 'amount_downs.amount_down_name', 'amount_downs.amount_down_code_color')
            ->get();

        $dataOutputs = [];

        foreach ($customer_visits_amount_downs as $key => $customer_visits_car_data) {
            $rawData = DB::table('customer_visits')
                ->join('amount_downs', 'customer_visits.car_serie_id', '=', 'amount_downs.id')
                ->where(
                    $request->branch_id == 0 ?
                        [['customer_visits.car_serie_id', '=', $customer_visits_car_data->car_serie_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                        :
                        [['customer_visits.car_serie_id', '=', $customer_visits_car_data->car_serie_id], ['customer_visits.branch_id', '=', $request->branch_id], ['customer_visits.created_at', '>=', $request->timeStart], ['customer_visits.created_at', '<=', $request->timeEnd]]
                )
                ->select(DB::raw("count(customer_visits.car_serie_id) as data"), DB::raw('MONTH(customer_visits.created_at) month'))
                ->groupBy(DB::raw('MONTH(customer_visits.created_at)'))
                ->get();
            $dataOutputs[$key]['label'] = $customer_visits_car_data->label;
            $dataOutputs[$key]['backgroundColor'] = $customer_visits_car_data->backgroundColor;
            $dataOutputs[$key]['data'] = $this->newDataVisit($rawData);
        }
        return response()->json($dataOutputs);
    }

    public function dashboard_sale_doughnut(Request $request)
    {
        $working_sale_progresses = DB::table('workings')
            ->where(
                $request->work_status == 3 ?
                    [['workings.sale_id', '=', $request->user_id], ['workings.created_at', '>=', $request->timeStart], ['workings.created_at', '<=', $request->timeEnd]]
                    :
                    [['workings.work_status', '=', $request->work_status], ['workings.sale_id', '=', $request->user_id], ['workings.created_at', '>=', $request->timeStart], ['workings.created_at', '<=', $request->timeEnd]]
            )
            ->select('workings.work_status as status', DB::raw("count(workings.work_status) as count"))
            ->groupBy('workings.work_status')
            ->get();

        $working_sale_coms = DB::table('receiving_moneys')
            ->where(
                [['receiving_moneys.sale_id', '=', $request->user_id], ['receiving_moneys.created_at', '>=', $request->timeStart], ['receiving_moneys.created_at', '<=', $request->timeEnd]]
            )
            ->select('receiving_moneys.receiving_money_status as status', DB::raw("sum(receiving_moneys.commission) as commission"))
            ->groupBy('receiving_moneys.receiving_money_status')
            ->get();


        $newLabels_progress = ['ขายได้', 'กำลังดำเนินการ'];
        $newColors = ['#009442', '#007BC5'];
        $newDatas = [0, 0];

        foreach ($working_sale_progresses as $key => $working_sale_progress) {
            if ($working_sale_progress->status == 11) {
                $newDatas[0] += $working_sale_progress->count;
            } else if ($working_sale_progress->status <= 10) {
                $newDatas[1] += $working_sale_progress->count;
            }
        }

        $newLabels_com = ['ค่าคอมที่ได้รับ', 'ค่าคอมที่ยังไม่ได้รับ'];
        $newColors_com = ['#007BC5', '#E67B19'];
        $newDatas_com = [0, 0];

        foreach ($working_sale_coms as $key => $working_sale_com) {
            if ($working_sale_com->status == 2) {
                $newDatas_com[0] += $working_sale_com->commission;
            } else if ($working_sale_com->status == 1) {
                $newDatas_com[1] += $working_sale_com->commission;
            }
        }

        return response()->json([
            'labels' => $newLabels_progress,
            'backgroundColor' => $newColors,
            'data' => $newDatas,
            'data_sum' => array_sum($newDatas),
            'labels_com' => $newLabels_com,
            'backgroundColor_com' => $newColors_com,
            'data_com' => $newDatas_com,
            'data_sum_com' => array_sum($newDatas_com),
        ]);
        // return response()->json($working_sale_progresses);
    }

    public function dashboard_sale_bar(Request $request)
    {
        $car_sale = DB::table('workings')
            ->where(
                [['workings.sale_id', '=', $request->sale_id], ['workings.work_status', '>=', 7], ['workings.created_at', '>=', $request->timeStart], ['workings.created_at', '<=', $request->timeEnd]]
            )
            ->select(DB::raw("count(workings.work_status) as data"), DB::raw('MONTH(workings.created_at) month'))
            ->groupBy(('work_status'), DB::raw('MONTH(workings.created_at)'))
            ->get();
        return response()->json($car_sale);
    }

    public function newDataCar($rawDatas)
    {
        $dataArray = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        foreach ($rawDatas as $key => $rawData) {
            $dataArray[((int)$rawData->month - 1)] = $rawData->data;
        }
        return $dataArray;
    }

    public function newDataVisit($rawDatas)
    {
        $dataArray = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        foreach ($rawDatas as $key => $rawData) {
            $dataArray[((int)$rawData->month - 1)] = $rawData->data;
        }
        return $dataArray;
    }

    public function dashboard_saleByBranch(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');
        $arr = explode("-", $timeStart);
        $commission_mount = $arr[0] . '-' . $arr[1];


        $car_sale = DB::table('workings')
            ->leftJoin('appointment_banks', 'workings.id', '=', 'appointment_banks.working_id')
            ->where('appointment_banks.commission_mount', $commission_mount)
            ->where('workings.status_del', 1)
            ->select(DB::raw("count(workings.id) as count"), DB::raw('branch_team_id'))
            ->groupBy(DB::raw('branch_team_id'))
            ->get();

        $sum = DB::table('workings')
            ->leftJoin('appointment_banks', 'workings.id', '=', 'appointment_banks.working_id')
            ->where('appointment_banks.commission_mount', $commission_mount)
            ->where('workings.status_del', 1)
            ->select(DB::raw("count(workings.id) as count"))
            ->get();

        return response()->json([
            'car_sale' => $car_sale,
            'sumCar_sale' => $sum[0]->count,
        ]);
    }

    public function dashboard_inventory_car(Request $request)
    {
        $inventory_car = DB::table('cars')
            ->join('car_series', 'cars.car_serie_id', '=', 'car_series.id')
            ->where('cars.car_stock', '!=', 3)
            ->where('cars.car_status', 1)
            ->select(DB::raw("count(cars.car_serie_id) as count"), 'cars.car_stock', 'cars.branch_id', 'cars.car_serie_id as car_serie_id', 'car_series.car_series_name as label')
            ->groupBy('cars.car_serie_id', 'cars.branch_id', 'cars.car_stock')
            ->orderBy('label')
            ->get();

        $car_series = DB::table('cars')
            ->join('car_series', 'cars.car_serie_id', '=', 'car_series.id')
            ->join('car_models', 'cars.car_models_id', '=', 'car_models.id')
            ->select('cars.car_serie_id as car_serie_id', 'car_series.car_series_name as label', 'car_series.car_series_minimum', 'car_models.car_model_name')
            ->where('cars.car_status', 1)
            ->where('car_series.car_series_active', 1)
            ->groupBy('cars.car_serie_id')
            ->orderBy('car_models.car_model_name')
            ->orderBy('label')
            ->get();


        return response()->json([
            'inventory_car' => $inventory_car,
            'car_series' => $car_series,
        ]);
    }
    public function dashboard_car_registration(Request $request)
    {
        $tax_date = Car::select(
            'id',
            'car_no',
            // 'car_models_id',
            // 'car_serie_id',
            'car_regis',
            'tex_date',
            'car_stock',
            'car_status',
        )
            // ->with(['car_models', 'car_series'])
            ->where('car_stock', 2)
            ->where('car_status', 1)
            ->where('tex_date', '!=', '')
            ->orderBy('tex_date', 'asc')
            ->get();

        $no_tax_date = Car::select(
            'id',
            'car_no',
            // 'car_models_id',
            // 'car_serie_id',
            'car_regis',
            'tex_date',
            'car_stock',
            'car_status',
        )
            ->where('car_stock', 2)
            ->where('car_status', 1)
            ->where('tex_date', null)
            ->get();

        $tax_date_sale = Car::select(
            'id',
            'car_no',
            // 'car_models_id',
            // 'car_serie_id',
            'car_regis',
            'tex_date',
            'car_stock',
            'car_status',
        )
            // ->with(['car_models', 'car_series'])
            ->where('car_stock', 3)
            ->where('car_status', 1)
            ->where('tex_date', '!=', '')
            ->orderBy('tex_date', 'asc')
            ->get();

        $no_tax_date_sale = Car::select(
            'id',
            'car_no',
            // 'car_models_id',
            // 'car_serie_id',
            'car_regis',
            'tex_date',
            'car_stock',
            'car_status',
        )
            ->where('car_stock', 3)
            ->where('car_status', 1)
            ->where('tex_date', null)
            ->get();

        return response()->json([
            'tax_date' => $tax_date,
            'no_tax_date' => $no_tax_date,
            'tax_date_sale' => $tax_date_sale,
            'no_tax_date_sale' => $no_tax_date_sale,
        ]);
    }

    public function dashboard_car_insurances(Request $request)
    {
        $car_stock = Car::select(
            'id',
            'car_serie_id',
            'car_no',
            'car_regis',
            'car_stock',
            'car_status',
        )
            ->with(['insurance', 'car_series'])
            ->where('car_stock', 3)
            // ->where('car_status', 1)
            ->get();

        $car_stock_insurance = [];
        $car_stock_not_insurance = [];


        foreach ($car_stock as $key => $item) {
            if (isset($item['insurance'][0])) {
                $filter['car'] = $item;
                $filter['car_insurance_class'] = $item->insurance[0]->insurance_class;
                $filter['car_insurance_end'] = $item->insurance[0]->insurance_end;
                $car_stock_insurance[] = $filter;
            } else {
                $car_stock_not_insurance[]['car'] = $item;
            }
        }

        usort($car_stock_insurance, fn ($a, $b) => strcmp($a['car_insurance_end'], $b['car_insurance_end']));


        return response()->json([
            'car_stock_insurance' => $car_stock_insurance,
            'car_stock_not_insurance' => $car_stock_not_insurance,
        ]);
    }
}
