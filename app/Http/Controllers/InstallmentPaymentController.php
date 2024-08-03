<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\InstallmentPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class InstallmentPaymentController extends Controller
{
    protected $pathCurrent = 'installment/';

    public function index()
    {
        $output = InstallmentPayment::with('user')->get();
        return response()->json($output);
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

        $credentials['installment_id'] = $request->installment_id;
        $credentials['installment_no'] = $request->installment_no;
        $credentials['installment_pay_date'] = $request->installment_pay_date;
        $credentials['installment_amount'] = $request->installment_amount;
        $credentials['user_id'] = auth()->user()->id;
        $created =  InstallmentPayment::create($credentials);

        $file_installment_img = $requestFormData->file('installment_img');
        if ($file_installment_img) {
            $filename_installment_img = $created->id . '_installment_img.' . $file_installment_img->getClientOriginalExtension();
            $saveImagePath_installment_img = $this->path . $this->pathCurrent;
            $file_installment_img->move($saveImagePath_installment_img, $filename_installment_img);
            $created->installment_img = $filename_installment_img;
        }
        $created->save();

        $this->sumInstallment($request->installment_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InstallmentPayment  $installmentPayment
     * @return \Illuminate\Http\Response
     */
    public function show(InstallmentPayment $installmentPayment)
    {
        return response()->json($installmentPayment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InstallmentPayment  $installmentPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $requestFormData, InstallmentPayment $installmentPayment)
    {
        // $credentials = $request->all();
        // $credentials['user_id'] = auth()->user()->id;

        $dataInput = $requestFormData->all();
        $request =  json_decode($dataInput['formData']);

        $installmentPayment->installment_pay_date = $request->installment_pay_date;
        $installmentPayment->installment_amount = $request->installment_amount;
        $installmentPayment->user_id = auth()->user()->id;

        $file_installment_img = $requestFormData->file('installment_img');
        if ($file_installment_img) {
            $filename_installment_img = $installmentPayment->id . '_installment_img.' . $file_installment_img->getClientOriginalExtension();
            $saveImagePath_installment_img = $this->path . $this->pathCurrent;
            $file_installment_img->move($saveImagePath_installment_img, $filename_installment_img);
            $installmentPayment->installment_img = $filename_installment_img;
        }
        $installmentPayment->save();

        $this->sumInstallment($request->installment_id);

        // $installmentPayment->update($credentials);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InstallmentPayment  $installmentPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(InstallmentPayment $installmentPayment)
    {
        $destinationPath = $this->path . $this->pathCurrent;
        File::delete($destinationPath . $installmentPayment->installment_img);
        $installmentPayment->delete();
        $this->sumInstallment($installmentPayment->installment_id);
    }

    public function getByInstallmentID($installment_id)
    {
        $installmentPayment = InstallmentPayment::with('user')
            ->where('installment_id', $installment_id)
            ->orderBy('installment_no')
            ->get();
        $installmentPayment->map(function ($item) {
            if ($item->installment_img) {
                if ($item['installment_img'] != null) {
                    $item['installment_img'] = '' . '/' . $this->path . 'installment/'  . $item['installment_img'];
                }
            }
        });
        return response()->json($installmentPayment);
    }

    public function sumInstallment($installment_id)
    {
        $installment_pay_1 = 0;
        $installment_pay_2 = 0;
        $installment_pay_3 = 0;
        $installment_pay_4 = 0;
        $installment_pay_5 = 0;
        $installment_pay_6 = 0;
        $installmentPayments = InstallmentPayment::where('installment_id', $installment_id)->get();
        foreach ($installmentPayments as $key => $value) {
            $installment_pay_1 =  $value->installment_no == 1 ? $installment_pay_1 +  $value->installment_amount : $installment_pay_1;
            $installment_pay_2 =  $value->installment_no == 2 ? $installment_pay_2 +  $value->installment_amount : $installment_pay_2;
            $installment_pay_3 =  $value->installment_no == 3 ? $installment_pay_3 +  $value->installment_amount : $installment_pay_3;
            $installment_pay_4 =  $value->installment_no == 4 ? $installment_pay_4 +  $value->installment_amount : $installment_pay_4;
            $installment_pay_5 =  $value->installment_no == 5 ? $installment_pay_5 +  $value->installment_amount : $installment_pay_5;
            $installment_pay_6 =  $value->installment_no == 6 ? $installment_pay_6 +  $value->installment_amount : $installment_pay_6;
        }

        $installment = Installment::find($installment_id);
        $installment->installment_pay_1 = $installment_pay_1;
        $installment->installment_pay_2 = $installment_pay_2;
        $installment->installment_pay_3 = $installment_pay_3;
        $installment->installment_pay_4 = $installment_pay_4;
        $installment->installment_pay_5 = $installment_pay_5;
        $installment->installment_pay_6 = $installment_pay_6;
        $installment->save();
    }
}
