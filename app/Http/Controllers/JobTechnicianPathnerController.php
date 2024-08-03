<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\JobTechnicianPathner;
use App\Models\Working;
use Illuminate\Http\Request;

class JobTechnicianPathnerController extends Controller
{
    protected $pathCurrent = 'cars/';

    public function index()
    {
        return response()->json(JobTechnicianPathner::with('partner_technicians', 'user', 'cars', 'branch')
            ->orderBy('id', 'DESC')
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
        $credentials = $request->except(['id']);
        $createJobTechnicianPathner =  JobTechnicianPathner::create($credentials);
        if ($request->working_id != 0) {
            $queryWorking = Working::find($request->working_id);
            $queryWorking->pathner_job_technician = 1;
            $queryWorking->user_id = $request->user()->id;
            $queryWorking->save();

            $car = Car::find($queryWorking->car_id);
            $sum_net_price = (float)$car->net_price + (float) $createJobTechnicianPathner->job_price;
            $car->net_price = $sum_net_price;
            $car->save();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JobTechnicianPathner  $jobTechnicianPathner
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(JobTechnicianPathner::findOrFail($id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobTechnicianPathner  $jobTechnicianPathner
     * @return \Illuminate\Http\Response
     */
    public function edit(JobTechnicianPathner $jobTechnicianPathner)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JobTechnicianPathner  $jobTechnicianPathner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JobTechnicianPathner $jobTechnicianPathner)
    {
        // $jobTechnicianPathner->update($request->except(['updated_at']));
        JobTechnicianPathner::where('id', $request->id)->update($request->except(['updated_at']));
        if ($request->job_status == 2) {
            if ($request->working_id != 0) {
                $queryWorking = Working::find($request->working_id);
                $queryWorking->pathner_job_technician = 0;
                $queryWorking->user_id = $request->user()->id;
                $queryWorking->save();
            }
        }
        // return response()->json($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobTechnicianPathner  $jobTechnicianPathner
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $jobTechnicianPathner->delete();
        $JobTechnicianPathner =  JobTechnicianPathner::find($id)->delete();

        // $car = Car::find($JobTechnicianPathner->car_id);
        // $sum_net_price = (float)$car->net_price - (float) $JobTechnicianPathner->job_price;
        // $sum_income = (float)$car->income + (float) $JobTechnicianPathner->job_price;
        // $car->net_price = $sum_net_price;
        // $car->income = $sum_income;
        // $car->save();
        // return response()->json($id);
    }

    public function PathnerJobTechnicianWhere(Request $request)
    {
        if ($request->branch_id == 0) {
            $data = JobTechnicianPathner::with('partner_technicians', 'user', 'cars.car_models', 'cars.car_series', 'branch')
                // ->where([['job_technician_pathners.job_status', '=', 1]])
                ->orderBy('job_status')
                ->orderBy('id', 'DESC')
                ->get();
        } else {
            $data = JobTechnicianPathner::with('partner_technicians', 'cars.car_models', 'cars.car_series', 'branch', 'user')
                // ->where([['job_technician_pathners.job_status', '=', 1], ['job_technician_pathners.branch_id', '=', $request->branch_id]])
                ->where([['job_technician_pathners.branch_id', '=', $request->branch_id]])
                ->orderBy('job_status')
                ->orderBy('id', 'DESC')
                ->get();
        }
        $data->map(function ($item) {
            $item['job_technician_pathner_list'] = nl2br(htmlspecialchars($item['job_technician_pathner_list']));
            if ($item['cars']['img_id_first'] == 0) {
                $item['cars']['img_id_first'] = '' . '/' . $this->path  . $this->pathCurrent . 'default/car_default.png';
            } else {
                if ($item['cars']['img_first']) {
                    $item['cars']['img_id_first'] = '' . '/' . $this->path . $this->pathCurrent  . $item['cars']['id'] . '/' . $item['cars']['img_first']['image_name'];
                } else {
                    $item['cars']['img_id_first'] = '' . '/' . $this->path  . $this->pathCurrent . 'default/car_default.png';
                }
            }
        });

        return response()->json($data);
    }

    public function JobTechnicianWhereCar_out($idCar)
    {
        $data = JobTechnicianPathner::with(['partner_technicians', 'user', 'cars', 'branch'])
            ->where([['car_id', $idCar]])
            ->get();
        $data->map(function ($item) {
            $item['job_technician_pathner_list'] = nl2br(htmlspecialchars($item['job_technician_pathner_list']));
        });

        return response()->json($data);
    }
}
