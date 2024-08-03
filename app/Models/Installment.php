<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Installment extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->select(['id', 'first_name']);
    }

    public function installment_payments()
    {
        return $this->hasMany(InstallmentPayment::class, 'installment_id');
    }

    public function working()
    {
        return $this->belongsTo(Working::class, 'working_id')->select(['id', 'customer_name', 'car_id', 'sale_id', 'branch_id']);
    }
}
