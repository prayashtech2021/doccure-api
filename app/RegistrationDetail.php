<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistrationDetail extends Model
{
    use SoftDeletes;
    use EncryptedAttribute;

    protected $fillable = [
        'user_id', 'name','registration_year','created_by',
    ];
    protected $encryptable = [
    'name'
    ];
}
