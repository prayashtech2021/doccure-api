<?php

namespace App;
//use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipDetail extends Model
{
    use SoftDeletes;
   // use EncryptedAttribute;

    protected $fillable = [
        'user_id', 'name','created_by',
   ];
    // protected $encryptable = [
    //     'name'
    // ];
}
