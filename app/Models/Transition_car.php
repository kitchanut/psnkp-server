<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transition_car extends Model
{
    use HasFactory;

    protected $guarded = [];
    // public $timestamps = false;
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public function car_types()
    {
        return $this->belongsTo(Car_type::class, 'car_types_id');
    }

    public function car_models()
    {
        return $this->belongsTo(Car_model::class, 'car_models_id');
    }

    public function car_series()
    {
        return $this->belongsTo(Car_series::class, 'car_serie_id');
    }


    public function car_serie_sub()
    {
        return $this->belongsTo(Car_serie_sub::class, 'car_serie_sub_id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
