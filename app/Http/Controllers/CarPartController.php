<?php

namespace App\Http\Controllers;

use App\Models\Car_part;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class CarPartController extends Controller
{
    protected $pathCurrent = 'car_parts/';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = Car_part::with(['car_part_types', 'unit'])->where([['del', 1]])->get();

        $map = $output->map(function ($items) {
            if ($items['car_part_image'] == null) {
                $items['car_part_image'] = '' . '/' . $this->path . $this->pathCurrent  . 'default/car_part.png';
            } else {
                $items['car_part_image'] = '' . '/' . $this->path . $this->pathCurrent . $items['id'] . '/' . $items['car_part_image'];
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
        $credentials = (array) json_decode($request->formData);
        $createCar_part = Car_part::create($credentials);
        if ($request->hasFile('Image')) {
            if (File::makeDirectory($this->path . $this->pathCurrent . $createCar_part->id, 0775, true)) {
                $file = $request->file('Image');

                $filename = $createCar_part->id . '.png';
                $saveImagePath = $this->path . $this->pathCurrent . $createCar_part->id;
                // $width = Image::make($file)->width();
                // $height = Image::make($file)->height();
                $resize = Image::make($file)->resize(800, 600);
                //$resize->save();
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $update = Car_part::find($createCar_part->id);
                $update->car_part_image = $filename;
                $update->save();
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Car_part  $car_part
     * @return \Illuminate\Http\Response
     */
    public function show(Car_part $car_part)
    {
        // if ($car_part->car_part_image == null) {
        //     // $car_part->car_part_image = '' . '/' . $this->path . $this->pathCurrent  . 'default/car_part.png';
        // } else {
        //     $car_part->car_part_image = '' . '/' . $this->path . $this->pathCurrent . $car_part->id . '/' . $car_part->car_part_image;
        // }
        return response()->json($car_part);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Car_part  $car_part
     * @return \Illuminate\Http\Response
     */
    public function edit(Car_part $car_part)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Car_part  $car_part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Car_part $car_part)
    {
        $credentials = (array) json_decode($request->formData);
        $car_part->update($credentials);
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
                    $update = Car_part::find($credentials['id']);
                    $update->car_part_image = $filename;
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
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $update = Car_part::find($credentials['id']);
                $update->car_part_image = $filename;
                $update->save();
            }
        }
        // return response()->json(Car_part::where('car_part_active', 1)->get());


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Car_part  $car_part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car_part $car_part, Request $request)
    {
        // $car_part->delete();

        $car_part->car_part_active = 0;
        $car_part->del = 0;
        $car_part->user_id = $request->user()->id;
        $car_part->save();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function SelectOnCarParts()
    {
        return response()->json(Car_part::where('car_part_active', 1)->get());
    }
}
