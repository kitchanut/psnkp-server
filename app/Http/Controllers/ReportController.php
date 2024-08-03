<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Appointment_bank;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function booking_car(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');
        $output = Working::with(['cars', 'cars.car_models', 'cars.car_series', 'cars.car_serie_sub', 'cars.color', 'branch', 'cars.province', 'cars.province_current'])
            ->where([['work_status', '>=', 2], ['status_del', 1]])
            ->with('bookings', function ($q) {
                $q->orderBy('created_at', 'DESC');
            })
            ->whereHas('bookings', function ($q) use ($timeStart, $timeEnd) {
                $q->where('created_at', '>=', $timeStart);
                $q->where('created_at', '<=', $timeEnd);
            })

            // ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json($output);
    }

    public function report_purchase_car(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');
        $output = Car::with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'branch', 'color', 'province', 'province_current'])
            ->where([['car_status', 1], ['car_date_buy', '>=', $timeStart], ['car_date_buy', '<=', $timeEnd]])
            ->orderBy('car_no', 'desc')
            ->get();
        return response()->json($output);
    }

    public function report_sale_car(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');
        $query = Car::with(['car_types', 'car_models', 'car_series', 'car_serie_sub', 'branch', 'color', 'contract', 'province', 'province_current', 'working.customer.customer_detail', 'working.appointment_banks', 'working.receiving_money'])
            ->where([['car_stock', 3], ['car_status', 1]])
            ->whereHas('contract', function ($q) use ($timeStart, $timeEnd) {
                $q->where('contract_date', '>=', $timeStart);
                $q->where('contract_date', '<=', $timeEnd);
            })
            ->orderBy('car_no', 'desc')
            ->get();

        // $map = $query->map(function ($item) {
        //     if ($item->working) {
        //         if ($item->working->customer->customer_job == 1) {
        //             $job = 'ข้าราชการ';
        //         } elseif ($item->working->customer->customer_job == 2) {
        //             $job = 'พนักงานเอกชน';
        //         } elseif ($item->working->customer->customer_job == 3) {
        //             $job = 'เกษตกร';
        //         } elseif ($item->working->customer->customer_job == 4) {
        //             $job = 'ค้าขาย';
        //         } elseif ($item->working->customer->customer_job == 5) {
        //             $job = 'อื่น ๆ - ' . $item->working->customer->customer_job_list;
        //         } else {
        //             $job = 'N/A';
        //         }
        //         $item->working->customer->job = $job;

        //         if ($item->working->customer->customer_detail->district) {
        //             $item->working->customer->address = $item->working->customer->customer_detail->customer_address . " ต." . $item->working->customer->customer_detail->district->name_th . " อ." . $item->working->customer->customer_detail->amphure->name_th . " จ." . $item->working->customer->customer_detail->province->name_th . " " . $item->working->customer->customer_detail->zip_code;
        //         } else {
        //             $item->working->customer->address = 'N/A';
        //         }

        //         if ($item->working->customer->customer_birthday_year) {
        //             $item->working->customer->age = (int)date("Y") + 543 - (int)$item->working->customer->customer_birthday_year;
        //         } else {
        //             $item->working->customer->age = 'N/A';
        //         }

        //         $item->working->credit =  $item->working->appointment_banks->credit;
        //     } else {
        //         $Newitem['customer']['job'] = 'ไม่มีข้อมูล';
        //         $Newitem['customer']['address'] = 'ไม่มีข้อมูล';
        //         $Newitem['customer']['age'] = 'ไม่มีข้อมูล';
        //         $Newitem['credit'] = 'ไม่มีข้อมูล';
        //         $item['working'] = $Newitem;
        //     }



        //     return $item;
        // });

        return response()->json($query);
    }

    public function report_work_cancle(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $query = Working::with([
            'cars', 'cars.car_models', 'cars.car_series', 'cars.car_serie_sub',
            'appointments', 'appointment_banks', 'banks', 'bank_branchs',
            'bookings', 'contract', 'cars.color',
            'sale', 'branch', 'team', 'branch_team'
        ])
            ->where([['status_del', 0]])
            ->where([['updated_at', '>=', $timeStart], ['updated_at', '<=', $timeEnd]])
            ->orderBy('updated_at', 'DESC')
            ->get();

        return response()->json($query);
    }

    public function report_booking_duplicate(Request $request)
    {
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');
        $query = Working::with(['cars', 'cars.car_types', 'cars.car_models', 'cars.car_series', 'cars.car_serie_sub', 'cars.color', 'cars.province', 'cars.province_current'])
            ->where([['created_at', '>=', $timeStart], ['created_at', '<=', $timeEnd]])
            ->select('car_id', DB::raw('count(*) as total'))
            ->groupBy('car_id')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json($query);
    }



    public function report_profit(Request $request)
    {
        $type = $request->input('type');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');
        $branch_team_id = $request->input('branch_team_id') ?? null;

        $query = Working::with(['income', 'expenses'])
            ->select('id', 'branch_team_id', 'car_id')
            ->with(['cars' => function ($q) {
                $q->select('id', 'car_no', 'amount_overCost');
            }])
            ->with(['contract' => function ($q) {
                $q->select('id', 'working_id', 'contract_date');
            }])
            ->with(['appointment_banks' => function ($q) {
                $q->select('id', 'working_id', 'appointment_money_date');
            }])
            ->with(['branch_team' => function ($q) {
                $q->select('id', 'branch_team_name');
            }])
            ->where('status_del', 1)
            ->when($branch_team_id, function ($query) use ($branch_team_id) {
                return $query->where('branch_team_id', $branch_team_id);
            });

        switch ($type) {
            case 'release':
                $query->whereHas('contract', function ($q) use ($timeStart, $timeEnd) {
                    $q->whereBetween('contract_date', [$timeStart, $timeEnd]);
                    $q->orderBy('contract_date', 'DESC');
                });
                break;
            case 'com':
                $commission_mount = explode(",", $request->input('mount'));
                $query->whereHas('appointment_banks', function ($q) use ($commission_mount) {
                    $q->whereIn('commission_mount', $commission_mount);
                });
                break;
            case 'money':
                $query->whereHas('appointment_banks', function ($q) use ($timeStart, $timeEnd) {
                    $q->whereBetween('appointment_money_date', [$timeStart, $timeEnd]);
                    $q->orderBy('appointment_money_date', 'DESC');
                });
                break;
            default:
                return response()->json(['error' => 'Invalid type'], 400);
        }

        $output = $query->get();
        return response()->json($output);
    }

    public function report_commission(Request $request)
    {
        // $timeStart = $request->input('timeStart');
        // $timeEnd = $request->input('timeEnd');

        $month = $request->input('month');

        $output = Working::with('sale', 'team', 'branch_team')
            ->whereHas('appointment_banks', function ($q) use ($month) {
                $q->where('commission_mount', '!=', NULL);
                $q->where('commission_mount', '!=', '');
                $q->where('commission_mount', $month);
            })
            ->select('sale_id', 'user_team_id', 'branch_team_id', DB::raw('count(*) as total'))
            ->groupBy('sale_id')
            ->orderBy('branch_team_id')
            ->orderBy('user_team_id')
            ->get();
        return response()->json($output);
    }
}
