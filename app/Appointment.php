<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    public function getData(){
        return [
            'id' => $this->id,
            'reference' => $this->appointment_reference,
            'date' => convertToLocal(Carbon::parse($this->appointment_date),'','d/m/Y'),
            'start_time' => Carbon::parse($this->start_time)->format('h:i A'),
            'end_time' => Carbon::parse($this->end_time)->format('h:i A'),
            'created' => $this->created_at->format('d/m/Y h:i A'),
        ];
    }
}
