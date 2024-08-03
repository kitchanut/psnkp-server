<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\User_group;
use App\Models\User_sub_group;
use App\Models\User_team;
use App\Models\Working;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
{
    protected $pathCurrent = 'users/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $output = User::with(['branch_team', 'branch', 'user_group', 'team'])->where([['user_del', 1]])->get();

        $map = $output->map(function ($items) {
            if ($items['user_image'] == null) {
                $items['user_image'] = '' . '/' . $this->path . 'users/'  . 'default/user_default.png';
            } else {
                $items['user_image'] = '' . '/' . $this->path . 'users/' . $items['id'] . '/' . $items['user_image'];
            }
            $items['user_sub_groups'] = User_sub_group::where('user_id', $items['id'])->count();
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
        $credentials_2 = (array) json_decode($request->formData);

        $credentials['password'] = bcrypt($credentials['password']);
        $checkEmail = User::where('email', $credentials['email'])->first();
        if ($checkEmail != null) {
            return response()->json('ชื่อผู้ใช้งานซ้ำ');
        }

        $checkCode = User::where('user_code', $credentials['user_code'])->first();
        if ($checkCode != null) {
            return response()->json('รหัสลับซ้ำ');
        }
        unset($credentials['user_sub_groups']);
        $createUser = User::create($credentials);

        $user_sub = $credentials_2['user_sub_groups'];
        for ($i = 0; $i < count($user_sub); $i++) {
            User_sub_group::create([
                'user_id' => $createUser->id,
                'user_group_id' => $user_sub[$i]->user_group_id,
                'branch_id' => $user_sub[$i]->branch_id,
                'user_team_id' => $user_sub[$i]->user_team_id,
                'active' => $user_sub[$i]->active,
            ]);
        }

        if ($request->hasFile('Image')) {
            if (File::makeDirectory($this->path . $this->pathCurrent . $createUser->id, 0775, true)) {
                $file = $request->file('Image');

                $filename = $createUser->id . '.png';
                $saveImagePath = $this->path . $this->pathCurrent . $createUser->id;
                // $width = Image::make($file)->width();
                // $height = Image::make($file)->height();
                $resize = Image::make($file)->resize(800, 600);
                //$resize->save();
                $resize->save($this->temp . $filename, 100);
                $file->move($saveImagePath, $filename);
                File::delete($this->temp . $filename);
                $update = User::find($createUser->id);
                $update->user_image = $filename;
                $update->save();
            }
        }

        return response()->json('success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {

        if ($user->user_image  == null) {
            $user->user_image  = 'default/user_default.png';
        } else {
            $user->user_image  =  $user->id  . '/' . $user->user_image;
        }
        $user->branch = Branch::find($user->branch_id);

        // $user->user_sub_groups = User_sub_group::where('user_id', $user->id)->get();
        $User_sub_group = User_sub_group::where('user_id', $user->id)->get();
        $user->user_sub_groups = $User_sub_group->map(function ($items) {
            $items['user_groups'] = User_group::where([['user_group_active', 1]])->get();
            $items['branches'] = Branch::where([['branch_active', 1], ['id', '>', 0]])->get();
            $items['user_teams'] = User_team::where([['branch_id', $items['branch_id']]])->get();
            return $items;
        });

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $credentials = (array) json_decode($request->formData);
        $credentials_2 = (array) json_decode($request->formData);

        $Working = Working::where('sale_id', $credentials['id'])->get();
        for ($i = 0; $i < count($Working); $i++) {
            if ($Working[$i]->work_status <= 8) {
                $update = Working::find($Working[$i]->id);
                $update->user_team_id = $credentials['user_team_id'];
                $update->branch_id = $credentials['branch_id'];

                $branch = Branch::find($credentials['branch_id']);
                $update->branch_team_id = $branch->branch_team_id;

                $update->save();
            }
        }

        if (!empty($credentials['password'])) {
            $credentials['password'] = bcrypt($credentials['password']);
        } else {
            unset($credentials['password']);
        }

        unset($credentials['user_sub_groups']);
        $user->update($credentials);

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
                    $update = User::find($credentials['id']);
                    $update->user_image = $filename;
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
                $update = User::find($credentials['id']);
                $update->user_image = $filename;
                $update->save();
            }
        }

        User_sub_group::where('user_id', $user->id)->delete();
        $user_sub = $credentials_2['user_sub_groups'];
        for ($i = 0; $i < count($user_sub); $i++) {
            User_sub_group::create([
                'user_id' => $user->id,
                'user_group_id' => $user_sub[$i]->user_group_id,
                'branch_id' => $user_sub[$i]->branch_id,
                'user_team_id' => $user_sub[$i]->user_team_id,
                'active' => $user_sub[$i]->active,
            ]);
        }


        // $Working->save();
        // return response()->json();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // $user->delete();
        $user->user_del = 0;
        $user->save();
    }


    public function SelectOnSale()
    {
        $output = User::with('branch.branch_team')
            ->where([['user_del', 1]])
            ->get();
        return response()->json($output);
    }


    public function SelectOnTechnicianBuild($branch_id)
    {
        return response()->json(User::where([['branch_id', $branch_id], ['user_del', 1]])->where('user_group_id', 4)->orWhere('user_group_id', 5)->get());
        // return response()->json($branch_id);

    }
}
