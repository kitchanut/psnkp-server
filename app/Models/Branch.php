<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use DateTimeInterface;

class Branch extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }


    public function branch_team()
    {
        return $this->belongsTo(Branch_team::class, 'branch_team_id');
    }
}
