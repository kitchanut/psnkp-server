<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;
use DateTimeInterface;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'tel', 'user_group_id', 'branch_team_id', 'branch_id', 'user_active', 'user_code', 'bank', 'bank_no', 'user_team_id', 'user_del'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        // $pathImg = 'assets/images/users/';
        $queryUser = DB::table('users')
            ->where("users.id", $this->id)
            ->join('branches', 'users.branch_id', '=', 'branches.id')
            ->join('user_groups', 'users.user_group_id', '=', 'user_groups.id')
            ->leftJoin('user_teams', function ($join) {
                $join->on('users.user_team_id', '=', 'user_teams.id');
            })
            ->get();

        $user = User::find($this->id);
        $branch_team = Branch_team::find($user->branch_team_id);

        $sub_group = User_sub_group::select(['id', 'user_group_id', 'branch_id', 'user_team_id'])
            ->where([["user_sub_groups.user_id", $this->id], ['active', 1]])->get();

        $new_sub = $sub_group->map(function ($items) {
            $items['user_groups'] = User_group::select('user_group_name', 'user_group_permission')->find($items['user_group_id']);
            $items['branches'] = Branch::select('branch_name')->find($items['branch_id']);
            $items['user_teams'] = User_team::select('team_name')->find($items['user_team_id']);
            return $items;
        });

        foreach ($queryUser as $key => $dataUser) {
            $user_group_permission['user_group_permission'] = $dataUser->user_group_permission;
            $user_code['user_code'] = $dataUser->user_code;
            $user_team['user_team'] = $dataUser->team_name;

            if ($dataUser->branch_id == null) {
                $newQuery = User::find($this->id);
                $branch_id['branch_id'] = $newQuery->branch_id;
            } else {
                $branch_id['branch_id'] = $dataUser->branch_id;
            }
            $branch_name['branch_name'] = $dataUser->branch_name;
            $user_group_name['user_group_name'] = $dataUser->user_group_name;
        }

        return [
            'iss' => '',
            // 'jti' => '',
            // 'sub' => '',
            // 'prv' => '',
            // 'user_image' => $user_image,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'user_group_permission' => $user_group_permission,
            'user_group_name' => $user_group_name,
            'user_team_id' => $this->user_team_id,
            'branch_team_id' => $branch_team->id,
            'branch_team' => $branch_team,
            'branch_id' => $branch_id,
            'branch_name' => $branch_name,
            'user_code' => $user_code,
            'user_team' => $user_team,
            'sub_group' => $new_sub,
        ];
    }

    public function user_group()
    {
        return $this->belongsTo(User_group::class, 'user_group_id');
    }

    public function branch_team()
    {
        return $this->belongsTo(Branch_team::class, 'branch_team_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function team()
    {
        return $this->belongsTo(User_team::class, 'user_team_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
