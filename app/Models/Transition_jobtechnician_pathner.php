<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Transition_jobtechnician_pathner extends Model
{
    use HasFactory;


    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function partner_technicians()
    {
        return $this->belongsTo(partner_technicians::class, 'partner_technician_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
