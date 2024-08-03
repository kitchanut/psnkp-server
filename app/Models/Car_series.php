<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car_series extends Model
{
    use HasFactory;


    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function carType()
    {
        return $this->belongsTo(Car_type::class);
    }

    public function carModel()
    {
        return $this->belongsTo(Car_model::class);
    }

}
