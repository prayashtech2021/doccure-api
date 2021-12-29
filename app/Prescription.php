<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Storage;

class Prescription extends Model
{
    use SoftDeletes;

    protected $fillable = [
         'appointment_id','user_id','signature_id','created_by',
   ];

    public function getData(){
        $detail = $this->prescriptionDetails()->get();
        removeMetaColumn($detail);
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'doctor' => $this->doctor()->first()->basicProfile(),
            'patient' => $this->patient()->first()->basicProfile(),
            'prescription_details' => $detail,
            'appointment_reference_no' => $this->appointment()->select('appointment_reference')->first(),
            'appointment_date' => $this->appointment()->select('appointment_date')->first(),
            'created_at' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'M-d-Y h:i A'),
            'sign' => $this->signature(),
        ];
    }

    public function appointment(){
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    public function doctorsign() { 
        return $this->belongsTo('App\Signature', 'signature_id','id')->select('id','signature_image'); 
    }

    public function prescriptionDetails(){
        return $this->hasMany(PrescriptionDetail::class);
    }

    public function patient(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function doctor(){
        return $this->belongsTo(User::class, 'doctor_id', 'id');
    }

    public function signature(){
        $sign = $this->doctorsign()->first();
        if($sign && !empty($sign->signature_image) && Storage::exists('images/signature/' . $sign->signature_image)) {
           return (config('filesystems.default') == 's3') ? Storage::temporaryUrl('images/signature/' . $sign->signature_image, now()->addMinutes(5)) : Storage::url('images/signature/' . $sign->signature_image);
        }else{
            return '';
        }
    }
    
}
