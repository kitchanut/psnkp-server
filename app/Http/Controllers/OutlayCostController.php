<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Car;
use App\Models\Outlay_cost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class OutlayCostController extends Controller
{
    protected $pathCurrent = 'outlay_cost/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
        $formData =  $request->outlay_costs;
        for ($i = 0; $i < count($formData); $i++) {
            $name_file = $formData[$i]['img_name'];
            unset($formData[$i]['img']);
            unset($formData[$i]['img_name']);
            unset($formData[$i]['menuDate']);
            unset($formData[$i]['search_shop']);
            unset($formData[$i]['search']);
            $newCreate = Outlay_cost::create($formData[$i]);

            if ($formData[$i]['type'] == 1) {
                $car = Car::find($newCreate->car_id);
                $car->expenses = Outlay_cost::where([['car_id', $newCreate->car_id], ['detail', 'ค่าตัวรถ'], ['active', 1]])->sum('money');
                $car->net_price = Outlay_cost::where([['car_id', $newCreate->car_id], ['active', 1]])->sum('money');
                $car->save();
            } else if ($formData[$i]['type'] == 2) {
                $formData[$i]['car_id'] == null;
            }

            // if ($formData[$i]['broken'] == 2) {
            //     $Branch = Branch::find($newCreate->branch_id);
            //     $Branch->branch_money = $Branch->branch_money - $newCreate->money;
            //     $Branch->save();
            // }

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
        // return response()->json($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Outlay_cost  $outlay_cost
     * @return \Illuminate\Http\Response
     */
    public function show(Outlay_cost $outlay_cost)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Outlay_cost  $outlay_cost
     * @return \Illuminate\Http\Response
     */
    public function edit(Outlay_cost $outlay_cost)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Outlay_cost  $outlay_cost
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Outlay_cost $outlay_cost)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Outlay_cost  $outlay_cost
     * @return \Illuminate\Http\Response
     */
    public function destroy(Outlay_cost $outlay_cost)
    {
        // $outlay_cost->active = 0;
        // $outlay_cost->save();
        // return response()->json(1);
    }

    public function delete_outlay($id, Request $request)
    {
        $Outlay_cost = Outlay_cost::find($id);
        $car = Car::find($Outlay_cost->car_id);
        if ($car) {
            $sum_net_price = (float)$car->net_price - (float) $Outlay_cost->money;
            $sum_expenses = (float)$car->expenses - (float) $Outlay_cost->money;

            $car->expenses = $sum_expenses;
            $car->net_price = $sum_net_price;
            $car->save();
        }
        $Outlay_cost->delete();
    }


    public  function outlaycost_where(Request $request)
    {

        $branch_id = $request->input('branch_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $output = Outlay_cost::with('car', 'branch', 'user')
            ->where([['active', 1], ['date', '>=', $timeStart], ['date', '<=', $timeEnd]])
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



    public  function uploadFile_outlay(Request $request)
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

    public  function delete_uploadFile_outlay(Request $request)
    {
        if (!empty($request->name_file)) {
            File::delete($this->temp . $request->name_file);
        }
        // return response()->json($request);
    }

    public  function cancle_uploadFile_outlay(Request $request)
    {
        $formData =  $request->outlay_costs;
        for ($i = 0; $i < count($formData); $i++) {
            if (!is_null($formData[$i]['img_name'])) {
                File::delete($this->temp . $formData[$i]['img_name']);
            }
        }
    }

    public  function report_withdraw_money(Request $request)
    {

        $branch_id = $request->input('branch_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');

        $output = Outlay_cost::with('car', 'branch', 'user')
            ->where([['active', 1], ['date', '>=', $timeStart], ['date', '<=', $timeEnd]])
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

    public  function outlaycost_car(Request $request)
    {

        $output = Outlay_cost::with('car', 'branch', 'user')
            ->where([['car_id', $request->car_id], ['active', 1]])
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



    public function comfirm_outlay($id)
    {
        $Outlay_cost = Outlay_cost::find($id);
        $Outlay_cost->status_check = 1;
        $Outlay_cost->save();
    }

    public function outlaycost_getwithTime(Request $request)
    {
        $car_id = $request->input('car_id');
        $timeStart = $request->input('timeStart');
        $timeEnd = $request->input('timeEnd');
        $search = $request->input('search');

        if ($car_id != 'null') {
            if ($search == '') {
                $output = Outlay_cost::with('car', 'branch', 'user')
                    ->where([['active', 1], ['car_id', $car_id]])
                    ->orderBy('date', 'DESC')
                    ->get();
            } else {
                $output = Outlay_cost::with('car', 'branch', 'user')
                    ->where([['active', 1], ['car_id', $car_id], ['detail', 'LIKE', '%' . $search . '%']])
                    ->orderBy('date', 'DESC')
                    ->get();
            }
        } elseif ($search != '') {
            $output = Outlay_cost::with('car', 'branch', 'user')
                ->where([['active', 1], ['detail', 'LIKE', '%' . $search . '%']])
                ->orderBy('date', 'DESC')
                ->limit(1000)
                ->get();
        } else {
            $output = Outlay_cost::with('car', 'branch', 'user')
                ->where([['active', 1], ['date', '>=', $timeStart], ['date', '<=', $timeEnd]])
                ->orderBy('date', 'DESC')
                ->get();
        }


        return response()->json($output);
    }
}
