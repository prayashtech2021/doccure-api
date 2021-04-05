<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    use EncryptedAttribute;
    protected $encryptable = [
        'title','sub_title','content',
    ];
}
