<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSpeciality extends Model{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_speciality';
    public $timestamps = false;

    protected $fillable = [
         'user_id', 'speciality_id',
    ];


    public function user() { 
        return $this->belongsTo('App\User'); }
    public function special() { 
        return $this->belongsTo('App\Speciality','speciality_id','id'); 
    }
}
