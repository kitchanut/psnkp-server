<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Transition_withdraw_part extends Model
{
    use HasFactory;


    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function car_part()
    {
        return $this->belongsTo(Car_part::class, 'car_part_id');
    }
}
