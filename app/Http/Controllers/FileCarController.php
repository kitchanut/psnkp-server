<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\File_car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class FileCarController extends Controller
{
    protected $pathCurrent = 'cars/';


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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\File_car  $file_car
     * @return \Illuminate\Http\Response
     */
    public function show(File_car $file_car)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\File_car  $file_car
     * @return \Illuminate\Http\Response
     */
    public function edit(File_car $file_car)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\File_car  $file_car
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File_car $file_car)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File_car  $file_car
     * @return \Illuminate\Http\Response
     */
    public function destroy(File_car $file_car)
    {
        //
    }



    public function file_car(Request $request)
    {
        $car = Car::where('id', $request->car_id)->select('car_date_book', 'tex_date')->first();
        $car_file = File_car::with('user')->where('car_id', $request->car_id)->get();

        $map = $car_file->map(function ($items) {
            $items['file_name'] = ''  . $items['car_id'] . '/' . $items['file_name'];
            return $items;
        });

        return response()->json([
            'car' => $car,
            'car_file' => $map
        ]);
    }

    public function upload_file_car(Request $request)
    {
        $File_car = File_car::create([
            'car_id' => $request->car_id,
            'name' => $request->name,
            // 'file_name' => $request->file_name,
            'user_id' => $request->user_id,
        ]);

        $file = $request->file('file');
        $filename = 'file_' . $File_car->id . '.' . $file->getClientOriginalExtension();
        $saveImagePath = $this->path . $this->pathCurrent . $request->car_id;
        if ($file->getClientOriginalExtension() != 'pdf') {
            $resize = Image::make($file)->resize(800, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            //$resize->save();
            $resize->save($this->temp . $filename, 100);
            $file->move($saveImagePath, $filename);
            File::delete($this->temp . $filename);
        } else {
            $file->move($saveImagePath, $filename);
        }

        $update = File_car::find($File_car->id);
        $update->file_name = $filename;
        $update->save();

        $car_file = File_car::with('user')->where('car_id', $request->car_id)->get();
        $map = $car_file->map(function ($items) {
            $items['file_name'] = ''  . $items['car_id'] . '/' . $items['file_name'];
            return $items;
        });
        // return response()->json($car_file);
        return response()->json($map);
    }

    public function delete_file_car(Request $request)
    {
        $File_car =  File_car::find($request->id);
        if (File::exists($this->path . $this->pathCurrent . $File_car->car_id . '/' . $File_car->file_name)) {
            File::delete($this->path . $this->pathCurrent . $File_car->car_id . '/' . $File_car->file_name);
        }
        $File_car->delete();
        $car_file = File_car::with('user')->where('car_id', $request->car_id)->get();
        $map = $car_file->map(function ($items) {
            $items['file_name'] = ''  . $items['car_id'] . '/' . $items['file_name'];
            return $items;
        });
        return response()->json($map);
    }
    public function change_date_file_car(Request $request)
    {
        $credentials = (array) json_decode($request->formData);

        $car = Car::find($request->car_id);
        $car->car_date_book = $credentials['car_date_book'];
        $car->tex_date = $credentials['tex_date'];
        $car->save();
    }
}
