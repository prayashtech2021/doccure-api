<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    public function getData(){
        return [
            'id' => $this->id,
            'created' => Carbon::parse($this->created_at)->format('d/m/Y h:i A'),
            'reference' => $this->appointment_reference,
            'type' => config('appointments.type')[$this->appointment_type],
            'appointment_status' => ucwords(config('custom.appointment_status')[$this->appointment_status]),
            'doctor' => $this->doctor()->first()->basicProfile(),
            'patient' => $this->patient()->first()->basicProfile(),
            'date' => convertToLocal(Carbon::parse($this->appointment_date),'','d/m/Y'),
            'start_time' => Carbon::parse($this->start_time)->format('h:i A'),
            'end_time' => Carbon::parse($this->end_time)->format('h:i A'),
            'next_visit' => convertToLocal(Carbon::parse($this->next_visit),'','d/m/Y'),
            'transaction_data' => $this->payment()->first()->getData()
        ];
    }

    public function payment(){
        return $this->hasOne(Payment::class);
    }

    public function patient(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function doctor(){
        return $this->belongsTo(User::class, 'doctor_id', 'id');
    }
}
