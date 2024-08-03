<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Car;
use App\Models\Financial;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class IncomeController extends Controller
{
    protected $pathCurrent = 'incomes/';

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
        $formData =  $request->incomes;
        for ($i = 0; $i < count($formData); $i++) {
            $name_file = $formData[$i]['img_name'];
            unset($formData[$i]['img']);
            unset($formData[$i]['img_name']);
            unset($formData[$i]['menuDate']);
            unset($formData[$i]['search_shop']);
            unset($formData[$i]['search']);
            if ($formData[$i]['type'] == 2) {
                $formData[$i]['car_id'] == null;
            }
            $newCreate = Income::create($formData[$i]);


            $car = Car::find($newCreate->car_id);
            $sum_net_price = (float)$car->income + (float) $newCreate->money;
            $car->income = $sum_net_price;
            $car->save();


            if (!empty($name_file)) {
                if (File::makeDirectory($this->path . $this->pathCurrent . $newCreate->id, 0775, false, true)) {
                    if (File::exists($this->temp . $name_file)) {
                        File::move($this->temp . $name_file, $this->path . $this->pathCurrent . $newCreate->id . '/' . $newCreate->id . '.png');
                        $newCreate->file = $newCreate->id . '.png';
                        $newCreate->save();
                    }
                }
            }
        }
        return response()->json($newCreate);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Income  $income
     * @return \Illuminate\Http\Response
     */
    public function show(Income $income)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Income  $income
     * @return \Illuminate\Http\Response
     */
    public function edit(Income $income)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Income  $income
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Income $income)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Income  $income
     * @return \Illuminate\Http\Response
     */
    public function destroy(Income $income)
    {
        // $income->active = 0;
        // $income->save();
        // return response()->json(1);
    }

    public function delete_income($id, Request $request)
    {
        $Income = Income::find($id);
        if (
            $Income->detail == 'เงินจอง'
            or $Income->detail == 'เงินดาวน์'
            or $Income->detail == 'ซื้อเงินสด'
            or $Income->detail == 'ค่างวดล่วงหน้า'
            or $Income->detail == 'สมาร์ทชัว'
            or $Income->detail == 'ประกันอื่นๆ'
            or $Income->detail == 'ใบสำคัญรับเงิน (อื่นๆ)'
            or $Income->detail == 'ใบสำคัญรับเงิน'
            or $Income->detail == 'เงินจอง/เงินดาวน์/ซื้อเงินสด'
        ) {
            Financial::find($Income->no)->delete();
        }
        $Income->delete();

        return response()->json();
    }


    public  function income_where(Request $request)
    {

        // $branch_id = $request->input('branch_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $output = Income::with('car', 'branch', 'user')
            ->where([['active', 1], ['date', '>=', $timeStart], ['date', '<=', $timeEnd]])
            // ->when($branch_id, function ($query) use ($branch_id) {
            //     return $query->where('branch_id', $branch_id);
            // })
            ->get();

        $map = $output->map(function ($items) {
            if ($items['file'] != null) {
                $items['file'] = '' . '/' . $this->path . $this->pathCurrent  . $items['id'] . '/' . $items['file'];
            }
            return $items;
        });

        return response()->json($map);
    }



    public  function uploadFile_income(Request $request)
    {
        if ($request->hasFile('Image')) {
            $img_name = time();
            $file = $request->file('Image');
            $filename = $img_name  . '.png';
            $resize = Image::make($file)->resize(800, 600);
            $resize->save($this->temp . $filename, 100);
            return response()->json($filename);
        }
    }

    public  function delete_uploadFile_income(Request $request)
    {
        if (!empty($request->name_file)) {
            File::delete($this->temp . $request->name_file);
        }
        // return response()->json($request);
    }

    public  function cancle_uploadFile_income(Request $request)
    {
        $formData =  $request->incomes;
        for ($i = 0; $i < count($formData); $i++) {
            if (!is_null($formData[$i]['img_name'])) {
                File::delete($this->temp . $formData[$i]['img_name']);
            }
        }
    }

    public  function report_income(Request $request)
    {

        $branch_id = $request->input('branch_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $output = Income::with('car', 'branch', 'user')
            ->where([['active', 1], ['status_check', 1], ['date', '>=', $timeStart], ['date', '<=', $timeEnd]])
            ->where([['date', '>=', $timeStart], ['date', '<=', $timeEnd]])

            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->where('branch_id', $branch_id);
            })
            ->get();

        $map = $output->map(function ($items) {
            if ($items['file'] != null) {
                $items['file'] = '' . '/' . $this->path . $this->pathCurrent  . $items['id'] . '/' . $items['file'];
            }
            return $items;
        });

        return response()->json($map);
    }

    public  function income_car(Request $request)
    {

        $output = Income::with('car', 'branch', 'user', 'working')
            ->where([['active', 1]])
            ->where([['car_id', $request->car_id]])
            // ->whereHas('working', function ($query) use ($request) {
            //     $query->where('car_id', $request->car_id);
            // })
            ->orderBy('date', 'DESC')
            ->get();

        $map = $output->map(function ($items) {
            if ($items['file'] != null) {
                $items['file'] = '' . '/' . $this->path . $this->pathCurrent  . $items['id'] . '/' . $items['file'];
            }
            return $items;
        });

        return response()->json($map);
    }



    public function comfirm_income($id)
    {
        $Income = Income::find($id);
        $Income->status_check = 1;
        $Income->save();
    }
}
