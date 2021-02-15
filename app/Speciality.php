<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;



class Speciality extends Model
{
    //
    use SoftDeletes;

    
    protected $fillable = [
        'name', 'image', 'created_by',
   ];
    public function getData(){
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => $this->duration, //Carbon::parse((int)$this->duration)->format('i'),
            'image' => $this->image,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
        ];
    }

    public function speciality() { 
        return $this->belongsTo('App\UserSpeciality','id','speciality_id'); 
    }
}
