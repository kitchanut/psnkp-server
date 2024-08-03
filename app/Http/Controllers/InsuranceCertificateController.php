<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Car_model;
use App\Models\Insurance_certificate;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceCertificateController extends Controller
{
    /**
     * Display a listing of the resource.s
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
        $credentials = $request->only([
            'working_id', 'car_id', 'customer_name', 'customer_address', 'amphure', 'district', 'province', 'zip_code', 'customer_tel', 'insurance_certificate_date', 'car_mileage', 'first_name', 'tel'
        ]);
        $createJobTechnician = Insurance_certificate::create($credentials);

        //อัพเดตสถานะของงาน
        $updateStatus = Working::find($request->working_id);
        $updateStatus->user_id = $request->user()->id;
        $updateStatus->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Insurance_certificate  $insurance_certificate
     * @return \Illuminate\Http\Response
     */
    public function show(Insurance_certificate $insurance_certificate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Insurance_certificate  $insurance_certificate
     * @return \Illuminate\Http\Response
     */
    public function edit(Insurance_certificate $insurance_certificate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Insurance_certificate  $insurance_certificate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Insurance_certificate $insurance_certificate)
    {
        // $insurance_certificate->update($credentials);
        $updateInsurance_certificate = Insurance_certificate::find($request->id);
        $updateInsurance_certificate->working_id = $request->working_id;
        $updateInsurance_certificate->car_id = $request->car_id;
        $updateInsurance_certificate->customer_name = $request->customer_name;
        $updateInsurance_certificate->customer_address = $request->customer_address;
        $updateInsurance_certificate->customer_tel = $request->customer_tel;
        $updateInsurance_certificate->car_mileage = $request->car_mileage;
        $updateInsurance_certificate->insurance_certificate_date = $request->insurance_certificate_date;
        $updateInsurance_certificate->first_name = $request->first_name;
        $updateInsurance_certificate->tel = $request->tel;
        $updateInsurance_certificate->save();

        $credentials = $request->except(['updated_at', 'repair_details', 'action', 'id']);

        //อัพเดตสถานะของงาน
        $queryWorking = Working::find($request->working_id);
        $queryWorking->user_id = $request->user()->id;
        $queryWorking->save();
        // return response()->json($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Insurance_certificate  $insurance_certificate
     * @return \Illuminate\Http\Response
     */
    public function destroy(Insurance_certificate $insurance_certificate)
    {
        //
    }

    public function printInsurCertificate($idInsu)
    {
        $output =  Insurance_certificate::find($idInsu);
        $car = Car::find($output->car_id);
        $model = Car_model::find($car->car_models_id);

        $output->car = $car;
        $output->model = $model;
        return response()->json($output);
    }



    public function checkInsurCertificate($idWork, $jobType)
    {
        $checkInsurCertificate = Insurance_certificate::where('working_id', $idWork)->first();

        if (empty($checkInsurCertificate)) {
            $query = DB::table('workings')
                ->join('cars', 'workings.car_id', '=', 'cars.id')
                ->join('users', 'workings.sale_id', '=', 'users.id')
                ->join('car_models', 'cars.car_models_id', '=', 'car_models.id')
                ->join('customers', 'workings.customer_id', '=', 'customers.id')
                ->join('customer_details', 'customers.id', '=', 'customer_details.customer_id')
                ->where('workings.id', $idWork)
                ->first();
            $query->repair_details = DB::table('job_technicians')
                ->where('job_technicians.car_id', $query->car_id)
                ->where('job_technicians.job_type', $jobType)
                ->get();
            $query->working_id = $idWork;
            $query->action = "add";
        } else {
            $query = DB::table('insurance_certificates')
                ->join('cars', 'insurance_certificates.car_id', '=', 'cars.id')
                ->join('car_models', 'cars.car_models_id', '=', 'car_models.id')
                ->where('working_id', $idWork)
                ->first();
            $query->repair_details = DB::table('job_technicians')
                ->where('job_technicians.car_id', $query->car_id)
                ->where('job_technicians.job_type', $jobType)
                ->get();
            $query->id = $checkInsurCertificate->id;
            $query->action = "edit";
        }
        return response()->json($query);
    }
}
