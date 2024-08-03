<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negotiation extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function working()
    {
        return $this->belongsTo(Working::class, 'working_id');
    }
}
