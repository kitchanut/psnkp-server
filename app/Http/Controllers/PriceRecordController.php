<?php

namespace App\Http\Controllers;

use App\Models\PriceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class PriceRecordController extends Controller
{
    protected $pathCurrent = 'price_record/';

    public function index(Request $request)
    {
        $timeStart = $request->query('timeStart');
        $timeEnd = $request->query('timeEnd');
        $car_models_id = $request->query('car_models_id');
        $car_serie_id = $request->query('car_serie_id');
        $car_serie_sub_id = $request->query('car_serie_sub_id') ? array_map('intval', explode(",", $request->query('car_serie_sub_id'))) : null;
        $car_gear = $request->query('car_gear');
        $car_year_start = (int)$request->query('car_year_start');
        $car_year_end = (int)$request->query('car_year_end');

        $output =  PriceRecord::with(['car_models', 'car_series', 'car_serie_sub', 'color', 'user'])
            ->when($timeStart, function ($q) use ($timeStart, $timeEnd) {
                return $q->whereBetween('date', [$timeStart, $timeEnd]);
            })
            ->when($car_models_id, function ($q) use ($car_models_id) {
                return $q->where('car_models_id', $car_models_id);
            })
            ->when($car_serie_id, function ($q) use ($car_serie_id) {
                return $q->where('car_serie_id', $car_serie_id);
            })
            ->when($car_serie_sub_id, function ($q) use ($car_serie_sub_id) {
                return $q->whereIn('car_serie_sub_id', $car_serie_sub_id);
            })
            ->when($car_gear, function ($q) use ($car_gear) {
                return $q->where('car_gear', $car_gear);
            })
            ->when($car_year_start, function ($q) use ($car_year_start, $car_year_end) {
                return $q->whereBetween('car_year', [$car_year_start, $car_year_end]);
            })
            ->limit(1000)
            ->orderBy('car_year', 'asc')
            ->get();
        $map = $output->map(function ($items) {
            if ($items['image'] == null) {
                $items['image'] = '' . '/' . $this->path . 'car_models/'  . 'default/car_model.png';
            } else {
                $items['image'] = '' . '/' . $this->path . $this->pathCurrent . '/' . $items['image'];
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
        $credentials = (array) json_decode($request->formData);
        $credentials['user_id'] = auth()->user()->id;
        $created = PriceRecord::create($credentials);
        if ($request->hasFile('Image')) {
            $file = $request->file('Image');
            $filename = $created->id . '.png';
            $saveImagePath = $this->path . $this->pathCurrent;
            $file->move($saveImagePath, $filename);
            $update = PriceRecord::find($created->id);
            $update->image = $filename;
            $update->save();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PriceRecord  $priceRecord
     * @return \Illuminate\Http\Response
     */
    public function show(PriceRecord $priceRecord)
    {
        if ($priceRecord['image'] == null) {
            $priceRecord['image'] = '' . '/' . $this->path . 'car_models/'  . 'default/car_model.png';
        } else {
            $priceRecord['image'] = '' . '/' . $this->path . $this->pathCurrent  . $priceRecord['image'];
        }

        return response()->json($priceRecord);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PriceRecord  $priceRecord
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PriceRecord $priceRecord)
    {
        $credentials = (array) json_decode($request->formData);
        unset($credentials['image']);

        if ($request->hasFile('Image')) {
            $file = $request->file('Image');
            $filename = $priceRecord->id . '.png';
            $saveImagePath = $this->path . $this->pathCurrent;
            $file->move($saveImagePath, $filename);
            $update = PriceRecord::find($priceRecord->id);
            $update->image = $filename;
            $update->save();
        }

        $priceRecord->update($credentials);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PriceRecord  $priceRecord
     * @return \Illuminate\Http\Response
     */
    public function destroy(PriceRecord $priceRecord)
    {
        $filename = $priceRecord->id . '.png';
        $saveImagePath = $this->path . $this->pathCurrent;
        File::delete($saveImagePath . $filename);
        $priceRecord->delete();
    }
}
