<?php

namespace App;
//use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationDetail extends Model
{
    use SoftDeletes;
   // use EncryptedAttribute;

    protected $fillable = [
        'user_id', 'degeree', 'institute', 'year_of_completion', 'created_by',
    ];
    // protected $encryptable = [
    //     'degeree', 'institute'
    // ];
}
