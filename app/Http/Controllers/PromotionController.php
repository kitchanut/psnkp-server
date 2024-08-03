<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class PromotionController extends Controller
{
    protected $pathCurrent = 'promotions/';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = Promotion::get();

        $map = $output->map(function ($items) {
            if ($items['promotion_image'] == null) {
                $items['promotion_image'] = '' . '/' . $this->path . $this->pathCurrent  . 'default/promotion_default.png';
            } else {
                $items['promotion_image'] = '' . '/' . $this->path . $this->pathCurrent . $items['id'] . '/' . $items['promotion_image'];
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
        $credentials['id'] = null;
        $createPromotion = Promotion::create($credentials);
        if ($request->hasFile('Image')) {
            if (File::makeDirectory($this->path . $this->pathCurrent . $createPromotion->id, 0775, true)) {
                $file = $request->file('Image');

                $filename = $createPromotion->id . '.png';
                $saveImagePath = $this->path . $this->pathCurrent . $createPromotion->id;
                // $width = Image::make($file)->width();
                // $height = Image::make($file)->height();
                $resize = Image::make($file)->resize(1600, 1200);

                //$resize->save();
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $update = Promotion::find($createPromotion->id);
                $update->promotion_image = $filename;
                $update->save();
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function show(Promotion $promotion)
    {
        return response()->json($promotion);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function edit(Promotion $promotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Promotion $promotion)
    {
        $credentials = (array) json_decode($request->formData);
        $promotion->update($credentials);
        if ($request->hasFile('Image')) {
            if (!File::exists($this->path . $this->pathCurrent . $credentials['id'])) {
                if (File::makeDirectory($this->path . $this->pathCurrent . $credentials['id'], 0775, true)) {
                    $file = $request->file('Image');

                    $filename = $credentials['id'] . '.png';
                    $saveImagePath = $this->path . $this->pathCurrent . $credentials['id'];
                    $resize = Image::make($file)->resize(1600, 1200, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    //$resize->save();
                    $resize->save($this->temp . $filename, 100);
                    $file->move($saveImagePath, $filename);
                    File::delete($this->temp . $filename);
                    $update = Promotion::find($credentials['id']);
                    $update->promotion_image = $filename;
                    $update->save();
                }
            } else {
                $file = $request->file('Image');

                $filename = $credentials['id'] . '.png';
                $saveImagePath = $this->path . $this->pathCurrent . $credentials['id'];
                // $width = Image::make($file)->width();
                // $height = Image::make($file)->height();
                $resize = Image::make($file)->resize(1600, 1200);
                // $resize->save();
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $update = Promotion::find($credentials['id']);
                $update->promotion_image = $filename;
                $update->save();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Promotion $promotion)
    {
        if (!File::exists($this->path . $this->pathCurrent  . $promotion->id)) {
            $delImgPath = $this->path . $this->pathCurrent  . $promotion->id;
            File::deleteDirectory($delImgPath);
        }
        $promotion->delete();
    }


    public function getImagePromotion()
    {
        $output = Promotion::where('promotion_active', 1)->get();

        $map = $output->map(function ($items) {
            if ($items['promotion_image'] == null) {
                $items['promotion_image'] = '' . '/' . $this->path . $this->pathCurrent  . 'default/promotion_default.png';
            } else {
                $items['promotion_image'] = '' . '/' . $this->path . $this->pathCurrent . $items['id'] . '/' . $items['promotion_image'];
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
