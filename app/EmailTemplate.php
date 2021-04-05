<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    //
    use EncryptedAttribute;
    protected $encryptable = [
        'subject', 'content'
    ];
}
