<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public function car_types()
    {
        return $this->belongsTo(Car_type::class, 'car_types_id')
            ->select(['id', 'car_type_name', 'car_type_name_en']);
    }

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

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id')->select(['id', 'name_th']);
    }

    public function province_current()
    {
        return $this->belongsTo(Province::class, 'province_id_current')->select(['id', 'name_th']);
    }

    public function car_serie_sub()
    {
        return $this->belongsTo(Car_serie_sub::class, 'car_serie_sub_id')
            ->select(['id', 'car_serie_sub_name']);
    }

    public function car_images()
    {
        return $this->hasMany(Image_car::class, 'car_id')->orderBy('img_first', 'DESC');
    }

    public function img_first()
    {
        return $this->hasOne(Image_car::class, 'id', 'img_id_first');
    }

    public function fuels()
    {
        return $this->belongsTo(Fuel::class, 'fuel_id')
            ->select(['id', 'fuel_name']);
    }


    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id')
            ->select(['id', 'branch_name']);
    }


    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id')
            ->select(['id', 'color_name']);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class, 'car_id')->latest();
    }

    public function working()
    {
        return $this->hasOne(Working::class, 'car_id')->where([['status_del', 1], ['work_status', '>=', 8]]);
    }

    public function workings()
    {
        return $this->hasMany(Working::class, 'car_id')->where([['status_del', 1]]);
    }

    public function partner_car()
    {
        return $this->belongsTo(Partner_car::class, 'partner_car_id')
            ->select(['id', 'partner_car_name']);
    }

    public function insurance()
    {
        return $this->hasMany(Insurance::class, 'car_id')->orderBy('insurance_end', 'DESC');
    }
}
