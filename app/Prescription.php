<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Prescription extends Model
{
    use SoftDeletes;

    protected $fillable = [
         'user_id','signature_id','created_by',
   ];

    public function getData(){
        return [
            'id' => $this->id,
            'doctor' => $this->doctor()->first()->basicProfile(),
            'patient' => $this->patient()->first()->basicProfile(),
            'prescription_details' => $this->prescriptionDetails()->get(),
            'created' => Carbon::parse($this->created_at)->format('d/m/Y h:i A'),
            'sign' => $this->doctorsign()->first(),
        ];
    }
    
    public function doctorsign() { 
        return $this->belongsTo('App\Signature', 'signature_id','id')->select('id','signature_image'); 
    }

    public function prescriptionDetails(){
        return $this->hasMany(PrescriptionDetail::class)->select('id','drug_name','quantity','type','days','time');
    }

    public function patient(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function doctor(){
        return $this->belongsTo(User::class, 'doctor_id', 'id');
    }
    
}
