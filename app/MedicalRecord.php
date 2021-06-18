<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

class MedicalRecord extends Model
{
    //
    use SoftDeletes;
    use EncryptedAttribute;
    
    protected $fillable = [
        'appointment_id','consumer_id', 'provider_id','description','document_','created_by',
    ];

    protected $encryptable = [
        'description'
    ];

    public function getData(){
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'created' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'M-d-Y h:i A'),
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
