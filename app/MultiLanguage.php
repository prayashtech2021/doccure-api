<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;

class MultiLanguage extends Model
{
    use EncryptedAttribute;

    public $table = 'multi_languages';
    protected $fillable = [
        'page_master_id', 'language_id','keyword','value','created_by',
   ];
    protected $encryptable = [
    'keyword','value'
    ];
}
