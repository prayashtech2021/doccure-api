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
         'id','user_id', 'speciality_id', 'duration', 'amount', 'created_by',
    ];


    public function getData(){
        return [
            'id' => $this->id,
            'name' => $this->special()->first(),
            'duration' => $this->duration,
            'amount' => $this->amount,
        ];
    }

    public function user() { 
        return $this->belongsTo('App\User'); 
    }
    public function special() { 
        return $this->belongsTo('App\Speciality','speciality_id')->pluck('name'); 
    }
    
}
