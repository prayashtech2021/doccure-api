<?php

namespace App;
//use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExperienceDetail extends Model
{
    use SoftDeletes;
   // use EncryptedAttribute;

    protected $fillable = [
        'user_id', 'hospital_name', 'from', 'to', 'designation', 'created_by',
   ];
    // protected $encryptable = [
    //     'hospital_name','designation'
    // ];
}
