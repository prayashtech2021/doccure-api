<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;


class MedicalRecord extends Model
{
    //
    use SoftDeletes;

    
    protected $fillable = [
        'appointment_id','consumer_id', 'provider_id','description','document_','created_by',
    ];

    public function getData(){
        return [
            'id' => $this->id,
            'created' => Carbon::parse($this->created_at)->format('d/m/Y h:i A'),
            'appointment_reference_no' => $this->appointment()->select('appointment_reference')->first(),
            'doctor' => $this->doctor()->first()->basicProfile(),
            'patient' => $this->patient()->first()->basicProfile(),
            'description' => $this->description,
            'document_file' => $this->document_file,
        ];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    public function patient(){
       return $this->belongsTo(User::class, 'consumer_id', 'id');
    }

    public function doctor(){
        return $this->belongsTo(User::class, 'provider_id', 'id');
    }
}
