<?php

namespace App\Http\Controllers;

use App\Models\Car_model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class CarModelController extends Controller
{
    protected $pathCurrent = 'car_models/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Car_model::all());

        $output = Car_model::where([['del', 1]])->get();

        $map = $output->map(function ($items) {
            if ($items['car_model_image'] == null) {
                $items['car_model_image'] = '' . '/' . $this->path . $this->pathCurrent  . 'default/car_model.png';
            } else {
                $items['car_model_image'] = '' . '/' . $this->path . $this->pathCurrent . $items['id'] . '/' . $items['car_model_image'];
            }
            return $items;
        });
        return response()->json($map);
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
        // $credentials = $request->except(['id']);
        // Car_model::create($credentials);

        $credentials = (array) json_decode($request->formData);
        $createCar_model = Car_model::create($credentials);
        if ($request->hasFile('Image')) {
            if (File::makeDirectory($this->path . $this->pathCurrent . $createCar_model->id, 0775, true)) {
                $file = $request->file('Image');

                $filename = $createCar_model->id . '.png';
                $saveImagePath = $this->path . $this->pathCurrent . $createCar_model->id;
                // $width = Image::make($file)->width();
                // $height = Image::make($file)->height();
                $resize = Image::make($file)->resize(800, 600);
                //$resize->save();
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $update = Car_model::find($createCar_model->id);
                $update->car_model_image = $filename;
                $update->save();
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Car_model  $car_model
     * @return \Illuminate\Http\Response
     */
    public function show(Car_model $car_model)
    {
        return response()->json($car_model);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Car_model  $car_model
     * @return \Illuminate\Http\Response
     */
    public function edit(Car_model $car_model)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Car_model  $car_model
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Car_model $car_model)
    {
        $credentials = (array) json_decode($request->formData);
        $car_model->update($credentials);
        if ($request->hasFile('Image')) {
            if (!File::exists($this->path . $this->pathCurrent . $credentials['id'])) {
                if (File::makeDirectory($this->path . $this->pathCurrent . $credentials['id'], 0775, true)) {
                    $file = $request->file('Image');

                    $filename = $credentials['id'] . '.png';
                    $saveImagePath = $this->path . $this->pathCurrent . $credentials['id'];
                    $resize = Image::make($file)->resize(800, 600, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    //$resize->save();
                    $resize->save($this->temp . $filename, 100);
                    $file->move($saveImagePath, $filename);
                    File::delete($this->temp . $filename);
                    $update = car_model::find($credentials['id']);
                    $update->car_model_image = $filename;
                    $update->save();
                }
            } else {
                $file = $request->file('Image');

                $filename = $credentials['id'] . '.png';
                $saveImagePath = $this->path . $this->pathCurrent . $credentials['id'];
                // $width = Image::make($file)->width();
                // $height = Image::make($file)->height();
                $resize = Image::make($file)->resize(800, 600);
                // $resize->save();
                // $resize->save($this->temp . $filename, 100);
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $update = car_model::find($credentials['id']);
                $update->car_model_image = $filename;
                $update->save();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Car_model  $car_model
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car_model $car_model, Request $request)
    {
        // $car_model->delete();
        $car_model->car_model_active = 0;
        $car_model->del = 0;
        $car_model->user_id = $request->user()->id;
        $car_model->save();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function SelectOnCarModel()
    {
        // return response()->json(Car_model::where('car_model_active', 1)->get());
        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            $allow_query = Car_model::where('car_model_active', 1)->get();
            return response()->json($allow_query);
        } else {
            if ($host == $allow_host) {
                $allow_query = Car_model::where('car_model_active', 1)->get();
                return response()->json($allow_query);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }

    public function getImageModel()
    {

        $output = Car_model::where('car_model_active', 1)->get();

        $map = $output->map(function ($items) {
            if ($items['car_model_image'] == null) {
                $items['car_model_image'] = '' . '/' . $this->path . $this->pathCurrent  . 'default/car_model.png';
            } else {
                $items['car_model_image'] = '' . '/' . $this->path . $this->pathCurrent . $items['id'] . '/' . $items['car_model_image'];
            }
            return $items;
        });
        // return response()->json($map);

        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            return response()->json($map);
        } else {
            if ($host == $allow_host) {
                return response()->json($map);
            } else {
                return response()->json($notaloow_query);
            }
        }
    }
}
