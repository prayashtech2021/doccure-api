<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'provider_id', 'name','link',
   ];
    protected $encryptable = [
    'name'
    ];
}
