<?php

namespace App;
//use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SocialMedia extends Model
{
    //
    use SoftDeletes;
  //  use EncryptedAttribute;

    protected $fillable = [
        'provider_id', 'name','link',
   ];
    // protected $encryptable = [
    // 'link',
    // ];
}
