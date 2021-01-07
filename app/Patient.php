<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Patient extends Model
{
    protected $fillable = [
        'id','user_id','first_name', 'last_name', 'sex', 'dob', 'blood_group', 'biography','where_you_heard','address_line1','country_id','state_id','city_id','created_by'
    ];

    public function user() { 
        return $this->belongsTo('App\User'); 
    }
    public function country() { 
        return $this->belongsTo('App\Country'); 
    }
    public function state() { 
        return $this->belongsTo('App\State'); 
    }
    public function city() { 
        return $this->belongsTo('App\City'); 
    }
    public function getPidAttribute() { // patient_ID
        return 'PT00'.$this->id; 
    } 
    public function getAgeAttribute() {
        return $this->dob->diffInYears(\Carbon\Carbon::now());
    }
    
}
