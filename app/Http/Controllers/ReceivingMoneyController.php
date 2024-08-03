<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Income;
use App\Models\Receiving_money;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ReceivingMoneyController extends Controller
{

    protected $pathCurrent = 'end_works/';

    public function index()
    {
        $receive = Receiving_money::orderBy('book_no', 'desc')
            ->orderBy('number_no', 'desc')
            ->limit(1000)
            ->get();
        return response()->json($receive);
    }

    public function getWithWorkID($working_id)
    {
        $receive = Receiving_money::orderBy('book_no', 'desc')
            ->when($working_id, function ($query) use ($working_id) {
                return $query->where('working_id', $working_id);
            })
            ->orderBy('number_no', 'desc')
            ->limit(1000)
            ->get();
        return response()->json($receive);
    }

    public function getDataWithCarNo($car_no)
    {
        $receive = Receiving_money::when($car_no, function ($query) use ($car_no) {
            return $query->where('car_no', 'LIKE', $car_no . '%');
        })
            ->orderBy('book_no', 'desc')
            ->orderBy('number_no', 'desc')
            ->limit(1000)
            ->get();
        return response()->json($receive);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        $credentials = (array) json_decode($request->formData);
        $createReceiving_money = Receiving_money::create([
            'working_id' => $credentials['working_id'],
            'company_name' => $credentials['company_name'],
            'company_address' => $credentials['company_address'],
            'company_tel' => $credentials['company_tel'],
            'car_no_body' => $credentials['car_no_body'],
            'company_idvat' => $credentials['company_idvat'],
            'company_fax' => $credentials['company_fax'],
            'book_no' => $credentials['book_no'],
            'number_no' => $credentials['number_no'],
            'receiving_money_vat' => $credentials['receiving_money_vat'],
            'bank_name' => $credentials['bank_name'],
            'bank_idvat' => $credentials['bank_idvat'],
            'bank_address' => $credentials['bank_address'],
            'receiving_money_date' => $credentials['receiving_money_date'],
            'car_id' => $credentials['car_id'],
            'car_no' => $credentials['car_no'],
            'car_model_name' => $credentials['car_model_name'],
            'car_regis' => $credentials['car_regis'],
            'name_th' => $credentials['name_th'],
            'car_no_engine' => $credentials['car_no_engine'],
            'car_no_body' => $credentials['car_no_body'],
            'car_price' => $credentials['car_price'],
            'receiving_money_sum' => $credentials['receiving_money_sum'],
            'receiving_money_sum_str' => $credentials['receiving_money_sum_str'],
            'receiving_money_sum_vat' => $credentials['receiving_money_sum_vat'],
            'receiving_money_sum_vat_str' => $credentials['receiving_money_sum_vat_str'],
            'receiving_money_all' => $credentials['receiving_money_all'],
            'receiving_money_all_str' => $credentials['receiving_money_all_str'],
            'name_user' => $credentials['name_user'],
            'name_authority' => $credentials['name_authority'],
            'sale_id' => $credentials['sale_id'],
            'branch_id' => $credentials['branch_id'],
            'user_id' => $credentials['user_id'],
            'receivingMoney_type' => $credentials['receivingMoney_type'],
            'receivingMoney_type_str' => $credentials['receivingMoney_type_str'],
            'customer_name' => $credentials['customer_name'],
            'receiving_money_status' => $credentials['receiving_money_status'],
        ]);

        $updateStatus = Working::find($credentials['working_id']);
        $updateStatus->user_id = $request->user()->id;
        $updateStatus->save();

        if ($request->hasFile('Image')) {
            if (!File::exists($this->path . $this->pathCurrent . $createReceiving_money->id)) {
                if (File::makeDirectory($this->path . $this->pathCurrent . $createReceiving_money->id, 0775, true)) {
                    $file = $request->file('Image');

                    $filename = $createReceiving_money->id . '.' . $file->getClientOriginalExtension();
                    $saveImagePath = $this->path . $this->pathCurrent . $createReceiving_money->id;
                    $file->move($saveImagePath, $filename);

                    $update = Receiving_money::find($createReceiving_money->id);
                    $update->receiving_money_file = $filename;
                    $update->save();
                }
            }
        }
    }

    public function show(Receiving_money $receiving_money)
    {
        return response()->json($receiving_money);
    }

    public function edit(Receiving_money $receiving_money)
    {
        //
    }

    public function update(Request $request, Receiving_money $receiving_money)
    {
        $credentials = (array) json_decode($request->formData);
        unset($credentials['updated_at']);
        unset($credentials['action']);
        $receiving_money->update($credentials);

        $credentials2 = (array) json_decode($request->formData);
        unset($credentials2['updated_at']);
        unset($credentials2['action']);
        unset($credentials2['id']);

        $queryWorking = Working::find($receiving_money->working_id);
        $queryWorking->user_id = $request->user()->id;
        $queryWorking->save();

        if ($request->hasFile('Image')) {
            if (!File::exists($this->path . $this->pathCurrent . $credentials['id'])) {
                if (File::makeDirectory($this->path . $this->pathCurrent . $credentials['id'], 0775, true)) {
                    $file = $request->file('Image');

                    $filename = $credentials['id'] . '.' . $file->getClientOriginalExtension();
                    $saveImagePath = $this->path . $this->pathCurrent . $credentials['id'];
                    $file->move($saveImagePath, $filename);

                    $update = Receiving_money::find($credentials['id']);
                    $update->receiving_money_file = $filename;
                    $update->save();
                }
            } else {
                $file = $request->file('Image');

                $filename = $credentials['id'] . '.' . $file->getClientOriginalExtension();
                $saveImagePath = $this->path . $this->pathCurrent . $credentials['id'];
                $file->move($saveImagePath, $filename);

                $update = Receiving_money::find($credentials['id']);
                $update->receiving_money_file = $filename;
                $update->save();
            }
        }
        $Car = Car::find($queryWorking->car_id);
        $Car->income = Income::where([['car_id', $Car->id], ['active', 1], ['status_check', 1]])->sum('money');
        $Car->save();
    }

    public function destroy(Receiving_money $receiving_money)
    {
        $receiving_money->delete();
    }

    public function check_receiving_money($idWork, $car_no, $receivingMoney_type)
    {
        $check_receiving_money = Receiving_money::where([['working_id', $idWork], ['car_no', $car_no], ['receivingMoney_type', $receivingMoney_type]])->first();

        if (empty($check_receiving_money)) {
            $query = DB::table('workings')
                ->leftJoin('banks', 'workings.bank_id', '=', 'banks.id')
                ->leftJoin('bank_branches', function ($join) {
                    $join->on('workings.bank_branch_id', '=', 'bank_branches.id');
                })
                ->join('cars', 'workings.car_id', '=', 'cars.id')
                ->join('car_models', 'cars.car_models_id', '=', 'car_models.id')
                ->join('provinces', 'cars.province_id', '=', 'provinces.id')
                ->join('users', 'workings.sale_id', '=', 'users.id')
                ->where('workings.id', $idWork)
                ->first();
            unset($query->password);
            $customer = Customer::select('customer_name')->find($query->customer_id);
            $query->name_user = '';
            $query->working_id = $idWork;
            $query->receivingMoney_type = $receivingMoney_type;
            if ($receivingMoney_type == 1) {
                $query->receivingMoney_type_str = 'ค่าตัวรถ';
            } else if ($receivingMoney_type == 2) {
                $query->receivingMoney_type_str = 'ค่าคอม';
            } else {
                $query->receivingMoney_type_str = 'อื่นๆ';
            }
            $query->car_price = $receivingMoney_type == 1 ? $query->car_price : '';
            $query->customer_name = $customer->customer_name;
            $query->receiving_money_date = date('Y-m-d');
            $query->action = "add";
        } else {
            $query = DB::table('receiving_moneys')
                ->where([['working_id', $idWork], ['car_no', $car_no], ['receivingMoney_type', $receivingMoney_type]])
                ->first();
            if (empty($query->receivingMoney_type_str)) {
                if ($receivingMoney_type == 1) {
                    $query->receivingMoney_type_str = 'ค่าตัวรถ';
                } else if ($receivingMoney_type == 2) {
                    $query->receivingMoney_type_str = 'ค่าคอม';
                } else {
                    $query->receivingMoney_type_str = 'อื่นๆ';
                }
            }
            if (empty($query->customer_name)) {
                $working = Working::select('customer_id')->where('id', $idWork)->first();
                $customer = Customer::select('customer_name')->find($working->customer_id);
                $query->customer_name = $customer->customer_name;
            }
            $query->action = "edit";
        }
        return response()->json($query);
    }


    public function commission(Request $request)
    {
        $query = DB::table('receiving_moneys')
            ->where([['receiving_moneys.sale_id', $request->user_id], ['receiving_moneys.receiving_money_status', 1], ['receiving_moneys.created_at', '>=', $request->timeStart], ['receiving_moneys.created_at', '<=', $request->timeEnd]])
            ->get();
        $user = DB::table('users')->select('first_name', 'last_name', 'tel', 'bank', 'bank_no')->where('id', $request->user_id)->first();
        return response()->json([
            'querys' => $query,
            'user' => $user,
        ]);
    }

    public function printReceivingMoney($id)
    {
        $output =  Receiving_money::with('car.car_series')->where('id', $id)->first();
        return response()->json($output);
    }
}
