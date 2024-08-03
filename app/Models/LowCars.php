<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LowCars extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function car_models()
    {
        return $this->belongsTo(Car_model::class, 'car_models_id')
            ->select(['id', 'car_model_name']);
    }

    public function car_series()
    {
        return $this->belongsTo(Car_series::class, 'car_serie_id')
            ->select(['id', 'car_series_name']);
    }

    public function car_serie_sub()
    {
        return $this->belongsTo(Car_serie_sub::class, 'car_serie_sub_id')
            ->select(['id', 'car_serie_sub_name']);
    }
}
