<?php

namespace App;
//use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Review extends Model
{
    use SoftDeletes;
   // use EncryptedAttribute;
    
    protected $fillable = [
        'appointment_id','user_id', 'reviewer_id', 'rating', 'description','reply','created_by',
    ];
    // protected $encryptable = [
    //     'description','reply'
    // ];
    public function getData(){
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'user' => $this->user()->first()->basicProfile(),
            'reviewer' => $this->reviewer()->first()->basicProfile(),
            'rating' => $this->rating,
            'description' => $this->description,
            'reply' => $this->reply,
            'created' => Carbon::parse($this->created_at)->diffForHumans(),//$this->created_at->diffForHumans(), 
            'updated' => Carbon::parse($this->updated_at)->diffForHumans(), //$this->updated_at->diffForHumans(), 
        ];
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function reviewer(){
        return $this->belongsTo(User::class, 'reviewer_id', 'id');
    }
}
?>