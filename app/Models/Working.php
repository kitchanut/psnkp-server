<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Working extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function sale()
    {
        return $this->belongsTo(User::class, 'sale_id')
            ->select(['id', 'first_name']);
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function team()
    {
        return $this->belongsTo(User_team::class, 'user_team_id');
    }


    public function branch_team()
    {
        return $this->belongsTo(Branch_team::class, 'branch_team_id')
            ->select(['id', 'branch_team_name']);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id')
            ->select(['id', 'branch_name']);
    }

    public function appointments()
    {
        return $this->hasOne(Appointment::class, 'working_id');
    }


    public function appointment_banks()
    {
        return $this->hasOne(Appointment_bank::class, 'working_id');
    }

    public function banks()
    {
        return $this->hasOne(Bank::class, 'id', 'bank_id');
    }

    public function bank_branchs()
    {
        return $this->hasOne(Bank_branch::class, 'id', 'bank_branch_id');
    }

    public function bookings()
    {
        return $this->hasOne(Booking::class, 'working_id');
    }

    public function contract()
    {
        return $this->hasOne(Contract::class, 'working_id')->latest();
    }
    public function cars()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function financial()
    {
        return $this->hasOne(Financial::class, 'working_id');
    }

    public function financials()
    {
        return $this->hasMany(Financial::class, 'working_id');
    }

    public function income()
    {
        return $this->hasMany(Income::class, 'car_id', 'car_id')
            ->where('active', 1)
            ->select(['id', 'car_id', 'money', 'detail']);
    }

    public function expenses()
    {
        return $this->hasMany(Outlay_cost::class, 'car_id', 'car_id')
            ->where('active', 1)
            ->select(['id', 'car_id', 'money', 'detail']);
    }

    public function expenses_only_car()
    {
        return $this->hasMany(Outlay_cost::class, 'car_id', 'car_id')
            ->where('active', 1)
            ->where('detail', 'ค่าตัวรถ')
            ->select(['id', 'car_id', 'money', 'detail']);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pre_approve()
    {
        return $this->hasOne(PreApprove::class, 'working_id');
    }

    public function receiving_money()
    {
        return $this->hasOne(Receiving_money::class, 'working_id')->orderBy('id', 'ASC');
    }

    public function request_update()
    {
        return $this->hasMany(RequestUpdate::class, 'working_id');
    }

    public function request_log()
    {
        return $this->hasMany(RequestLog::class, 'working_id');
    }
}
