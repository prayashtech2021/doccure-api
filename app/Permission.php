<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
  use EncryptedAttribute;

  protected $guard_name='web';
  
    protected $fillable = [
        'name','guard_name',
    ];
    protected $encryptable = [
      'name','guard_name'
    ];
   
    public function users(){
      return $this->belongsToMany('App\User');
    }
}
