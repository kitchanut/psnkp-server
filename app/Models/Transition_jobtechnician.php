<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Transition_jobtechnician extends Model
{
    use HasFactory;


    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function car_lift()
    {
        return $this->belongsTo(Car_lift::class, 'carlift_id');
    }

    public function cars()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function repair()
    {
        return $this->belongsTo(Repair::class, 'repair_id');
    }
}
