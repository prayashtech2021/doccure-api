<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class Speciality extends Model
{
    //
    use SoftDeletes;

    
    protected $fillable = [
        'name', 'image', 'created_by',
   ];

    public function speciality() { 
        return $this->belongsTo('App\UserSpeciality','id','speciality_id'); 
    }
}
