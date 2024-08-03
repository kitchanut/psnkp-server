<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Car_part;
use App\Models\JobTechnician;
use App\Models\Repair;
use App\Models\Repair_details;
use App\Models\Repair_price;
use App\Models\Withdraw_part;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobTechnicianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(JobTechnician::with(['cars', 'car_lift'])
            ->where([['job_technicians.job_status']])
            ->get());
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
        $JobTechnicians = $request->repair_details;
        foreach ($JobTechnicians as $key => $JobTechnician) {
            $createJobTechnician_detail = JobTechnician::create($JobTechnician);
            $queryRepair_details = Repair_details::where('repair_id', $JobTechnician['repair_id'])->first();
            if ($queryRepair_details != null) {
                $createWithdraw =  Withdraw_part::create([
                    'car_part_id' => $queryRepair_details->car_part_id,
                    'job_technician_id' => $createJobTechnician_detail->id,
                    'car_id' => $JobTechnician['car_id'],
                    'car_part_amount' => $queryRepair_details->car_part_value,
                    'withdraw_part_amount' => $queryRepair_details->car_part_value,
                    'balance_part_amount' => 0,
                    'user_id' => $JobTechnician['user_id'],
                    'branch_id' => $JobTechnician['branch_id'],
                    'withdraw_part_status' => 1,
                ]);
            }

            //อัพเดตสถานะของงาน
            if ($JobTechnician['working_id'] != 0) {
                $queryWorking = Working::find($JobTechnician['working_id']);
                $queryWorking->user_id = $request->user()->id;
                $queryWorking->job_fix = 1;
                $queryWorking->save();
            }
        }


        // return response()->json($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JobTechnician  $jobTechnician
     * @return \Illuminate\Http\Response
     */
    public function show(JobTechnician $jobTechnician)
    {
        $jobTechnician->repair_details;
        return response()->json($jobTechnician);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobTechnician  $jobTechnician
     * @return \Illuminate\Http\Response
     */
    public function edit(JobTechnician $jobTechnician)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JobTechnician  $jobTechnician
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JobTechnician $jobTechnician)
    {

        $JobTechnicians = $request->repair_details;
        foreach ($JobTechnicians as $key => $JobTechnician) {
            if (isset($JobTechnician['id']) == false) {
                $createJobTechnician_detail = JobTechnician::create($JobTechnician);

                $queryRepair_details = Repair_details::where('repair_id', $JobTechnician['repair_id'])->first();
                if ($queryRepair_details != null) {
                    $createWithdraw =  Withdraw_part::create([
                        'car_part_id' => $queryRepair_details->car_part_id,
                        'job_technician_id' => $createJobTechnician_detail->id,
                        'car_id' => $JobTechnician['car_id'],
                        'car_part_amount' => $queryRepair_details->car_part_value,
                        'withdraw_part_amount' => $queryRepair_details->car_part_value,
                        'balance_part_amount' => 0,
                        'user_id' => $JobTechnician['user_id'],
                        'branch_id' => $JobTechnician['branch_id'],
                        'withdraw_part_status' => 1,
                    ]);
                }
            } else {
                $createJobTechnician_detail = JobTechnician::find($JobTechnician['id']);

                if ($createJobTechnician_detail->repair_id != $JobTechnician['repair_id']) {
                    $queryRepair_details = Repair_details::where('repair_id', $JobTechnician['repair_id'])->first();
                    if ($queryRepair_details != null) {
                        $createWithdraw =  Withdraw_part::create([
                            'car_part_id' => $queryRepair_details->car_part_id,
                            'job_technician_id' => $createJobTechnician_detail->id,
                            'car_id' => $JobTechnician['car_id'],
                            'car_part_amount' => $queryRepair_details->car_part_value,
                            'withdraw_part_amount' => $queryRepair_details->car_part_value,
                            'balance_part_amount' => 0,
                            'user_id' => $JobTechnician['user_id'],
                            'branch_id' => $JobTechnician['branch_id'],
                            'withdraw_part_status' => 1,
                        ]);
                    }
                }
            }
        }

        if ($request->working_id != 0) {
            $queryWorking = Working::find($request->working_id);
            $queryWorking->user_id = $request->user()->id;
            $queryWorking->job_fix = 1;
            $queryWorking->save();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobTechnician  $jobTechnician
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobTechnician $jobTechnician)
    {
        //
    }


    public function checkTechnician($idWork, $idCar)
    {

        if ($idWork == 0) {
            $query = Car::find($idCar);
            $query->car_id = $query->id;
            $query->working_id = $idWork;
            $query->action = "add";
        } else {
            $checkTechnician = JobTechnician::where('working_id', $idWork)->first();
            if ($checkTechnician == null || $checkTechnician == '') {
                $query = DB::table('workings')
                    ->join('cars', 'workings.car_id', '=', 'cars.id')
                    ->where('workings.id', $idWork)
                    ->first();
                $query->working_id = $idWork;
                $query->action = "add";
            } else {
                $query = DB::table('cars')
                    ->where('cars.id', $idCar)
                    ->first();
                $query->repair_details =  JobTechnician::where('working_id', $idWork)->get();
                $query->working_id = $idWork;
                $query->action = "edit";
            }
        }
        return response()->json($query);
    }

    public function SelectOnJob($idJob)
    {
        $detailJob =  JobTechnician::with(['cars', 'car_lift', 'user', 'repair'])->where('job_technicians.id', $idJob)->get();
        return response()->json($detailJob);
    }



    public function updateOnJob($idJob, $job_status)
    {
        $queryJob = JobTechnician::find($idJob);
        $withdraw_part_status = '';
        if ($job_status == 2) {
            $queryWithdraw_part = Withdraw_part::where('job_technician_id', $idJob)->first();
            $withdraw_part_status = $queryWithdraw_part->withdraw_part_status;
            $queryJob->job_start = date("Y-m-d H:i");
        } else if ($job_status == 3) {
            $queryJob->job_pasue = date("Y-m-d H:i");
        } else if ($job_status == 4) {
            $queryJob->job_end = date("Y-m-d H:i");
            $findRepair = Repair::find($queryJob->repair_id);
            $findRepairPart = Repair_details::where('repair_id', $findRepair->id)->get();
            $sum_part = 0;
            for ($i = 0; $i < count($findRepairPart); $i++) {
                $findPrice = Car_part::find($findRepairPart[$i]['car_part_id']);
                $sum_part = (float)$sum_part + ((float)$findPrice->car_part_buy * (int)$findRepairPart[$i]['car_part_value']);
            }
            $createRepair_price = Repair_price::create([
                'job_technician_id' => $queryJob->id,
                'car_id' => $queryJob->car_id,
                'repair_id' => $queryJob->repair_id,
                'job_type' => $queryJob->job_type,
                'car_part_buy' => $sum_part
            ]);
        } else if ($job_status == 5) {
            $queryJob->job_cancel = date("Y-m-d H:i");

            $queryWithdraw_part = Withdraw_part::where('job_technician_id', $idJob)->first();
            $queryWithdraw_part->withdraw_part_status = 3;

            $queryWithdraw_part->save();
        }

        if ($withdraw_part_status != 1) {
            $queryJob->job_status = $job_status;
            $queryJob->save();
        }


        if ($queryJob->working_id != 0) {
            $checkJob_fix = JobTechnician::where([['working_id', $queryJob->working_id], ['job_status', '<', 4]])
                ->get();
            // $queryWorking = Working::find($queryJob->working_id);
            // $queryWorking->job_fix = count($checkJob_fix);
            // $queryWorking->save();
            if (count($checkJob_fix) > 0) {
                $queryWorking = Working::find($queryJob->working_id);
                $queryWorking->job_fix = 0;
                $queryWorking->save();
            } else {
                $queryWorking = Working::find($queryJob->working_id);
                $queryWorking->job_fix = 0;
                $queryWorking->save();
            }
        }

        return response()->json($withdraw_part_status);
    }



    public function JobTechnicianWhere(Request $request)
    {
        // return response()->json($request);
        if ($request->branch_id == 0) {
            $data = JobTechnician::with(['cars', 'car_lift', 'user', 'repair'])
                // ->where([['job_technicians.job_status', '<', 4]])
                ->get();
        } else {
            $data = JobTechnician::with(['cars', 'car_lift', 'user', 'repair'])
                // ->where([['job_technicians.job_status', '<', 4], ['job_technicians.branch_id', '=', $request->branch_id]])
                ->where([['job_technicians.branch_id', '=', $request->branch_id]])
                ->get();
        }
        return response()->json($data);
    }


    public function JobTechnicianWhereCar($idCar)
    {
        // return response()->json($request);
        $data = JobTechnician::with(['cars', 'car_lift', 'user', 'repair'])
            ->where([['job_technicians.car_id', '=', $idCar]])
            ->get();
        return response()->json($data);
    }
}
