<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Prescription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'appointment_id', 'signature_id','created_by',
   ];

    /*public function getData(){
        return [
            'id' => $this->id,
            'created' => Carbon::parse($this->created_at)->format('d/m/Y h:i A'),
            'doctor' => $this->doctor()->first()->basicProfile(),
            'patient' => $this->patient()->first()->basicProfile(),
            'date' => convertToLocal(Carbon::parse($this->appointment_date),'','d/m/Y'),
            'start_time' => Carbon::parse($this->start_time)->format('h:i A'),
            'end_time' => Carbon::parse($this->end_time)->format('h:i A'),
            'next_visit' => convertToLocal(Carbon::parse($this->next_visit),'','d/m/Y'),
        ];
    }*/

    public function prescriptionDetails(){
        return $this->hasMany(PrescriptionDetail::class);
    }
    
}
