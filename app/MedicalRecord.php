<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    //
    use SoftDeletes;

    
    protected $fillable = [
        'consumer_id', 'provider_id','description','document_','created_by',
   ];

    public function patient(){
       return $this->belongsTo(User::class, 'consumer_id', 'id');
    }

    public function doctor(){
        return $this->belongsTo(User::class, 'provider_id', 'id');
    }
}
