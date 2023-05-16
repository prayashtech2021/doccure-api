<?php

namespace App;
//use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;

class MultiLanguage extends Model
{
   // use EncryptedAttribute;

    public $table = 'multi_languages';
    protected $fillable = [
        'page_master_id', 'language_id','keyword','value','created_by',
   ];
    // protected $encryptable = [
    // 'keyword','value'
    // ];

    public function getData(){
        return [ $this->keyword => $this->value ];
    }

    public function page(){
        return $this->belongsTo(PageMaster::class, 'page_master_id', 'id');
    }
}
