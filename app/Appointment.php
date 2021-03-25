<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    public function getData(){
        return [
            'id' => $this->id,
            'created' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'d/m/Y h:i A'),
            'reference' => $this->appointment_reference,
            'type' => $this->appointment_type,
            'type_name' => config('appointments.type')[$this->appointment_type],
            'appointment_status' => $this->appointment_status,
            'appointment_status_name' => ucwords(config('custom.appointment_status')[$this->appointment_status]),
            'request_type' => $this->request_type,
            'call_status' => $this->call_status,
            'review_status' => $this->review_status,
            'doctor' => $this->doctor()->first()->basicProfile(),
            'patient' => $this->patient()->first()->basicProfile(),
            'date' => Carbon::parse($this->appointment_date)->format('d/m/Y'),
            'start_time' => Carbon::parse($this->start_time)->format('h:i A'),
            'end_time' => Carbon::parse($this->end_time)->format('h:i A'),
            'next_visit' => convertToLocal(Carbon::parse($this->next_visit),config('custom.timezone')[251],'d/m/Y'),
            'transaction_data' => $this->payment()->first()->getData()
        ];
    }

    public function basicData(){
        return [
            'id' => $this->id,
            'created' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'d/m/Y h:i A'),
            'type' => config('appointments.type')[$this->appointment_type],
            'appointment_status' => config('custom.appointment_status')[$this->appointment_status],
            'doctor' => $this->doctor()->first()->basicProfile(),
            'patient' => $this->patient()->first()->basicProfile(),
            'date' => Carbon::parse($this->appointment_date)->format('d/m/Y'),
            'start_time' => Carbon::parse($this->start_time)->format('h:i A'),
            'end_time' => Carbon::parse($this->end_time)->format('h:i A'),
            'amount' => $this->payment()->select('currency_code','total_amount')->first(),
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
