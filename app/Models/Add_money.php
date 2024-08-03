<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Add_money extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // public function branch_details()
    // {
    //     return $this->hasMany(Add_money_detail::class);
    // }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
