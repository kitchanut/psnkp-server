<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer_detail()
    {
        return $this->hasOne(Customer_detail::class, 'customer_id');
    }
}
