<?php

namespace App\Http\Controllers;

// use App\Branch;
use App\Models\Branch;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class BranchController extends Controller
{
    protected $pathCurrent = 'branchs/';


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Branch::all());
        // return response()->json(Branch::with(['province'])->get());

        $output = Branch::with(['province', 'branch_team'])->where([['del', 1]])->orderBy('branch_name', 'asc')->get();
        return response()->json($output);

        // $map = $output->map(function ($items) {
        //     if ($items['branch_image'] == null) {
        //         $items['branch_image'] = '' . '/' . $this->path . $this->pathCurrent  . 'default/branch_default.png';
        //     } else {
        //         $items['branch_image'] = '' . '/' . $this->path . $this->pathCurrent . $items['id'] . '/' . $items['branch_image'];
        //     }
        //     return $items;
        // });
        // return response()->json($map);
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
        unset($credentials['zip_code']);
        $createBranch = Branch::create($credentials);

        if ($request->hasFile('qr_code')) {
            if (File::makeDirectory($this->path . $this->pathCurrent . $createBranch->id, 0775, true)) {
                $file = $request->file('qr_code');

                $filename = $createBranch->id . '_qr_code.png';
                $saveImagePath = $this->path . $this->pathCurrent . $createBranch->id;
                // $width = Image::make($file)->width();
                // $height = Image::make($file)->height();
                $resize = Image::make($file)->resize(800, 600);
                //$resize->save();
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $update = Branch::find($createBranch->id);
                $update->branch_image = $filename;
                $update->save();
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function show(Branch $branch)
    {
        return response()->json($branch);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Branch $branch)
    {
        // $branch->update($request->except(['updated_at', 'zip_code']));
        $credentials = (array) json_decode($request->formData);
        unset($credentials['zip_code']);
        $branch->update($credentials);
        $Working = Working::where('branch_team_id', $branch->branch_team_id);
        if (!empty($Working)) {
            $Working->branch_team_id = $branch->branch_team_id;
        }

        if ($request->hasFile('qr_code')) {
            if (!File::exists($this->path . $this->pathCurrent . $credentials['id'])) {
                if (File::makeDirectory($this->path . $this->pathCurrent . $credentials['id'], 0775, true)) {
                    $file = $request->file('qr_code');

                    $filename = $credentials['id'] . '_qr_code.png';
                    $saveImagePath = $this->path . $this->pathCurrent . $credentials['id'];
                    $resize = Image::make($file)->resize(800, 600, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    // $resize->save();
                    $resize->save($this->temp . $filename, 100);
                    $file->move($saveImagePath, $filename);
                    File::delete($this->temp . $filename);
                    $update = Branch::find($credentials['id']);
                    $update->branch_image = $filename;
                    $update->save();
                }
            } else {
                $file = $request->file('qr_code');

                $filename = $credentials['id'] . '_qr_code.png';
                $saveImagePath = $this->path . $this->pathCurrent . $credentials['id'];
                // $width = Image::make($file)->width();
                // $height = Image::make($file)->height();
                $resize = Image::make($file)->resize(800, 600);
                //$resize->save();
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $update = Branch::find($credentials['id']);
                $update->branch_image = $filename;
                $update->save();
                // return response()->json($saveImagePath);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Branch $branch, Request $request)
    {
        // if (!File::exists($this->path . $this->pathCurrent . '/' . $branch->id)) {
        //     File::delete($this->path . $this->pathCurrent . '/' . $branch->id);
        // };

        // $branch->delete();
        $branch->branch_active = 0;
        $branch->del = 0;
        $branch->user_id = $request->user()->id;
        $branch->save();
        // return response()->json($branch->delete());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function SelectOnBranches()
    {
        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            $allow_query = Branch::where([['branch_active', 1], ['id', '>', 0]])->orderBy('branch_name', 'asc')->get();
            return response()->json($allow_query);
        } else {
            if ($host == $allow_host) {
                $allow_query = Branch::where([['branch_active', 1], ['id', '>', 0]])->orderBy('branch_name', 'asc')->get();
                return response()->json($allow_query);
            } else {
                return response()->json($notaloow_query);
            }
        }
        // return response()->json($debug);
    }

    public function SelectOnBranches_where($province_id)
    {
        if ($province_id == -1) {
            $province_id = 0;
        } else {
            $province_id = $province_id;
        }
        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {
            $allow_query = Branch::where([['branch_active', 1], ['id', '>', 0]])
                ->when($province_id, function ($query) use ($province_id) {
                    return $query->where('province_id', $province_id);
                })
                ->get();
            return response()->json($allow_query);
        } else {
            if ($host == $allow_host) {
                $allow_query = Branch::where([['branch_active', 1], ['id', '>', 0]])
                    ->when($province_id, function ($query) use ($province_id) {
                        return $query->where('province_id', $province_id);
                    })
                    ->get();
                return response()->json($allow_query);
            } else {
                return response()->json($notaloow_query);
            }
        }
        // return response()->json($debug);
    }



    public function selectOnBranch($id)
    {
        $queryBranch = Branch::find($id);
        return response()->json($queryBranch);
    }

    public function getProvince()
    {
        // return response()->json(Branch::all());
        // return response()->json(Branch::with(['province'])->get());

        $host = request()->getHttpHost();
        $allow_host = config('app.domain');
        $debug = config('app.debug');
        $notaloow_query = [];
        if ($debug == true) {

            $output = DB::table('branches')
                ->join('provinces', 'branches.province_id', '=', 'provinces.id')
                ->select(DB::raw("provinces.name_th as name_th"), DB::raw('provinces.id as id'))
                ->groupBy(DB::raw('provinces.id'), DB::raw("provinces.name_th"))
                ->get();


            return response()->json($output);
        } else {
            if ($host == $allow_host) {
                $output = DB::table('branches')
                    ->join('provinces', 'branches.province_id', '=', 'provinces.id')
                    ->select(DB::raw("provinces.name_th as name_th"), DB::raw('provinces.id as id'))
                    ->groupBy(DB::raw('provinces.id'), DB::raw("provinces.name_th"))
                    ->get();
                return response()->json($output);
            } else {
                return response()->json($notaloow_query);
            }
        }


        // $map = $output->map(function ($items) {
        //     if ($items['branch_image'] == null) {
        //         $items['branch_image'] = '' . '/' . $this->path . $this->pathCurrent  . 'default/branch_default.png';
        //     } else {
        //         $items['branch_image'] = '' . '/' . $this->path . $this->pathCurrent . $items['id'] . '/' . $items['branch_image'];
        //     }
        //     return $items;
        // });
        // return response()->json($map);
    }
}
