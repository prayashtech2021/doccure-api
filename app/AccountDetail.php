<?php

namespace App;
//use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;

class AccountDetail extends Model
{
   // use EncryptedAttribute;
    // protected $encryptable = [
    //     'account_name','bank_name','branch_name'
    // ];
    public function getData(){
        return [
            'id' => $this->id,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'bank_name' => $this->bank_name,
            'branch_name' => $this->branch_name,
            'ifsc_code' => $this->ifsc_code,
            'tin_number' => $this->tin_number
        ];
    }
}
