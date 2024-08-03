<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Middle_price extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function car_serie()
    {
        return $this->belongsTo(Car_series::class, 'car_serie_id');
    }


    public function car_serie_sub()
    {
        return $this->belongsTo(Car_serie_sub::class, 'car_serie_sub_id');
    }
    // public function middle_price_details()
    // {
    //     return $this->hasMany(Middle_price_detail::class);
    // }
}
