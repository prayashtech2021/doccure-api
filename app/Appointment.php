<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\User;

class Appointment extends Model
{
    public function getData(){
        (auth()->user()->time_zone)? $zone = auth()->user()->time_zone : $zone = '';
        if(auth()->user()->hasRole('doctor')){
        $ttemp1 = $this->appointment_date.' '.$this->start_time;
        $ttemp2 = $this->appointment_date.' '.$this->end_time;
        $userr = User::find($this->user_id);
        $providerr = User::find($this->doctor_id);
        $startTime = userToProvider($ttemp1,$userr->time_zone,$providerr->time_zone,'h:i A');
        $endTime = userToProvider($ttemp2,$userr->time_zone,$providerr->time_zone,'h:i A');
        }else{
            $startTime = Carbon::parse($this->start_time)->format('h:i A');
            $endTime = Carbon::parse($this->end_time)->format('h:i A');
        }
        return [
            'id' => $this->id,
            'created' => convertToLocal(Carbon::parse($this->created_at),$zone,''),
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
            'date' => date('d/m/Y',strtotime($this->appointment_date)),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'next_visit' => convertToLocal(Carbon::parse($this->next_visit),$zone,'M-d-Y'),
            'transaction_data' => $this->payment()->first()->getData()
        ];
    }

    public function basicData(){
        (auth()->user()->time_zone)? $zone = auth()->user()->time_zone : $zone = '';
        if(auth()->user()->hasRole('doctor')){
            $ttemp1 = $this->appointment_date.' '.$this->start_time;
            $ttemp2 = $this->appointment_date.' '.$this->end_time;
            $userr = User::find($this->user_id);
            $providerr = User::find($this->doctor_id);
            $startTime = userToProvider($ttemp1,$userr->time_zone,$providerr->time_zone,'h:i A');
            $endTime = userToProvider($ttemp2,$userr->time_zone,$providerr->time_zone,'h:i A');
            }else{
                $startTime = Carbon::parse($this->start_time)->format('h:i A');
                $endTime = Carbon::parse($this->end_time)->format('h:i A');
            }
        return [
            'id' => $this->id,
            'created' => convertToLocal(Carbon::parse($this->created_at),$zone,''),
            'type' => config('appointments.type')[$this->appointment_type],
            'appointment_status' => config('custom.appointment_status')[$this->appointment_status],
            'doctor' => $this->doctor()->first()->basicProfile(),
            'patient' => $this->patient()->first()->basicProfile(),
            'date' => date('d/m/Y',strtotime($this->appointment_date)),
            'start_time' => $startTime,
            'end_time' => $endTime,
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
