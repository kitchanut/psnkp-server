<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Customer_visit extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
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


    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function amount_down()
    {
        return $this->belongsTo(Amount_down::class, 'amount_down_id');
    }

    public function amount_slacken()
    {
        return $this->belongsTo(Amount_slacken::class, 'amount_slacken_id');
    }

}
