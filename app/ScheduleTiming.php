<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ScheduleTiming extends Model
{
    protected $casts = [
        'working_hours' => 'array'
    ];

    public function getData(){
        return [
            'id' => $this->id,
            'appointment_type' => ($this->appointment_type == 1)?'Online':'Offline',
            'duration' => Carbon::parse((int)$this->duration)->format('i'),
            'working_hours' => $this->working_hours,
        ];
    }

    public function provider(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
