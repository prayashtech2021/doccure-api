<?php

namespace App;
//use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Storage;
use URL;


class Speciality extends Model
{
    //
    use SoftDeletes;
    //use EncryptedAttribute;

    protected $fillable = [
        'name', 'image', 'created_by',
    ];
    
    public function getData(){
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => $this->duration, //Carbon::parse((int)$this->duration)->format('i'),
            'image' => $this->getSpecilityImage(),
            'amount' => $this->amount,
            'created_at' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'M-d-Y h:i A'),
            'deleted_at' => $this->deleted_at,        
        ];
    }

    public function speciality() { 
        return $this->belongsTo('App\UserSpeciality','id','speciality_id'); 
    }
    function getSpecilityImage(){
        $image = $this->image;
        if (!empty($image) && Storage::exists('images/speciality/' . $image)) {
            $path = (config('filesystems.default') == 's3') ? Storage::temporaryUrl('images/speciality/' . $image, now()->addMinutes(5)) : url('storage/images/speciality/' . $image);
        } else {
            $path = url('img/speciality-logo.png');
        }
        return $path;
    }
}
