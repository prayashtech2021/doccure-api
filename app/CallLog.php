<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    protected $fillable = [
        'appointment_id','from', 'to', 'start_time', 'end_time', 'duration', 'type',
   ];

  
}
